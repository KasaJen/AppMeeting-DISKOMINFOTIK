<!DOCTYPE html>
<html>
<head>
    <title>Tambah Tempat Baru</title>
    <link rel="icon" href="{{ asset('images/KotaBanjarmasin.png') }}" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="p-3 p-md-5" style="background-color: #f4f6f9;">
    
    <div class="container" style="max-width: 500px;">
        
        <div class="d-flex align-items-center justify-content-center gap-3 mb-4 text-center">
            <img src="{{ asset('images/KotaBanjarmasin.png') }}" alt="Logo Kota" style="height: 50px; width: auto;">
            <h3 class="fw-bold m-0 fs-4 fs-md-3">Tambah Master Tempat</h3>
        </div>

        <div class="card shadow border-0 rounded-4">
            <div class="card-body p-4">
                <form id="formTempat" onsubmit="event.preventDefault(); simpanTempat();">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Tempat / Ruangan</label>
                        <input type="text" id="inputNama" class="form-control" placeholder="Contoh: Aula Kayuh Baimbai" required autofocus>
                        <div class="form-text text-muted">Masukkan nama tempat meeting diluar kantor.</div>
                    </div>

                    <hr class="my-4">

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success fw-bold py-2">Simpan Tempat</button>
                        <a href="{{ url('/') }}" class="btn btn-outline-secondary py-2">Kembali</a>
                    </div>

                </form>
            </div>
        </div>

    </div>

    <script>
        function simpanTempat() {
            var inputNama = document.getElementById('inputNama');
            var name = inputNama.value;
            var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            if(!name) return;

            // Tampilkan Loading
            Swal.fire({
                title: 'Menyimpan...',
                didOpen: () => { Swal.showLoading() }
            });

            // Kirim ke Server via AJAX
            fetch("{{ route('store.place') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": token,
                    "X-Requested-With": "XMLHttpRequest"
                },
                body: JSON.stringify({ name: name })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'exist') {
                    // Gagal (Sudah Ada)
                    Swal.fire({
                        icon: 'info',
                        title: 'Tempat Sudah Terdaftar!',
                        text: 'Tempat dengan nama ini sudah ada. Silahkan gunakan nama lain.',
                        confirmButtonText: 'Oke',
                        confirmButtonColor: '#0dcaf0'
                    });
                } else {
                    // Sukses
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Tempat baru berhasil ditambahkan.',
                        showConfirmButton: true,
                        confirmButtonText: 'Lanjut',
                        confirmButtonColor: '#198754'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            inputNama.value = '';
                            inputNama.focus();
                        }
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
            });
        }
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