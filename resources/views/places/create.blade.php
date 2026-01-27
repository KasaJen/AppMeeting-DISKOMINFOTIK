<!DOCTYPE html>
<html>
<head>
    <title>Tambah Tempat Baru</title>
    <link rel="icon" href="{{ asset('images/KotaBanjarmasin.png') }}" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-3 p-md-5" style="background-color: #f4f6f9;">
    
    <div class="container" style="max-width: 500px;">
        
        <div class="d-flex align-items-center justify-content-center gap-3 mb-4 text-center">
            <img src="{{ asset('images/KotaBanjarmasin.png') }}" alt="Logo Kota" style="height: 50px; width: auto;">
            <h3 class="fw-bold m-0 fs-4 fs-md-3">Tambah Master Tempat</h3>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card shadow border-0 rounded-4">
            <div class="card-body p-4">
                <form action="{{ route('store.place') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Tempat / Ruangan</label>
                        <input type="text" name="name" class="form-control" placeholder="Contoh: Aula Kayuh Baimbai" required autofocus>
                        <div class="form-text text-muted">Masukkan nama tempat meeting diluar kantor.</div>
                    </div>

                    <hr class="my-4">

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success fw-bold py-2">Simpan Tempat</button>
                        <a href="{{ url('/') }}" class="btn btn-outline-secondary py-2">Batal</a>
                    </div>

                </form>
            </div>
        </div>

    </div>
    
</body>
</html>