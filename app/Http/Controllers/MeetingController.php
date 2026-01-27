<?php

namespace App\Http\Controllers;

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
        
        $place = Place::create(['name' => $request->name]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'data' => $place]);
        }

        return redirect('/')->with('success', 'Tempat baru berhasil ditambahkan!');
    }

    // HALAMAN BUAT JADWAL
    public function create()
    {
        $places = Place::all(); 
        return view('meetings.create', compact('places'));
    }

    public function store(Request $request)
    {
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
            
            // Data Online
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
                $placeData = Place::find($request->place_id);
                $tempat = $placeData ? $placeData->name : $meeting->place;
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
}