<!DOCTYPE html>
<html>
<head>
    <title>Tambah User Baru</title>
    <link rel="icon" href="{{ asset('images/KotaBanjarmasin.png') }}" type="image/png">
    
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="p-3 p-md-5" style="background-color: #f4f6f9;">
    
    <div class="container" style="max-width: 500px;">
        
        <div class="d-flex align-items-center justify-content-center gap-3 mb-4 text-center">
            <img src="{{ asset('images/KotaBanjarmasin.png') }}" alt="Logo Kota" style="height: 50px; width: auto;">
            <h3 class="fw-bold m-0 fs-4 fs-md-3">Tambah Pengguna Baru</h3>
        </div>

        <div class="card shadow border-0 rounded-4">
            <div class="card-body p-4">
                <form action="{{ route('store.user') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Lengkap</label>
                        <input type="text" name="name" class="form-control" placeholder="Masukkan Nama Lengkap..." required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Alamat Email</label>
                        <input type="email" name="email" class="form-control" placeholder="Masukkan Email..." required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Password</label>
                        <input type="text" name="password" class="form-control" placeholder="Masukkan Password..." required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Hak Akses (Role)</label>
                        <select name="role" class="form-select">
                            <option value="user">User (Biasa)</option>
                            <option value="admin">Admin (Full Akses)</option>
                        </select>
                    </div>

                    <hr class="my-4">

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success fw-bold py-2">Simpan User</button>
                        <a href="{{ url('/') }}" class="btn btn-outline-secondary py-2">Batal</a>
                    </div>

                </form>
            </div>
        </div>

    </div>

    <footer class="text-center mt-5 mb-4 text-muted">
            <small>
                &copy; {{ date('Y') }} 
                <a href="https://diskominfotik.banjarmasinkota.go.id/" target="_blank" class="text-decoration-none text-secondary fw-bold">
                    Pemerintah Kota Banjarmasin
                </a>
                <br>
                <a href="https://t.me/KasaJen" target="_blank" class="text-decoration-none text-muted">
                    Developed with <span class="text-danger">❤️</span>
                </a>
            </small>
    </footer>
    
</body>
</html>