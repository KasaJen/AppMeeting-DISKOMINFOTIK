<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Meeting;
use Carbon\Carbon;

class MeetingController extends Controller
{
    public function index()
    {
        $meetings = Meeting::orderBy('created_at', 'desc')->get();

        $events = $meetings->map(function($item) {
            // Logika Warna: Kalau ada Link Zoom = Biru (#3788d8), Kalau Offline = Hijau (#198754)
            $warna = !empty($item->join_url) ? '#3788d8' : '#198754';

            return [
                'id' => $item->id,
                'title' => $item->topic,
                'start' => $item->start_time,
                'end'   => date('Y-m-d H:i:s', strtotime($item->start_time) + ($item->duration * 60)),
                'color' => $warna, 
                
                'extendedProps' => [
                    'durasi' => $item->duration,
                    'join_url' => $item->join_url,
                    'edit_url' => route('edit.meeting', $item->id),
                    'delete_id' => $item->id 
                ]
            ];
        });

        return view('meetings.index', compact('events'));
    }

    public function create()
    {
        return view('meetings.create');
    }

    public function store(Request $request)
    {
        // 1. CEK BENTROK JADWAL
        $newStart = Carbon::parse($request->tanggal . ' ' . $request->jam . ':00');
        $newEnd   = $newStart->copy()->addMinutes($request->duration);

        $bentrok = Meeting::where(function ($query) use ($newStart, $newEnd) {
            $query->where('start_time', '<', $newEnd)
                  ->whereRaw("DATE_ADD(start_time, INTERVAL duration MINUTE) > ?", [$newStart]);
        })->exists();

        if ($bentrok) {
            return back()->withInput()->with('error', 'GAGAL! Jadwal bentrok dengan meeting lain di jam tersebut.');
        }

        // 2. TENTUKAN LINK ZOOM (Manual)
        // Kalau tipe offline, link dikosongkan (null). Kalau online, ambil inputan user.
        $linkZoom = ($request->tipe == 'online') ? $request->join_url : null;

        // 3. SIMPAN KE DATABASE
        Meeting::create([
            'topic'           => $request->topic,
            'start_time'      => $request->tanggal . ' ' . $request->jam . ':00',
            'duration'        => $request->duration,
            'join_url'        => $linkZoom,       // Simpan link manual
            'zoom_meeting_id' => null,            // Tidak pakai API lagi
            'password'        => null,
        ]);

        return redirect('/')->with('success', 'Jadwal berhasil dibuat!');
    }

    public function edit($id)
    {
        $meeting = Meeting::find($id);
        return view('meetings.edit', compact('meeting'));
    }

    public function update(Request $request, $id)
    {
        // 1. CEK BENTROK (Kecuali dirinya sendiri)
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

        // 2. UPDATE DATA
        // Cek lagi apakah user mengubah tipe jadi Offline/Online
        $linkZoom = ($request->tipe == 'online') ? $request->join_url : null;

        $meeting->update([
            'topic'      => $request->topic,
            'start_time' => $request->tanggal . ' ' . $request->jam . ':00',
            'duration'   => $request->duration,
            'join_url'   => $linkZoom // Update link manual
        ]);

        return redirect('/')->with('success', 'Jadwal berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $meeting = Meeting::find($id);
        if ($meeting) {
            $meeting->delete(); // Hapus database saja (Zoom API sudah dibuang)
        }
        
        return redirect()->back()->with('success', 'Jadwal berhasil dihapus!');
    }
}