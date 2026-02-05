<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Kegiatan - {{ $meeting->agency }}</title>
    <link rel="icon" href="{{ asset('images/KotaBanjarmasin.png') }}" type="image/png">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        body { background-color: #f0f2f5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .ticket-container { max-width: 600px; margin: 40px auto; background: white; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.08); overflow: hidden; border: 1px solid #e1e4e8; }
        .ticket-header { background: #ffffff; padding: 25px; text-align: center; border-bottom: 2px dashed #e1e4e8; position: relative; }
        
        /* Efek Bolong Tiket */
        .ticket-header::after { content: ""; position: absolute; bottom: -10px; left: -10px; width: 20px; height: 20px; background: #f0f2f5; border-radius: 50%; }
        .ticket-header::before { content: ""; position: absolute; bottom: -10px; right: -10px; width: 20px; height: 20px; background: #f0f2f5; border-radius: 50%; }

        .ticket-label { font-size: 11px; color: #8898aa; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 5px; }
        .ticket-value { font-size: 15px; color: #212529; font-weight: 600; line-height: 1.5; }
        .info-row { padding: 18px 25px; border-bottom: 1px solid #f0f0f0; }
        .info-row:last-child { border-bottom: none; }
        
        .bg-soft { background-color: #f8f9fe; }
        
        .btn-join { background-color: #0d6efd; color: white; border-radius: 8px; padding: 12px; font-weight: bold; transition: all 0.2s; }
        .btn-join:hover { background-color: #0b5ed7; transform: translateY(-2px); box-shadow: 0 4px 10px rgba(13, 110, 253, 0.3); color: white; }
        
        .credential-box { background: #eef2f7; border-radius: 8px; padding: 10px; text-align: center; border: 1px solid #dee2e6; }
        .credential-label { font-size: 10px; color: #6c757d; text-transform: uppercase; font-weight: bold; }
        .credential-value { font-size: 16px; font-family: monospace; color: #333; font-weight: bold; letter-spacing: 1px; }
    </style>
</head>
<body>

    <div class="container px-3">
        <div class="ticket-container">
            <div class="ticket-header">
                <img src="{{ asset('images/KotaBanjarmasin.png') }}" alt="Logo" style="height: 65px; margin-bottom: 15px;">
                <h5 class="fw-bold m-0 text-dark">JADWAL KEGIATAN</h5>
                <small class="text-muted">Banjarmasin Command Center</small>
            </div>

            <div class="info-row bg-soft">
                <div class="ticket-label">Instansi / Dinas</div>
                <div class="ticket-value text-primary">{{ $meeting->agency }}</div>
            </div>

            <div class="info-row">
                <div class="ticket-label">Topik Pembahasan</div>
                <div class="ticket-value text-break">{{ $meeting->description }}</div>
            </div>

            @if($meeting->additional_requests)
                <div class="info-row" style="background-color: #fff3cd;">
                    <div class="ticket-label text-warning-emphasis">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Permintaan Tambahan
                    </div>
                    <div class="ticket-value text-dark small" style="white-space: pre-line;">{{ $meeting->additional_requests }}</div>
                </div>
            @endif

            <div class="row m-0">
                <div class="col-md-6 info-row bg-soft border-end">
                    <div class="ticket-label">Tanggal</div>
                    <div class="ticket-value">
                        {{ \Carbon\Carbon::parse($meeting->start_time)->translatedFormat('l, d F Y') }}
                    </div>
                </div>
                <div class="col-md-6 info-row bg-soft">
                    <div class="ticket-label">Waktu</div>
                    <div class="ticket-value">
                        {{ date('H:i', strtotime($meeting->start_time)) }} WITA 
                        <span class="badge bg-secondary rounded-pill ms-1" style="font-size: 10px; vertical-align: middle;">{{ $meeting->duration }} Menit</span>
                    </div>
                </div>
            </div>

            <div class="info-row">
                <div class="ticket-label">Lokasi / Link Meeting</div>
                
                @if($meeting->join_url)
                    <div class="mt-2">
                        <a href="{{ $meeting->join_url }}" target="_blank" class="btn btn-join w-100 d-block text-decoration-none text-center mb-3">
                            GABUNG MEETING SEKARANG
                        </a>

                        @if($meeting->zoom_meeting_id || $meeting->password)
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="credential-box">
                                        <div class="credential-label">Meeting ID</div>
                                        <div class="credential-value">{{ $meeting->zoom_meeting_id ?? '-' }}</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="credential-box">
                                        <div class="credential-label">Passcode</div>
                                        <div class="credential-value">{{ $meeting->password ?? '-' }}</div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="ticket-value fs-5 mt-1">
                        {{ $meeting->place }}
                    </div>
                @endif
            </div>
            
            <div class="info-row bg-soft text-center py-4">
                <small class="text-muted d-block" style="font-size: 11px;">
                    Simpan link ini untuk melihat detail jadwal sewaktu-waktu.
                </small>
            </div>

        </div>
    </div>

    <footer class="text-center mt-5 mb-4 text-muted">
        <small>
            &copy; {{ date('Y') }} 
            <a href="https://diskominfotik.banjarmasinkota.go.id/" target="_blank" class="text-decoration-none text-secondary fw-bold">Pemerintah Kota Banjarmasin</a>
            <br>
            <a href="https://www.instagram.com/rezarevaldyy" target="_blank" class="text-decoration-none text-muted">Developed with <span class="text-danger">❤️</span></a>
        </small>
    </footer>

</body>
</html>