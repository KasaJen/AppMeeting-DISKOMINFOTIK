<!DOCTYPE html>
<html>
<head>
    <title>Buat Jadwal Baru</title>
    <link rel="icon" href="{{ asset('images/KotaBanjarmasin.png') }}" type="image/png">
    
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">

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
                        <select name="tipe" id="tipeSelect" class="form-select" onchange="toggleLinkInput()">
                            <option value="online">Online</option>
                            <option value="offline">Offline (Tatap Muka)</option>
                        </select>
                    </div>

                    <div class="mb-3" id="linkInputRow">
                        <label class="form-label fw-bold">Link Meeting (URL)</label>
                        <input type="url" name="join_url" class="form-control" 
                               placeholder="Tempel Link Zoom/Gmeet disini... (https://...)" 
                               value="{{ old('join_url') }}">
                        <small class="text-muted d-block mt-1">
                            *Silahkan buat meeting terlebih dahulu, lalu copy-paste kesini.
                        </small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Topik Meeting</label>
                        <input type="text" name="topic" class="form-control" placeholder="Isi Topik Meeting..." required value="{{ old('topic') }}">
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
        // Konfigurasi Flatpickr (Jam)
        flatpickr(".timepicker", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true,
            disableMobile: true
        });

        // Fungsi Muncul/Hilang Input Link
        function toggleLinkInput() {
            var tipe = document.getElementById('tipeSelect').value;
            var linkRow = document.getElementById('linkInputRow');

            if(tipe === 'online') {
                linkRow.style.display = 'block';
            } else {
                linkRow.style.display = 'none';
            }
        }

        // Jalankan saat load pertama kali
        document.addEventListener('DOMContentLoaded', function() {
            toggleLinkInput();
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