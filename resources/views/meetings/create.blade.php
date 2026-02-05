<!DOCTYPE html>
<html>
<head>
    <title>Buat Jadwal Baru</title>
    <link rel="icon" href="{{ asset('images/KotaBanjarmasin.png') }}" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>

<body class="p-3 p-md-5" style="background-color: #f8f9fa;">
    
    <div class="container" style="max-width: 600px;">
        <div class="d-flex align-items-center justify-content-center gap-3 mb-4 text-center">
            <img src="{{ asset('images/KotaBanjarmasin.png') }}" alt="Logo Kota" style="height: 50px; width: auto;">
            <h2 class="fw-bold m-0 fs-4 fs-md-2">Buat Jadwal Meeting</h2>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                ✅ {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <strong>⛔ Gagal Simpan!</strong> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card shadow border-0 rounded-4">
            <div class="card-body p-4">
                <form action="{{ route('simpan.meeting') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tipe Meeting</label>
                        <select name="tipe" id="tipeSelect" class="form-select" onchange="aturTampilanForm()">
                            <option value="online">Meeting Online</option>
                            <option value="offline">Meeting</option>
                        </select>
                    </div>

                    <div id="rowLink" style="display: block;">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Link Meeting (URL)</label>
                            <input type="url" name="join_url" class="form-control" 
                                   placeholder="Tempel Link Zoom/Gmeet disini..." 
                                   value="{{ old('join_url') }}">
                            <small class="text-muted d-block mt-1">
                                *Buat meeting dulu di aplikasi, lalu copy link-nya kesini.
                            </small>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Meeting ID (Opsional)</label>
                                <input type="text" name="zoom_meeting_id" class="form-control" 
                                       placeholder="Contoh: 123 4567 8910" value="{{ old('zoom_meeting_id') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Passcode (Opsional)</label>
                                <input type="text" name="password" class="form-control" 
                                       placeholder="Contoh: 123456" value="{{ old('password') }}">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3" id="rowPilihLokasi" style="display: none;">
                        <label class="form-label fw-bold">Pilih Lokasi</label>
                        <select name="lokasi_type" id="lokasiSelect" class="form-select" onchange="aturTampilanForm()">
                            <option value="bcc">Di Ruang BCC</option>
                            <option value="luar">Diluar BCC (Tempat Lain)</option>
                        </select>
                    </div>

                    <div class="mb-3" id="rowTempatDatabase" style="display: none;">
                        <label class="form-label fw-bold">Pilih Lokasi Luar</label>
                        <select name="place_id" class="form-select">
                            <option value="" disabled selected>-- Pilih Daftar Tempat --</option>
                            
                            @foreach($places as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                            
                        </select>
                        <small class="text-muted d-block mt-1">
                            *Tempat belum ada? <a href="{{ route('create.place') }}" class="fw-bold text-decoration-none">Tambah Disini</a>
                        </small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Dinas / Instansi Permintaan</label>
                        <input type="text" name="agency" class="form-control" 
                               placeholder="Contoh: Dinas Kesehatan, Bappeda..." 
                               required value="{{ old('agency') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Deskripsi / Topik Pembahasan</label>
                        <textarea name="description" class="form-control" rows="3" 
                                  placeholder="Jelaskan detail yang akan dibahas..." required>{{ old('description') }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Permintaan Tambahan (Opsional)</label>
                        <textarea name="additional_requests" class="form-control" rows="2" 
                                  placeholder="Silahkan isi permintaan tambahan...(Kosongkan jika tidak ada)">{{ old('additional_requests') }}</textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-7 mb-3">
                            <label class="form-label fw-bold">Tanggal</label>
                            <input type="date" name="tanggal" class="form-control" required value="{{ old('tanggal') }}">
                        </div>
                        <div class="col-md-5 mb-3">
                            <label class="form-label fw-bold">Jam (WITA)</label>
                            <input type="text" name="jam" class="form-control timepicker" placeholder="Pilih Jam..." required value="{{ old('jam') }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Durasi (Menit)</label>
                        <input type="number" name="duration" class="form-control" value="{{ old('duration', 60) }}" required>
                    </div>

                    <hr class="my-4">

                    <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                        <a href="{{ url('/') }}" class="btn btn-secondary px-4 fw-bold">Kembali</a>
                        <button type="submit" class="btn btn-primary px-4 fw-bold">Simpan Jadwal</button>
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

        // LOGIKA TAMPILAN DINAMIS
        function aturTampilanForm() {
            var tipe = document.getElementById('tipeSelect').value;
            var lokasi = document.getElementById('lokasiSelect').value;
            var rowLink = document.getElementById('rowLink');
            var rowPilihLokasi = document.getElementById('rowPilihLokasi');
            var rowTempatDatabase = document.getElementById('rowTempatDatabase');

            if (tipe === 'online') {
                // Kalo Online: Munculkan Link, Sembunyikan urusan lokasi
                rowLink.style.display = 'block';
                rowPilihLokasi.style.display = 'none';
                rowTempatDatabase.style.display = 'none';
            } else {
                // Kalo Offline: Sembunyikan Link, Munculkan Pilihan Lokasi
                rowLink.style.display = 'none';
                rowPilihLokasi.style.display = 'block';

                // Cek lagi, BCC atau Luar?
                if (lokasi === 'luar') {
                    rowTempatDatabase.style.display = 'block';
                } else {
                    rowTempatDatabase.style.display = 'none';
                }
            }
        }

        // Jalankan saat load
        document.addEventListener('DOMContentLoaded', function() {
            aturTampilanForm();
        });
    </script>

    <footer class="text-center mt-5 mb-4 text-muted">
            <small>
                &copy; {{ date('Y') }} 
                <a href="https://diskominfotik.banjarmasinkota.go.id/" target="_blank" class="text-decoration-none text-secondary fw-bold">
                    Pemerintah Kota Banjarmasin
                </a>
                <br>
                <a href="https://www.instagram.com/rezarevaldyy" target="_blank" class="text-decoration-none text-muted">
                    Developed with <span class="text-danger">❤️</span>
                </a>
            </small>
    </footer>
    
</body>
</html>