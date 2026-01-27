<!DOCTYPE html>
<html>
<head>
    <title>Jadwal Meeting BCC</title>
    <link rel="icon" href="{{ asset('images/KotaBanjarmasin.png') }}" type="image/png">
    
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        #calendar {
            max-width: 1100px;
            margin: 0 auto;
            background: white;
            padding: 10px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        /* TAMPILAN DESKTOP */
        @media (min-width: 768px) {
            #calendar { padding: 20px; }
            .desktop-title { white-space: nowrap; }
        }

        body { background-color: #f4f6f9; }

        /* Style Event (Desktop) */
        .fc-event-title { font-weight: 600; }
        .fc-event-time { font-weight: bold; margin-right: 5px; }
        .fc-event { cursor: pointer; }
        
        /* Style List View (HP) */
        .fc-list-event-title { font-weight: bold !important; }
        .fc-list-day-text { font-weight: bold; color: #0d6efd; }
        .fc-list-day-side-text { font-weight: bold; color: #6c757d; }
    </style>
</head>

<body class="p-2 p-md-4">
    <div class="container">
        
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
            <div class="d-flex align-items-center gap-3">
                <img src="{{ asset('images/KotaBanjarmasin.png') }}" alt="Logo Kota" style="height: 50px; width: auto;">
                
                <h2 class="fw-bold text-dark m-0 text-center text-md-start fs-4 fs-md-2 desktop-title">
                    Kalender Jadwal Meeting BCC
                </h2>
            </div>
            
            <div class="d-grid gap-2 d-md-flex justify-content-md-end w-100">
                @guest
                    <a href="{{ route('login') }}" class="btn btn-primary fw-bold shadow-sm">Login Admin</a>
                @else
                    <div class="dropdown">
                        <button class="btn btn-light dropdown-toggle shadow-sm w-100" type="button" data-bs-toggle="dropdown">
                            👤 {{ Auth::user()->name }} <span class="badge bg-secondary">{{ Auth::user()->role }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <form id="formLogout" action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="button" onclick="konfirmasiLogout()" class="dropdown-item text-danger">Logout</button>
                                </form>
                            </li>
                        </ul>
                    </div>

                    @if(auth()->user()->role == 'admin')
                        <div class="d-flex gap-2 flex-wrap justify-content-center justify-content-md-end w-100">
                            <a href="{{ route('create.place') }}" class="btn btn-info text-white shadow fw-bold">
                                + Tempat
                            </a>
                            <a href="{{ route('create.user') }}" class="btn btn-success shadow fw-bold">
                                + User
                            </a>
                            <a href="{{ url('/buat-meeting') }}" class="btn btn-primary shadow fw-bold">
                                + Jadwal
                            </a>
                        </div>
                    @endif
                @endguest
            </div>
        </div>

        <div id='calendar'></div>

        <footer class="text-center mt-5 mb-4 text-muted">
            <small>
                &copy; {{ date('Y') }} 
                <a href="https://diskominfotik.banjarmasinkota.go.id/" target="_blank" class="text-decoration-none text-secondary fw-bold">Pemerintah Kota Banjarmasin</a>
                <br>
                <a href="https://www.instagram.com/rezarevaldyy" target="_blank" class="text-decoration-none text-muted">Developed with <span class="text-danger">❤️</span></a>
            </small>
        </footer>

    </div>

    <div class="modal fade" id="eventModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalTitle">Detail Meeting</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="text-muted small fw-bold mb-1">DINAS / INSTANSI:</label>
                        <h4 id="modalTopic" class="fw-bold text-primary m-0"></h4>
                        <div id="modalDesc" class="text-muted mt-2 border-start border-3 border-primary ps-2 fst-italic text-break"></div>
                    </div>
                    
                    <hr>

                    <p><strong>Waktu:</strong> <span id="modalTime"></span></p>
                    <p><strong>Durasi:</strong> <span id="modalDuration"></span> Menit</p>
                    
                    <div class="d-grid gap-2 mt-4" id="actionArea"></div>
                </div>
                <div class="modal-footer justify-content-between d-none" id="adminActions">
                    <form id="formDelete" action="" method="POST">
                        @csrf @method('DELETE')
                        <button type="button" onclick="konfirmasiHapus()" class="btn btn-danger">Hapus</button>
                    </form>
                    <a href="#" id="btnEdit" class="btn btn-warning">Edit Jadwal</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        @if(session('success'))
            Swal.fire({ icon: 'success', title: 'Berhasil!', text: "{{ session('success') }}", confirmButtonText: 'Oke', confirmButtonColor: '#0d6efd' });
        @endif
        @if(session('error'))
            Swal.fire({ icon: 'error', title: 'Gagal!', text: "{{ session('error') }}", confirmButtonText: 'Coba Lagi', confirmButtonColor: '#dc3545' });
        @endif
        
        function konfirmasiLogout() {
            Swal.fire({ title: 'Yakin mau keluar?', icon: 'question', showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#6c757d', confirmButtonText: 'Ya, Keluar' }).then((result) => { if (result.isConfirmed) document.getElementById('formLogout').submit(); });
        }
        function konfirmasiHapus() {
            Swal.fire({ title: 'Yakin hapus jadwal ini?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#6c757d', confirmButtonText: 'Ya, Hapus' }).then((result) => { if (result.isConfirmed) document.getElementById('formDelete').submit(); });
        }

        document.addEventListener('DOMContentLoaded', function() {
            history.pushState(null, null, location.href);
            window.onpopstate = function () { history.go(1); };
            
            const IS_ADMIN = @json(auth()->check() && auth()->user()->role == 'admin');
            var calendarEl = document.getElementById('calendar');

            var initialMobile = window.innerWidth < 768;

            var calendar = new FullCalendar.Calendar(calendarEl, {
                
                initialView: initialMobile ? 'listMonth' : 'dayGridMonth', 
                themeSystem: 'bootstrap5',
                locale: 'id',
                slotLabelFormat: { hour: '2-digit', minute: '2-digit', hour12: false, meridiem: false },
                eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
                
                headerToolbar: {
                    left: initialMobile ? 'prev,next' : 'prev,next today', 
                    center: 'title', 
                    right: initialMobile ? '' : 'dayGridMonth,timeGridWeek,timeGridDay'
                },

                contentHeight: initialMobile ? 'auto' : undefined,

                windowResize: function(view) {
                    var isMobileNow = window.innerWidth < 768;
                    if (isMobileNow) {
                        calendar.changeView('listMonth');
                        calendar.setOption('headerToolbar', { left: 'prev,next', center: 'title', right: '' });
                        calendar.setOption('contentHeight', 'auto');
                    } else {
                        calendar.changeView('dayGridMonth');
                        calendar.setOption('headerToolbar', { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,timeGridDay' });
                        calendar.setOption('contentHeight', undefined);
                    }
                },
                
                events: @json($events),

                eventClick: function(info) {
                    var eventObj = info.event;
                    var props = eventObj.extendedProps;
                    
                    document.getElementById('modalTitle').innerText = 'Detail Jadwal';
                    
                    // Tampilkan Nama Dinas & Deskripsi
                    document.getElementById('modalTopic').innerText = eventObj.title; 
                    document.getElementById('modalDesc').innerText = props.deskripsi ? props.deskripsi : '(Tidak ada deskripsi)';
                    
                    var waktu = eventObj.start.toLocaleString('id-ID', { dateStyle: 'full', timeStyle: 'short' });
                    document.getElementById('modalTime').innerText = waktu;
                    document.getElementById('modalDuration').innerText = props.durasi;
                    
                    var actionArea = document.getElementById('actionArea');
                    
                    if (props.join_url) {
                        // Manual Info Zoom
                        var manualInfo = '';
                        if (props.meeting_id || props.password) {
                            manualInfo = `
                                <div class="bg-light p-2 rounded border mt-2">
                                    <small class="d-block text-muted fw-bold mb-1">INFO DETAIL ZOOM:</small>
                                    <div class="d-flex justify-content-between">
                                        <span>ID Zoom: <strong>${props.meeting_id ? props.meeting_id : '-'}</strong></span>
                                        <span>Password: <strong>${props.password ? props.password : '-'}</strong></span>
                                    </div>
                                </div>
                            `;
                        }

                        var tempatInfo = props.place ? props.place : 'Online';
                        actionArea.innerHTML = `
                            <div class="alert alert-info mb-2"><strong>Lokasi:</strong> ${tempatInfo}</div>
                            <a href="${props.join_url}" target="_blank" class="btn btn-success fw-bold w-100">Gabung Meeting Online</a>
                            ${manualInfo}
                        `;
                    } else {
                        var lokasi = props.place ? props.place : 'Tempat Belum Diisi';
                        actionArea.innerHTML = `
                            <div class="alert alert-secondary text-center">
                                <strong>Lokasi Meeting:</strong><br>
                                <span class="fs-5">${lokasi}</span>
                            </div>
                        `;
                    }
                    
                    var adminDiv = document.getElementById('adminActions');
                    if (IS_ADMIN) {
                        adminDiv.classList.remove('d-none');
                        document.getElementById('btnEdit').href = props.edit_url;
                        document.getElementById('formDelete').action = "{{ url('/meeting') }}" + "/" + props.delete_id;
                    } else {
                        adminDiv.classList.add('d-none');
                    }
                    var myModal = new bootstrap.Modal(document.getElementById('eventModal'));
                    myModal.show();
                }
            });

            calendar.render();
        });
    </script>
</body>
</html>