<!DOCTYPE html>
<html>
<head>
    <title>Tambah User Baru</title>
    <link rel="icon" href="{{ asset('images/KotaBanjarmasin.png') }}" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-5" style="background-color: #f4f6f9;">
    <div class="container" style="max-width: 500px;">
        
        <h3 class="mb-4 text-center fw-bold">👤 Tambah Pengguna Baru</h3>

        <div class="card shadow border-0">
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
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>

                    <hr class="my-4">

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success fw-bold">Simpan User</button>
                        <a href="{{ url('/') }}" class="btn btn-outline-secondary">Batal</a>
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
                <a href="https://www.instagram.com/rezarevaldyy" target="_blank" class="text-decoration-none text-muted">
                    Developed with <span class="text-danger">❤️</span>
                </a>
            </small>
    </footer>
    
</body>
</html>