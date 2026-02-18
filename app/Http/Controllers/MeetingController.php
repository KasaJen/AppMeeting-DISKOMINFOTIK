<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Http\Request;
use App\Models\Meeting;
use App\Models\Place;
use Carbon\Carbon;
use Illuminate\Support\Str;

class MeetingController extends Controller
{
    // HALAMAN UTAMA (KALENDER & TABEL)
    public function index()
    {
        // Urutkan berdasarkan waktu meeting dari yang terdekat
        $meetings = Meeting::orderBy('start_time', 'asc')->get();

        $events = $meetings->map(function($item) {
            // Logika Warna: Online = Biru, Offline = Hijau
            $warna = !empty($item->join_url) ? '#3788d8' : '#198754';

            return [
                'id' => $item->id,
                'title' => $item->agency,
                'start' => $item->start_time,
                'end'   => date('Y-m-d H:i:s', strtotime($item->start_time) + ($item->duration * 60)),
                'color' => $warna, 
                
                'extendedProps' => [
                    'deskripsi' => $item->description,
                    'permintaan' => $item->additional_requests,
                    'durasi' => $item->duration,
                    'place' => $item->placeDetail ? $item->placeDetail->name : $item->place,
                    'join_url' => $item->join_url,
                    'meeting_id' => $item->zoom_meeting_id,
                    'password' => $item->password,
                    'share_url' => route('meeting.share', $item->uuid),
                    'edit_url' => route('edit.meeting', $item->id),
                    'delete_id' => $item->id 
                ]
            ];
        });

        return view('meetings.index', compact('events', 'meetings'));
    }

    // FITUR TAMBAH TEMPAT BARU
    public function createPlace()
    {
        $places = Place::orderBy('id', 'desc')->get();
        return view('places.create', compact('places'));
    }

