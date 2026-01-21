<!DOCTYPE html>
<html>
<head>
    <title>Kalender Jadwal Meeting</title>
    <link rel="icon" href="{{ asset('images/KotaBanjarmasin.png') }}" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
    
    <style>
        #calendar {
            max-width: 1100px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        body { background-color: #f4f6f9; }
        
        .fc-daygrid-event {
            margin-top: 4px !important; margin-left: 5px !important; margin-right: 5px !important;
            padding: 4px 8px !important; border-radius: 6px !important; border: none !important;
        }
        .fc-event-title { white-space: normal !important; font-weight: 600; }
        .fc-event-time { font-weight: bold; margin-right: 5px; }
        .fc-event { cursor: pointer; } 
    </style>
</head>
<body class="p-4">
    <div class="container">
        
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
            
            <div class="d-flex align-items-center gap-3">
                <img src="{{ asset('images/KotaBanjarmasin.png') }}" alt="Logo Kota" style="height: 50px; width: auto;">
                <h2 class="fw-bold text-dark m-0 text-center text-md-start">Kalender Jadwal Meeting</h2>
            </div>
            
            <div class="d-grid gap-2 d-md-flex justify-content-md-end w-100">
                @guest
                    <a href="{{ route('login') }}" class="btn btn-primary fw-bold shadow-sm">
                        🔐 Login Admin
                    </a>
                @else
                    <div class="dropdown">
                        <button class="btn btn-light dropdown-toggle shadow-sm w-100" type="button" data-bs-toggle="dropdown">
                            👤 {{ Auth::user()->name }} <span class="badge bg-secondary">{{ Auth::user()->role }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">Logout</button>
                                </form>
                            </li>
                        </ul>
                    </div>

                    @if(auth()->user()->role == 'admin')
                        <div class="d-flex gap-2">
                            <a href="{{ route('create.user') }}" class="btn btn-success shadow w-100">
                                + User
                            </a>
                            <a href="{{ url('/buat-meeting') }}" class="btn btn-primary shadow w-100">
                                + Jadwal
                            </a>
                        </div>
                    @endif
                @endguest
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                ✅ {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                ⛔ {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div id='calendar'></div>

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

    </div>

    <div class="modal fade" id="eventModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalTitle">Detail Meeting</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <h4 id="modalTopic" class="fw-bold mb-3">Topik</h4>
                    <p><strong>🕒 Waktu:</strong> <span id="modalTime"></span></p>
                    <p><strong>⏳ Durasi:</strong> <span id="modalDuration"></span> Menit</p>
                    
                    <div class="d-grid gap-2 mt-4" id="actionArea">
                        </div>
                </div>
                
                <div class="modal-footer justify-content-between d-none" id="adminActions">
                    <form id="formDelete" action="" method="POST" onsubmit="return confirm('Yakin hapus jadwal ini?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </form>
                    <a href="#" id="btnEdit" class="btn btn-warning">Edit Jadwal</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            // FITUR ANTI-BACK (Supaya pas logout gak bisa back ke dalem)
            history.pushState(null, null, location.href);
            window.onpopstate = function () {
                history.go(1);
            };
            
            // Cek apakah user adalah admin (untuk nampilin tombol edit/hapus)
            const IS_ADMIN = @json(auth()->check() && auth()->user()->role == 'admin');

            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                
                // Responsiveness: HP pakai List, Laptop pakai Grid
                initialView: window.innerWidth < 768 ? 'listMonth' : 'dayGridMonth',
                
                themeSystem: 'bootstrap5',
                locale: 'id', // Bahasa Indonesia
                slotLabelFormat: { hour: '2-digit', minute: '2-digit', hour12: false, meridiem: false },
                eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
                
                // Toolbar: Sederhana di HP, Lengkap di Laptop
                headerToolbar: {
                    left: 'prev,next today', 
                    center: 'title', 
                    right: window.innerWidth < 768 ? '' : 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                
                events: @json($events),

                // Saat jadwal diklik
                eventClick: function(info) {
                    var eventObj = info.event;
                    var props = eventObj.extendedProps;

                    // Isi data ke Modal
                    document.getElementById('modalTitle').innerText = 'Detail: ' + eventObj.title;
                    document.getElementById('modalTopic').innerText = eventObj.title;
                    
                    var waktu = eventObj.start.toLocaleString('id-ID', { dateStyle: 'full', timeStyle: 'short' });
                    document.getElementById('modalTime').innerText = waktu;
                    document.getElementById('modalDuration').innerText = props.durasi;
                    
                    // Logika Tombol Zoom vs Offline
                    var actionArea = document.getElementById('actionArea');
                    actionArea.innerHTML = ''; 

                    if (props.join_url) {
                        actionArea.innerHTML = `<a href="${props.join_url}" target="_blank" class="btn btn-success fw-bold">🚀 Gabung Zoom Sekarang</a>`;
                    } else {
                        actionArea.innerHTML = `<div class="alert alert-secondary text-center fw-bold">🏢 Meeting Offline (Tatap Muka)</div>`;
                    }

                    // Logika Tombol Admin
                    var adminDiv = document.getElementById('adminActions');
                    
                    if (IS_ADMIN) {
                        adminDiv.classList.remove('d-none');
                        document.getElementById('btnEdit').href = props.edit_url;
                        var baseUrlDelete = "{{ url('/meeting') }}";
                        document.getElementById('formDelete').action = baseUrlDelete + "/" + props.delete_id;
                    } else {
                        adminDiv.classList.add('d-none');
                    }

                    // Tampilkan Modal
                    var myModal = new bootstrap.Modal(document.getElementById('eventModal'));
                    myModal.show();
                }
            });

            calendar.render();
        });
    </script>
</body>
</html>