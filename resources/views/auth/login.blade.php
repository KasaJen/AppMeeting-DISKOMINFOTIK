<!DOCTYPE html>
<html>
<head>
    <title>Login Aplikasi</title>
    <link rel="icon" href="{{ asset('images/KotaBanjarmasin.png') }}" type="image/png">
    
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body {
            background-color: #f4f6f9;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            flex-direction: column; 
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .login-header {
            background: #0d6efd;
            color: white;
            border-radius: 15px 15px 0 0;
            padding: 20px;
            text-align: center;
        }
        .cursor-pointer { cursor: pointer; }
    </style>
</head>
<body>

    <div class="d-flex align-items-center gap-3 mb-4">
        <img src="{{ asset('images/KotaBanjarmasin.png') }}" alt="Logo Kota" style="height: 60px; width: auto;">
        <h2 class="fw-bold text-dark m-0">APLIKASI PENJADWALAN MEETING</h2>
    </div>
    <div class="card login-card">
        <div class="login-header">
            <h4 class="mb-0 fw-bold">Silakan Login</h4>
            <small>Masuk untuk melihat dan mengelola jadwal</small>
        </div>
        <div class="card-body p-4">
            
            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-bold">Alamat Email</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                           placeholder="Masukkan Email..." value="{{ old('email') }}" required autofocus>
                    @error('email')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Password</label>
                    <div class="input-group has-validation">
                        <input type="password" name="password" id="passwordInput" 
                               class="form-control @error('password') is-invalid @enderror" 
                               placeholder="Masukan Password..." required>
                        
                        <span class="input-group-text cursor-pointer" onclick="togglePassword()">
                            <i id="eyeIcon" class="bi bi-eye-fill text-secondary"></i>
                        </span>
                        
                        @error('password')
                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>
                </div>

                <div class="d-grid gap-2 mt-4">
                    <button type="submit" class="btn btn-primary fw-bold py-2">LOGIN</button>
                </div>
                
                <div class="text-center mt-3">
                   <small class="text-muted">Pastikan email & password benar.</small>
                </div>

            </form>
        </div>
    </div>

    <script>
        
        function togglePassword() {
            var passwordInput = document.getElementById("passwordInput");
            var eyeIcon = document.getElementById("eyeIcon");

            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                eyeIcon.classList.remove("bi-eye-fill");
                eyeIcon.classList.add("bi-eye-slash-fill");
            } else {
                passwordInput.type = "password";
                eyeIcon.classList.remove("bi-eye-slash-fill");
                eyeIcon.classList.add("bi-eye-fill");
            }
        }

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