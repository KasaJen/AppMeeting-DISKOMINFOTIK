<!DOCTYPE html>
<html>
<head>
    <title>Edit Jadwal Meeting</title>
    <link rel="icon" href="{{ asset('images/KotaBanjarmasin.png') }}" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>
<body class="p-5" style="background-color: #f8f9fa;">
    <div class="container" style="max-width: 600px;">
        
        <h2 class="mb-4 text-center">✏️ Edit Jadwal Meeting</h2>

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <strong>⛔ Gagal Update!</strong> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card shadow">
            <div class="card-body p-4">
                <form action="{{ route('update.meeting', $meeting->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Topik Meeting</label>
                        <input type="text" name="topic" class="form-control" value="{{ old('topic', $meeting->topic) }}" required>
                    </div>

                    @php
                        $waktu_lama = strtotime($meeting->start_time);
                        $tanggal_default = date('Y-m-d', $waktu_lama);
                        $jam_default = date('H:i', $waktu_lama);
                    @endphp

                    <div class="row">
                        <div class="col-md-7 mb-3">
                            <label class="form-label fw-bold">Tanggal</label>
                            <input type="date" name="tanggal" class="form-control" value="{{ old('tanggal', $tanggal_default) }}" required>
                        </div>
                        <div class="col-md-5 mb-3">
                            <label class="form-label fw-bold">Jam (WITA)</label>
                            <input type="text" name="jam" class="form-control timepicker" value="{{ old('jam', $jam_default) }}" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Durasi (Menit)</label>
                        <input type="number" name="duration" class="form-control" value="{{ old('duration', $meeting->duration) }}" required>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-between">
                        <a href="{{ url('/') }}" class="btn btn-secondary px-4">Batal</a>
                        <button type="submit" class="btn btn-warning px-4">Update Jadwal</button>
                    </div>

                </form>
            </div>
        </div>

    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        flatpickr(".timepicker", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true
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