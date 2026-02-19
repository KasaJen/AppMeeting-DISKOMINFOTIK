<!DOCTYPE html>
<html>
<head>
    <title>Tambah Tempat Baru</title>
    <link rel="icon" href="{{ asset('images/KotaBanjarmasin.png') }}" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body { background-color: #f4f6f9; }
        /* Membatasi tinggi tabel biar tidak terlalu panjang ke bawah */
        .table-wrapper { max-height: 300px; overflow-y: auto; }
        .table-wrapper::-webkit-scrollbar { width: 6px; }
        .table-wrapper::-webkit-scrollbar-thumb { background-color: #ccc; border-radius: 4px; }
    </style>
</head>
<body class="p-3 p-md-5">
    
    <div class="container" style="max-width: 600px;">
        
        <div class="d-flex align-items-center justify-content-center gap-3 mb-4 text-center">
            <img src="{{ asset('images/KotaBanjarmasin.png') }}" alt="Logo Kota" style="height: 55px; width: auto;">
            <div>
                <h3 class="fw-bold m-0 text-dark fs-4">Master Data Tempat</h3>
            </div>
        </div>

        <div class="card shadow border-0 rounded-4 mb-4">
            <div class="card-body p-4">
                <form id="formTempat" onsubmit="event.preventDefault(); simpanTempat();">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Tempat / Ruangan</label>
                        <input type="text" id="inputNama" class="form-control form-control-lg bg-light" placeholder="Contoh: Aula Kayuh Baimbai" required autofocus>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success fw-bold py-2">
                            <i class="bi bi-plus-lg"></i> Simpan Tempat
                        </button>
                        <a href="{{ url('/') }}" class="btn btn-outline-secondary py-2">Kembali ke Dashboard</a>
                    </div>

                </form>
            </div>
        </div>

        <div class="card shadow border-0 rounded-4">
            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold m-0 text-primary"><i class="bi bi-list-check"></i> Daftar Tempat Tersedia</h6>
                <span class="badge bg-light text-dark border">{{ $places->count() }} Ruangan</span>
            </div>
            
            <div class="card-body p-0">
                <div class="table-wrapper">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th class="ps-4">Nama Tempat</th>
                                <th class="text-end pe-4" style="width: 100px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($places as $place)
                            <tr>
                                <td class="ps-4 fw-bold text-secondary">{{ $place->name }}</td>
                                <td class="text-end pe-4">
                                    <form action="{{ route('delete.place', $place->id) }}" method="POST" onsubmit="return konfirmasiHapus(event, this);">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger border-0 rounded-circle shadow-sm" title="Hapus">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="2" class="text-center py-4 text-muted small">
                                    Belum ada data tempat meeting.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
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
                <a href="https://github.com/KasaJen/" target="_blank" class="text-decoration-none text-muted">
                    Developed with <span class="text-danger">❤️</span>
                </a>
            </small>
    </footer>
    
    </div>

    <script>
        function simpanTempat() {
            var inputNama = document.getElementById('inputNama');
            var name = inputNama.value;
            var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            if(!name) return;

            Swal.fire({ title: 'Menyimpan...', didOpen: () => { Swal.showLoading() } });

            fetch("{{ route('store.place') }}", {
                method: "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": token, "X-Requested-With": "XMLHttpRequest" },
                body: JSON.stringify({ name: name })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'exist') {
                    Swal.fire({ icon: 'warning', title: 'Sudah Ada!', text: data.message });
                } else {
                    Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Tempat ditambahkan.', showConfirmButton: false, timer: 1500 })
                    .then(() => { location.reload(); });
                }
            })
            .catch(error => { Swal.fire('Error', 'Terjadi kesalahan.', 'error'); });
        }

        function konfirmasiHapus(event, form) {
            event.preventDefault();
            Swal.fire({
                title: 'Hapus tempat ini?',
                text: "Data tidak bisa dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) { form.submit(); }
            });
        }

        @if(session('success'))
            Swal.fire({ icon: 'success', title: 'Sukses!', text: "{{ session('success') }}", timer: 2000, showConfirmButton: false });
        @endif
    </script>
    
</body>
</html>