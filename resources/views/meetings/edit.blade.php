<!DOCTYPE html>
<html>
<head>
    <title>Edit Jadwal Meeting</title>
    <link rel="icon" href="{{ asset('images/KotaBanjarmasin.png') }}" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>

<body class="p-3 p-md-5" style="background-color: #f8f9fa;">
    
    <div class="container" style="max-width: 600px;">
        <div class="d-flex align-items-center justify-content-center gap-3 mb-4 text-center">
            <img src="{{ asset('images/KotaBanjarmasin.png') }}" alt="Logo Kota" style="height: 50px; width: auto;">
            <h2 class="fw-bold m-0 fs-4 fs-md-2">Edit Jadwal Meeting</h2>
        </div>

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <strong>⛔ Gagal Update!</strong> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card shadow border-0 rounded-4">
            <div class="card-body p-4">
                <form action="{{ route('update.meeting', $meeting->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    @php 
                        $isOnline = !empty($meeting->join_url);
                        $isBCC = ($meeting->place == 'Banjarmasin Command Center (BCC)');
                    @endphp

                    <div class="mb-3">
                        <label class="form-label fw-bold">Tipe Meeting</label>
                        <select name="tipe" id="tipeSelect" class="form-select" onchange="aturTampilanForm()">
                            <option value="online" {{ $isOnline ? 'selected' : '' }}>Meeting Online</option>
                            <option value="offline" {{ !$isOnline ? 'selected' : '' }}>Meeting</option>
                        </select>
                    </div>

                    <div id="rowLink" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Link Meeting (URL)</label>
                            <input type="url" name="join_url" class="form-control" 
                                   placeholder="Tempel Link Zoom/Gmeet disini..." 
                                   value="{{ old('join_url', $meeting->join_url) }}">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Meeting ID (Opsional)</label>
                                <input type="text" name="zoom_meeting_id" class="form-control" 
                                       placeholder="ID Meeting" 
                                       value="{{ old('zoom_meeting_id', $meeting->zoom_meeting_id) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Passcode (Opsional)</label>
                                <input type="text" name="password" class="form-control" 
                                       placeholder="Passcode" 
                                       value="{{ old('password', $meeting->password) }}">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3" id="rowPilihLokasi" style="display: none;">
                        <label class="form-label fw-bold">Pilih Lokasi</label>
                        <select name="lokasi_type" id="lokasiSelect" class="form-select" onchange="aturTampilanForm()">
                            <option value="bcc" {{ $isBCC ? 'selected' : '' }}>Di Ruang BCC</option>
                            <option value="luar" {{ (!$isBCC && !$isOnline) ? 'selected' : '' }}>Diluar BCC (Tempat Lain)</option>
                        </select>
                    </div>

                    <div class="mb-3" id="rowTempatDatabase" style="display: none;">
                        <label class="form-label fw-bold">Pilih Lokasi Luar</label>
                        <select name="place_id" class="form-select">
                            <option value="" disabled>-- Pilih Daftar Tempat --</option>
                            
                            @foreach($places as $p)
                                <option value="{{ $p->id }}" {{ $p->name == $meeting->place ? 'selected' : '' }}>
                                    {{ $p->name }}
                                </option>
                            @endforeach
                            
                        </select>
                        <small class="text-muted d-block mt-1">
                            *Tempat belum ada? <a href="{{ route('create.place') }}" class="fw-bold text-decoration-none">Tambah Disini</a>
                        </small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Dinas / Instansi Permintaan</label>
                        <input type="text" name="agency" class="form-control" 
                               placeholder="Contoh: Dinas Kesehatan..." 
                               required value="{{ old('agency', $meeting->agency) }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Deskripsi / Topik Pembahasan</label>
                        <textarea name="description" class="form-control" rows="3" 
                                  placeholder="Jelaskan detail yang akan dibahas..." required>{{ old('description', $meeting->description) }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Permintaan Tambahan (Opsional)</label>
                        <textarea name="additional_requests" class="form-control" rows="2" 
                                  placeholder="Silahkan isi permintaan tambahan...(Kosongkan jika tidak ada)">{{ old('additional_requests', $meeting->additional_requests ?? '') }}</textarea>
                    </div>

                    @php
                        $tglValue = date('Y-m-d', strtotime($meeting->start_time));
                        $jamValue = date('H:i', strtotime($meeting->start_time));
                    @endphp
                    
                    <div class="row">
                        <div class="col-md-7 mb-3">
                            <label class="form-label fw-bold">Tanggal</label>
                            <input type="date" name="tanggal" class="form-control" required value="{{ old('tanggal', $tglValue) }}">
                        </div>
                        <div class="col-md-5 mb-3">
                            <label class="form-label fw-bold">Jam (WITA)</label>
                            <input type="text" name="jam" class="form-control timepicker" required value="{{ old('jam', $jamValue) }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Durasi (Menit)</label>
                        <input type="number" name="duration" class="form-control" value="{{ old('duration', $meeting->duration) }}" required>
                    </div>

                    <hr class="my-4">

                    <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                        <a href="{{ url('/') }}" class="btn btn-secondary px-4 fw-bold">Batal</a>
                        <button type="submit" class="btn btn-warning px-4 fw-bold">Update Jadwal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    
    <script>
        flatpickr(".timepicker", {
            enableTime: true, noCalendar: true, dateFormat: "H:i", time_24hr: true, disableMobile: true
        });

        function aturTampilanForm() {
            var tipe = document.getElementById('tipeSelect').value;     
            var lokasi = document.getElementById('lokasiSelect').value; 

            var rowLink = document.getElementById('rowLink');
            var rowPilihLokasi = document.getElementById('rowPilihLokasi');
            var rowTempatDatabase = document.getElementById('rowTempatDatabase');

            if (tipe === 'online') {
                rowLink.style.display = 'block';
                rowPilihLokasi.style.display = 'none';
                rowTempatDatabase.style.display = 'none';
            } else {
                rowLink.style.display = 'none';
                rowPilihLokasi.style.display = 'block';

                if (lokasi === 'luar') {
                    rowTempatDatabase.style.display = 'block';
                } else {
                    rowTempatDatabase.style.display = 'none';
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            aturTampilanForm();
        });
    </script>

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