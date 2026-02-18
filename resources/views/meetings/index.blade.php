<!DOCTYPE html>
<html>
<head>
    <title>Jadwal Meeting BCC</title>
    <link rel="icon" href="{{ asset('images/KotaBanjarmasin.png') }}" type="image/png">
    
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .main-container {
            max-width: 1100px;
            margin: 0 auto;
            background: white;
            padding: 10px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            min-height: 600px;
        }

        @media (min-width: 768px) {
            .main-container { padding: 20px; }
            .desktop-title { white-space: nowrap; }
        }

        body { background-color: #f4f6f9; }
        .fc-event { cursor: pointer; }
        
        /* Style Tabel & Kalender */
        .fc-daygrid-event {
            display: flex !important;
            align-items: center !important;
            border-radius: 6px !important;  
            border: none !important;        
            margin: 2px 4px !important;     
            padding: 4px 6px !important;    
            font-size: 0.85em;              
            box-shadow: 0 2px 3px rgba(0,0,0,0.1); 
            transition: all 0.2s ease-in-out; 
            overflow: hidden;
        }
        .fc-event-time {
            flex-shrink: 0 !important;
            margin-right: 6px !important;
            font-weight: bold !important;
            color: rgba(255, 255, 255, 0.9) !important;
            font-size: 0.95em !important;
        }
        .fc-event-title {
            flex-grow: 1 !important;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            color: #ffffff !important;
            font-weight: 600 !important;
        }
        .fc-daygrid-event:hover {
            transform: scale(1.03);         
            box-shadow: 0 5px 10px rgba(0,0,0,0.2); 
            z-index: 50;                    
            cursor: pointer;
        }
        .fc-daygrid-event-dot { display: none !important; }

        .clickable-row {
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .clickable-row:hover {
            background-color: #f1f3f5 !important;
        }
        
        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
        }
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
                @endguest
            </div>
        </div>

        <div class="main-container">
            
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-3 gap-3">
                <div class="btn-group shadow-sm w-100 w-md-auto" role="group">
                    <button type="button" class="btn btn-outline-primary active fw-bold" id="btnModeKalender" onclick="switchMode('calendar')">
                        Tampilan Kalender
                    </button>
                    <button type="button" class="btn btn-outline-primary fw-bold" id="btnModeList" onclick="switchMode('list')">
                        Tampilan List Tabel
                    </button>
                </div>

                @if(auth()->check() && auth()->user()->role == 'admin')
                    <div class="d-flex gap-2 flex-wrap justify-content-center w-100 w-md-auto">
                        <a href="{{ route('create.place') }}" class="btn btn-info text-white shadow fw-bold">+ Tempat</a>
                        <a href="{{ route('create.user') }}" class="btn btn-success shadow fw-bold">+ User</a>
                        <a href="{{ url('/buat-meeting') }}" class="btn btn-primary shadow fw-bold">+ Jadwal</a>
                    </div>
                @endif
            </div>

            <div id='calendarWrapper'>
                <div id='calendar'></div>
            </div>

            <div id='listWrapper' class="d-none">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light text-center">
                            <tr>
                                <th style="width: 15%;">Waktu</th>
                                <th style="width: 25%;">Instansi / Dinas</th>
                                <th style="width: 35%;">Topik, Lokasi & Permintaan</th>
                                <th style="width: 15%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($meetings))
                                @forelse($meetings as $m)
                                    @php
                                        $eventData = [
                                            'title'       => $m->agency,
                                            'deskripsi'   => $m->description,
                                            'permintaan'  => $m->additional_requests,
                                            'start'       => $m->start_time,
                                            'durasi'      => $m->duration,
                                            'place'       => $m->place,
                                            'join_url'    => $m->join_url,
                                            'meeting_id'  => $m->zoom_meeting_id,
                                            'password'    => $m->password,
                                            'share_url'   => route('meeting.share', $m->uuid),
                                            'edit_url'    => route('edit.meeting', $m->id),
                                            'delete_id'   => $m->id
                                        ];
                                    @endphp

                                    <tr class="clickable-row" onclick="openModalFromTable(this)" data-event="{{ json_encode($eventData) }}">
                                        
                                        <td class="text-center">
                                            <div class="fw-bold">{{ \Carbon\Carbon::parse($m->start_time)->translatedFormat('d M Y') }}</div>
                                            <div class="text-primary fw-bold">{{ date('H:i', strtotime($m->start_time)) }} WITA</div>
                                            <small class="text-muted">{{ $m->duration }} Menit</small>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $m->agency }}</div>
                                        </td>
                                        <td>
                                            <div class="text-muted fst-italic small mb-2">{{ Str::limit($m->description, 60) }}</div>
                                            
                                            <div class="d-flex flex-wrap gap-2">
                                                @if($m->join_url)
                                                    <span class="badge bg-primary"><i class="bi bi-camera-video"></i> Online</span>
                                                @else
                                                    <span class="badge bg-success"><i class="bi bi-geo-alt"></i> {{ $m->place }}</span>
                                                @endif

                                                @if($m->additional_requests)
                                                    <span class="badge bg-warning text-dark border border-warning-subtle" title="{{ $m->additional_requests }}">
                                                        <i class="bi bi-exclamation-triangle-fill"></i> Req: {{ Str::limit($m->additional_requests, 25) }}
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <button onclick="event.stopPropagation(); copyShareLink('{{ $eventData['share_url'] }}')" class="btn btn-sm btn-outline-primary fw-bold w-100 mb-1" title="Salin Link Publik">
                                                <i class="bi bi-share-fill"></i> Share
                                            </button>

                                            @if(auth()->check() && auth()->user()->role == 'admin')
                                                <div class="btn-group w-100" role="group">
                                                    <a href="{{ $eventData['edit_url'] }}" onclick="event.stopPropagation()" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                                                    
                                                    <form action="{{ route('hapus.meeting', $m->id) }}" method="POST" class="d-inline">
                                                        @csrf @method('DELETE')
                                                        <button type="button" onclick="konfirmasiHapusTabel(event, this)" class="btn btn-sm btn-danger w-100 rounded-0 rounded-end">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center py-4 text-muted">Belum ada jadwal meeting.</td></tr>
                                @endforelse
                            @else
                                <tr><td colspan="4" class="text-center text-danger">Error: Variabel $meetings belum dikirim.</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <footer class="text-center mt-5 mb-4 text-muted">
            <small>
                &copy; {{ date('Y') }} 
                <a href="https://diskominfotik.banjarmasinkota.go.id/" target="_blank" class="text-decoration-none text-secondary fw-bold">Pemerintah Kota Banjarmasin</a>
                <br>
                <a href="https://t.me/KasaJen" target="_blank" class="text-decoration-none text-muted">Developed with <span class="text-danger">❤️</span></a>
            </small>
        </footer>

    </div>

    <div class="modal fade" id="eventModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalTitle">Detail Jadwal</h5>
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
                    
                    <div id="modalReq" class="mt-2 mb-3"></div>

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

        function konfirmasiHapusTabel(e, btnElement) {
            e.stopPropagation(); 
            Swal.fire({
                title: 'Yakin hapus jadwal ini?',
                text: "Data tidak bisa dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus'
            }).then((result) => {
                if (result.isConfirmed) {
                    btnElement.closest('form').submit();
                }
            });
        }

        // FITUR SALIN LINK PUBLIK

        function copyShareLink(url) {
            navigator.clipboard.writeText(url).then(function() {
                Swal.fire({ icon: 'success', title: 'Link Disalin!', text: 'Link publik berhasil disalin.', timer: 1500, showConfirmButton: false });
            }, function(err) { Swal.fire('Gagal', 'Tidak bisa menyalin link manual.', 'error'); });
        }


        function switchMode(mode) {
            var calWrapper = document.getElementById('calendarWrapper');
            var listWrapper = document.getElementById('listWrapper');
            var btnCal = document.getElementById('btnModeKalender');
            var btnList = document.getElementById('btnModeList');

            if (mode === 'calendar') {
                calWrapper.classList.remove('d-none');
                listWrapper.classList.add('d-none');
                btnCal.classList.add('active');
                btnList.classList.remove('active');
                window.dispatchEvent(new Event('resize'));
            } else {
                calWrapper.classList.add('d-none');
                listWrapper.classList.remove('d-none');
                btnCal.classList.remove('active');
                btnList.classList.add('active');
            }
        }

        function showModalDetail(data) {
            document.getElementById('modalTitle').innerText = 'Detail Jadwal';
            document.getElementById('modalTopic').innerText = data.title; 
            
            // Deskripsi Bersih
            document.getElementById('modalDesc').innerText = data.deskripsi ? data.deskripsi : '(Tidak ada deskripsi)';
            
            var dateObj = new Date(data.start);
            var options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
            var waktu = dateObj.toLocaleString('id-ID', options).replace('.', ':'); 
            
            document.getElementById('modalTime').innerText = waktu + ' WITA';
            document.getElementById('modalDuration').innerText = data.durasi;

            // Menampilkan Permintaan Tambahan di TEMPAT BARU
            var reqDiv = document.getElementById('modalReq');
            reqDiv.innerHTML = '';
            if (data.permintaan) {
                reqDiv.innerHTML = `
                    <div class="alert alert-warning border-0 shadow-sm">
                        <small class="fw-bold text-dark d-block mb-1"><i class="bi bi-exclamation-circle-fill"></i> PERMINTAAN TAMBAHAN:</small>
                        <span class="text-dark">${data.permintaan}</span>
                    </div>
                `;
            }
            
            var actionArea = document.getElementById('actionArea');
            var shareBtn = data.share_url ? `<button onclick="copyShareLink('${data.share_url}')" class="btn btn-outline-primary fw-bold w-100 mb-2">🔗 Salin Link dan Bagikan</button>` : '';
            
            var content = '';
            if (data.join_url) {
                var manualInfo = '';
                if (data.meeting_id || data.password) {
                    manualInfo = `<div class="bg-light p-2 rounded border mt-2"><small class="d-block text-muted fw-bold mb-1">INFO MANUAL:</small><div class="d-flex justify-content-between"><span>ID: <strong>${data.meeting_id || '-'}</strong></span><span>Pass: <strong>${data.password || '-'}</strong></span></div></div>`;
                }
                var tempatInfo = data.place ? data.place : 'Online';
                content = `${shareBtn}<div class="alert alert-info mb-2"><strong>Lokasi:</strong> ${tempatInfo}</div><a href="${data.join_url}" target="_blank" class="btn btn-success fw-bold w-100">Gabung Meeting</a>${manualInfo}`;
            } else {
                var lokasi = data.place ? data.place : 'Tempat Belum Diisi';
                content = `${shareBtn}<div class="alert alert-secondary text-center"><strong>Lokasi Meeting:</strong><br><span class="fs-5">${lokasi}</span></div>`;
            }

            actionArea.innerHTML = content;
            
            var adminDiv = document.getElementById('adminActions');
            if (IS_ADMIN) {
                adminDiv.classList.remove('d-none');
                document.getElementById('btnEdit').href = data.edit_url;
                document.getElementById('formDelete').action = "{{ url('/meeting') }}" + "/" + data.delete_id;
            } else {
                adminDiv.classList.add('d-none');
            }
            var myModal = new bootstrap.Modal(document.getElementById('eventModal'));
            myModal.show();
        }

        function openModalFromTable(rowElement) {
            var rawData = JSON.parse(rowElement.dataset.event);
            showModalDetail(rawData);
        }

        const IS_ADMIN = @json(auth()->check() && auth()->user()->role == 'admin');

        document.addEventListener('DOMContentLoaded', function() {
            history.pushState(null, null, location.href);
            window.onpopstate = function () { history.go(1); };
            
            var calendarEl = document.getElementById('calendar');
            var initialMobile = window.innerWidth < 768;

            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: initialMobile ? 'listMonth' : 'dayGridMonth', 
                themeSystem: 'bootstrap5',
                locale: 'id',
                eventDisplay: 'block', 
                displayEventTime: false, 
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
                    var props = info.event.extendedProps;
                    var data = {
                        title: info.event.title,
                        start: info.event.start,
                        deskripsi: props.deskripsi,
                        permintaan: props.permintaan, 
                        durasi: props.durasi,
                        place: props.place,
                        join_url: props.join_url,
                        meeting_id: props.meeting_id,
                        password: props.password,
                        share_url: props.share_url,
                        edit_url: props.edit_url,
                        delete_id: props.delete_id
                    };
                    showModalDetail(data);
                }
            });
            calendar.render();
        });
    </script>
</body>
</html>