    public function storePlace(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        
        $existingPlace = Place::where('name', $request->name)->first();

        if ($existingPlace) {
            if ($request->ajax()) {
                return response()->json([
                    'status'  => 'exist',
                    'message' => 'Tempat ini sudah terdaftar di database!',
                    'data'    => $existingPlace
                ]);
            }
            return back()->with('error', 'Tempat sudah ada!');
        }

        $newPlace = Place::create(['name' => $request->name]);

        if ($request->ajax()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Tempat baru berhasil ditambahkan!',
                'data'    => $newPlace
            ]);
        }

        return back()->with('success', 'Tempat berhasil ditambahkan!');
    }

    // HAPUS TEMPAT
    public function destroyPlace($id)
    {
        $place = Place::find($id);
        
        if ($place) {
            $place->delete();
            return back()->with('success', 'Master tempat berhasil dihapus!');
        }

        return back()->with('error', 'Data tidak ditemukan.');
    }

    // HALAMAN BUAT JADWAL
    public function create()
    {
        $places = Place::all(); 
        return view('meetings.create', compact('places'));
    }

    public function store(Request $request)
    {
        // VALIDASI INPUT
        $rules = [
            'agency'      => 'required|string',
            'description' => 'required|string',
            'tanggal'     => 'required',
            'jam'         => 'required',
            'duration'    => 'required|integer',
        ];

        if ($request->tipe == 'online') {
            $rules['join_url'] = 'required|url'; 
        } elseif ($request->lokasi_type == 'luar') {
            $rules['place_id'] = 'required';
        }

        $request->validate($rules, [
            'join_url.required' => 'Link Zoom/GMeet wajib diisi kalau meeting online!',
            'place_id.required' => 'Silakan pilih tempat meeting dari daftar!',
        ]);

        // TENTUKAN NAMA TEMPAT & ID TEMPAT
        $tempat = '';
        $placeId = null;
        
        $link = null;
        $meetingId = null;
        $passcode = null;

        if ($request->tipe == 'online') {
            $tempat      = 'Meeting Online';
            $link        = $request->join_url;
            $meetingId   = $request->zoom_meeting_id;
            $passcode    = $request->password;
        } else {
            // Offline
            if ($request->lokasi_type == 'bcc') {
                $tempat = 'Banjarmasin Command Center (BCC)';
                $placeId = null;
            } else {
                // Lokasi Luar: Ambil ID dan Namanya
                $placeData = Place::find($request->place_id);
                if ($placeData) {
                    $tempat = $placeData->name;
                    $placeId = $placeData->id;
                } else {
                    $tempat = 'Tempat Tidak Dikenal';
                }
            }
        }

        // CEK BENTROK (Cek Waktu DAN Tempat)
        $newStart = Carbon::parse($request->tanggal . ' ' . $request->jam . ':00');
        $newEnd   = $newStart->copy()->addMinutes($request->duration);

        $bentrok = Meeting::where(function ($query) use ($newStart, $newEnd) {
            $query->where('start_time', '<', $newEnd)
                  ->whereRaw("DATE_ADD(start_time, INTERVAL duration MINUTE) > ?", [$newStart]);
        })
        ->where('place', $tempat)
        ->exists();

        if ($bentrok) {
            return back()->withInput()->with('error', 'GAGAL! Tempat "' . $tempat . '" sudah terisi jadwal lain di jam tersebut.');
        }

        // SIMPAN KE DATABASE
        Meeting::create([
            'uuid'                => Str::uuid(),
            'agency'              => $request->agency,
            'description'         => $request->description,
            'additional_requests' => $request->additional_requests,
            'start_time'          => $request->tanggal . ' ' . $request->jam . ':00',
            'duration'            => $request->duration,
            'place'               => $tempat,
            'place_id'            => $placeId,
            'join_url'            => $link,
            'zoom_meeting_id'     => $meetingId, 
            'password'            => $passcode,
        ]);

        return redirect('/')->with('success', 'Jadwal berhasil dibuat!');
    }

    // HALAMAN EDIT JADWAL
    public function edit($id)
    {
        $meeting = Meeting::find($id);
        $places = Place::all();
        return view('meetings.edit', compact('meeting', 'places'));
    }

    public function update(Request $request, $id)
    {
        // VALIDASI INPUT
        $rules = [
            'agency'   => 'required',
            'tanggal'  => 'required',
            'jam'      => 'required',
            'duration' => 'required',
        ];

        if ($request->tipe == 'online') {
            $rules['join_url'] = 'required|url';
        } elseif ($request->lokasi_type == 'luar') {
            $rules['place_id'] = 'required';
        }

        $request->validate($rules);

        // TENTUKAN NAMA TEMPAT & ID TEMPAT
        $meeting = Meeting::find($id);
        
        $tempat = '';
        $placeId = null;

        $link = null;
        $meetingId = null;
        $passcode = null;

        if ($request->tipe == 'online') {
            $tempat      = 'Meeting Online';
            $link        = $request->join_url;
            $meetingId   = $request->zoom_meeting_id;
            $passcode    = $request->password;
        } else {
            // Offline
            if ($request->lokasi_type == 'bcc') {
                $tempat = 'Banjarmasin Command Center (BCC)';
                $placeId = null;
            } else {
                // Lokasi Luar
                $placeData = Place::find($request->place_id);
                if ($placeData) {
                    $tempat = $placeData->name;
                    $placeId = $placeData->id;
                } else {
                    $tempat = ($meeting->place != 'Meeting Online') ? $meeting->place : 'Tempat Belum Dipilih';
                }
            }
        }

        // CEK BENTROK (Waktu DAN Tempat)
        $newStart = Carbon::parse($request->tanggal . ' ' . $request->jam . ':00');
        $newEnd   = $newStart->copy()->addMinutes($request->duration);

        $bentrok = Meeting::where('id', '!=', $id)
            ->where(function ($query) use ($newStart, $newEnd) {
                $query->where('start_time', '<', $newEnd)
                      ->whereRaw("DATE_ADD(start_time, INTERVAL duration MINUTE) > ?", [$newStart]);
            })
            ->where('place', $tempat)
            ->exists();

        if ($bentrok) {
            return back()->withInput()->with('error', 'GAGAL UPDATE! Tempat "' . $tempat . '" bentrok dengan jadwal lain.');
        }

        // UPDATE DATABASE
        $meeting->update([
            'agency'              => $request->agency,
            'description'         => $request->description,
            'additional_requests' => $request->additional_requests,
            'start_time'          => $request->tanggal . ' ' . $request->jam . ':00',
            'duration'            => $request->duration,
            'place'               => $tempat,
            'place_id'            => $placeId,
            'join_url'            => $link,
            'zoom_meeting_id'     => $meetingId,
            'password'            => $passcode
        ]);

        return redirect('/')->with('success', 'Jadwal berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $meeting = Meeting::find($id);
        if ($meeting) {
            $meeting->delete();
        }
        
        return redirect()->back()->with('success', 'Jadwal berhasil dihapus!');
    }

    public function showPublic($uuid)
    {
        $meeting = Meeting::where('uuid', $uuid)->firstOrFail();
        return view('meetings.share', compact('meeting'));
    }
}