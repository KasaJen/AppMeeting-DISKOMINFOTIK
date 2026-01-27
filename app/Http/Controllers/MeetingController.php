<?php

namespace App\Http\Controllers;


use Illuminate\Support\Facades\Crypt;
use Illuminate\Http\Request;
use App\Models\Meeting;
use App\Models\Place;
use Carbon\Carbon;

class MeetingController extends Controller
{
    // HALAMAN UTAMA (KALENDER)
    public function index()
    {
        $meetings = Meeting::orderBy('created_at', 'desc')->get();

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
                    'durasi' => $item->duration,
                    'place' => $item->place,
                    
                    'join_url' => $item->join_url,
                    'meeting_id' => $item->zoom_meeting_id,
                    'password' => $item->password,

                    'share_url' => route('meeting.share', Crypt::encryptString($item->id)),

                    'edit_url' => route('edit.meeting', $item->id),
                    'delete_id' => $item->id 
                ]
            ];
        });

        return view('meetings.index', compact('events'));
    }

    // FITUR TAMBAH TEMPAT BARU
    public function createPlace()
    {
        return view('places.create');
    }

    public function storePlace(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        
        $existingPlace = Place::where('name', $request->name)->first();

        if ($existingPlace) {
            // KALAU SUDAH ADA
            if ($request->ajax()) {
                return response()->json([
                    'status'  => 'exist',
                    'message' => 'Tempat ini sudah terdaftar di database!',
                    'data'    => $existingPlace
                ]);
            }
        }

        // KALAU BELUM ADA
        $newPlace = Place::create(['name' => $request->name]);

        if ($request->ajax()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Tempat baru berhasil ditambahkan!',
                'data'    => $newPlace
            ]);
        }

        return redirect('/')->with('success', 'Tempat berhasil ditambahkan!');
    }

    // HALAMAN BUAT JADWAL
    public function create()
    {
        $places = Place::all(); 
        return view('meetings.create', compact('places'));
    }

    public function store(Request $request)
    {
        // VALIDASI (Supaya Data Tidak Error/Kosong)
        $rules = [
            'agency'      => 'required|string',
            'description' => 'required|string',
            'tanggal'     => 'required',
            'jam'         => 'required',
            'duration'    => 'required|integer',
        ];

        // Aturan Khusus:
        // Kalau Online -> WAJIB isi Link
        if ($request->tipe == 'online') {
            $rules['join_url'] = 'required|url'; 
        } 
        // Kalau Luar BCC -> WAJIB pilih Tempat
        elseif ($request->lokasi_type == 'luar') {
            $rules['place_id'] = 'required';
        }

        $request->validate($rules, [
            'join_url.required' => 'Link Zoom/GMeet wajib diisi kalau meeting online!',
            'place_id.required' => 'Silakan pilih tempat meeting dari daftar!',
        ]);

        // CEK BENTROK JADWAL
        $newStart = Carbon::parse($request->tanggal . ' ' . $request->jam . ':00');
        $newEnd   = $newStart->copy()->addMinutes($request->duration);

        $bentrok = Meeting::where(function ($query) use ($newStart, $newEnd) {
            $query->where('start_time', '<', $newEnd)
                  ->whereRaw("DATE_ADD(start_time, INTERVAL duration MINUTE) > ?", [$newStart]);
        })->exists();

        if ($bentrok) {
            return back()->withInput()->with('error', 'GAGAL! Jadwal bentrok dengan meeting lain di jam tersebut.');
        }

        // LOGIKA PENENTUAN TEMPAT & DATA ONLINE
        $link = null;
        $meetingId = null;
        $passcode = null;
        $tempat = null;

        if ($request->tipe == 'online') {
            $tempat    = 'Meeting Online';
            $link      = $request->join_url;
            $meetingId = $request->zoom_meeting_id;
            $passcode  = $request->password;
        } else {
            // Kalau Offline
            if ($request->lokasi_type == 'bcc') {
                $tempat = 'Banjarmasin Command Center (BCC)';
            } else {
                $placeData = Place::find($request->place_id);
                $tempat = $placeData ? $placeData->name : 'Tempat Tidak Dikenal';
            }
        }

        // SIMPAN KE DATABASE
        Meeting::create([
            'agency'          => $request->agency,
            'description'     => $request->description,
            'start_time'      => $request->tanggal . ' ' . $request->jam . ':00',
            'duration'        => $request->duration,
            'place'           => $tempat,
            'join_url'        => $link,
            'zoom_meeting_id' => $meetingId, 
            'password'        => $passcode,
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
        // VALIDASI KETAT JUGA DI UPDATE
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

        // CEK BENTROK
        $newStart = Carbon::parse($request->tanggal . ' ' . $request->jam . ':00');
        $newEnd   = $newStart->copy()->addMinutes($request->duration);

        $bentrok = Meeting::where('id', '!=', $id)
            ->where(function ($query) use ($newStart, $newEnd) {
                $query->where('start_time', '<', $newEnd)
                      ->whereRaw("DATE_ADD(start_time, INTERVAL duration MINUTE) > ?", [$newStart]);
            })->exists();

        if ($bentrok) {
            return back()->withInput()->with('error', 'GAGAL UPDATE! Jadwal bentrok dengan meeting lain.');
        }

        $meeting = Meeting::find($id);

        // LOGIKA UPDATE
        $link = null;
        $meetingId = null;
        $passcode = null;
        $tempat = null;

        if ($request->tipe == 'online') {
            $tempat    = 'Meeting Online';
            $link      = $request->join_url;
            $meetingId = $request->zoom_meeting_id;
            $passcode  = $request->password;
        } else {
            // Kalau Offline
            if ($request->lokasi_type == 'bcc') {
                $tempat = 'Banjarmasin Command Center (BCC)';
            } else {
                // Logic Perbaikan: Pastikan nama tempat terupdate
                $placeData = Place::find($request->place_id);
                if ($placeData) {
                    $tempat = $placeData->name;
                } else {
                    // Fallback: Jika tempat lama bukan 'Meeting Online', pakai lama.
                    $tempat = ($meeting->place != 'Meeting Online') ? $meeting->place : 'Tempat Belum Dipilih';
                }
            }
        }

        // UPDATE DATABASE
        $meeting->update([
            'agency'          => $request->agency,
            'description'     => $request->description,
            'start_time'      => $request->tanggal . ' ' . $request->jam . ':00',
            'duration'        => $request->duration,
            'place'           => $tempat,
            'join_url'        => $link,
            'zoom_meeting_id' => $meetingId,
            'password'        => $passcode
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

    // HALAMAN PUBLIK (SHARE)
    public function showPublic($encrypted_id)
    {
        try {
            // Pecahkan Enkripsi
            $id = Crypt::decryptString($encrypted_id);
            
            // Cari Datanya
            $meeting = Meeting::findOrFail($id);

            // Tampilkan View Khusus
            return view('meetings.share', compact('meeting'));

        } catch (\Exception $e) {
            abort(404);
        }
    }
}