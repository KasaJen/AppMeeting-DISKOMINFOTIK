<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Meeting;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class MeetingController extends Controller
{
    public function index()
    {
        $meetings = Meeting::orderBy('created_at', 'desc')->get();

        $events = $meetings->map(function($item) {
            // Biru = Online, Hijau = Offline
            $warna = $item->join_url ? '#3788d8' : '#198754';

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
        // 1. CEK BENTROK (Berlaku untuk Online maupun Offline)
        $newStart = Carbon::parse($request->tanggal . ' ' . $request->jam . ':00');
        $newEnd   = $newStart->copy()->addMinutes($request->duration);

        $bentrok = Meeting::where(function ($query) use ($newStart, $newEnd) {
            $query->where('start_time', '<', $newEnd)
                  ->whereRaw("DATE_ADD(start_time, INTERVAL duration MINUTE) > ?", [$newStart]);
        })->exists();

        if ($bentrok) {
            return back()->withInput()->with('error', 'GAGAL! Jadwal bentrok dengan meeting lain di jam tersebut.');
        }

        // ONLINE / OFFLINE?
        if ($request->tipe == 'online') {
            
            // ONLINE (Create Zoom)
            $accessToken = $this->getZoomAccessToken();
            $waktu_gabungan = $request->tanggal . 'T' . $request->jam . ':00';

            $zoomData = [
                'topic'      => $request->topic,
                'type'       => 2,
                'start_time' => $waktu_gabungan,
                'duration'   => $request->duration,
                'timezone'   => 'Asia/Makassar',
                'settings'   => ['join_before_host' => false, 'waiting_room' => true]
            ];

            $email = 'rezarevaldy007@gmail.com'; 
            $response = Http::withToken($accessToken)->post("https://api.zoom.us/v2/users/{$email}/meetings", $zoomData);

            if ($response->successful()) {
                $data = $response->json();

                Meeting::create([
                    'topic'           => $request->topic,
                    'start_time'      => $request->tanggal . ' ' . $request->jam . ':00',
                    'duration'        => $request->duration,
                    'join_url'        => $data['join_url'],
                    'zoom_meeting_id' => $data['id'],
                    'password'        => $data['password'] ?? null,
                ]);
            } else {
                return back()->withInput()->with('error', 'Gagal koneksi ke Zoom API.');
            }

        } else {

            // OFFLINE (Tatap Muka)
            Meeting::create([
                'topic'           => $request->topic,
                'start_time'      => $request->tanggal . ' ' . $request->jam . ':00',
                'duration'        => $request->duration,
                'join_url'        => null,
                'zoom_meeting_id' => null,
                'password'        => null,
            ]);
        }

        return redirect('/')->with('success', 'Jadwal berhasil dibuat!');
    }

    public function edit($id)
    {
        $meeting = Meeting::find($id);
        return view('meetings.edit', compact('meeting'));
    }

    public function destroy($id)
    {
        $meeting = Meeting::find($id);

        if ($meeting) {
            // Hapus Zoom HANYA JIKA meeting tersebut Online (punya ID Zoom)
            if (!empty($meeting->zoom_meeting_id) && $meeting->zoom_meeting_id != '123456') {
                try {
                    $accessToken = $this->getZoomAccessToken();
                    Http::withToken($accessToken)
                        ->delete("https://api.zoom.us/v2/meetings/{$meeting->zoom_meeting_id}");
                } catch (\Exception $e) {}
            }

            $meeting->delete();
        }
        
        return redirect()->back()->with('success', 'Jadwal berhasil dihapus!');
    }

    public function update(Request $request, $id)
    {
        // Cek Bentrok Update
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

        // Update Zoom (Hanya jika meeting ini tipe Online/Punya ID Zoom)
        if (!empty($meeting->zoom_meeting_id) && $meeting->zoom_meeting_id != '123456') {
            $waktu_gabungan_zoom = $request->tanggal . 'T' . $request->jam . ':00';
            
            $accessToken = $this->getZoomAccessToken();
            $zoomData = [
                'topic'      => $request->topic,
                'start_time' => $waktu_gabungan_zoom,
                'duration'   => $request->duration,
                'timezone'   => 'Asia/Makassar',
            ];

            try {
                Http::withToken($accessToken)
                    ->patch("https://api.zoom.us/v2/meetings/{$meeting->zoom_meeting_id}", $zoomData);
            } catch (\Exception $e) {}
        }

        // Update Database
        $meeting->update([
            'topic'      => $request->topic,
            'start_time' => $request->tanggal . ' ' . $request->jam . ':00',
            'duration'   => $request->duration,
        ]);

        return redirect('/')->with('success', 'Jadwal berhasil diperbarui!');
    }

    private function getZoomAccessToken()
    {
        $accountId = env('ZOOM_ACCOUNT_ID');
        $clientId  = env('ZOOM_CLIENT_ID');
        $clientSecret = env('ZOOM_CLIENT_SECRET');

        $response = Http::withBasicAuth($clientId, $clientSecret)
            ->post("https://zoom.us/oauth/token?grant_type=account_credentials&account_id={$accountId}");

        if ($response->successful()) {
            return $response->json()['access_token'];
        } else {
            dd('Gagal dapat Token!', $response->json());
        }
    }
}