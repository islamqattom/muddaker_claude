<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - نظام تسميع القرآن الكريم</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Tajawal', sans-serif;
            background: linear-gradient(135deg, #182B48 0%, #0D1829 40%, #182B48 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            background: rgba(180,128,44,0.1);
            top: -100px;
            right: -100px;
        }

        body::after {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: rgba(255,255,255,0.05);
            bottom: -80px;
            left: -80px;
        }

        .login-card {
            background: #fff;
            border-radius: 20px;
            padding: 50px 45px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 30px 80px rgba(0,0,0,0.3);
            position: relative;
            z-index: 1;
        }

        .login-logo {
            text-align: center;
            margin-bottom: 35px;
        }

        .logo-circle {
            width: 90px;
            height: 90px;
            background: linear-gradient(135deg, #182B48, #2A4570);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            box-shadow: 0 10px 25px rgba(24,43,72,0.3);
        }

        .logo-circle i { color: #fff; font-size: 40px; }

        .login-logo h4 {
            color: #182B48;
            font-weight: 800;
            font-size: 20px;
            margin-bottom: 5px;
        }

        .login-logo p {
            color: #7f8c8d;
            font-size: 13px;
            margin: 0;
        }

        .form-label {
            font-weight: 600;
            color: #2c3e50;
            font-size: 14px;
        }

        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            font-size: 14px;
            font-family: 'Tajawal', sans-serif;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: #182B48;
            box-shadow: 0 0 0 0.2rem rgba(24,43,72,0.15);
        }

        .input-group-text {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-left: none;
            border-radius: 0 10px 10px 0;
            color: #182B48;
        }

        .btn-login {
            background: linear-gradient(135deg, #182B48, #2A4570);
            border: none;
            border-radius: 10px;
            padding: 13px;
            font-size: 16px;
            font-weight: 700;
            font-family: 'Tajawal', sans-serif;
            letter-spacing: 0.5px;
            transition: all 0.3s;
            width: 100%;
            color: #fff;
        }

        .btn-login:hover {
            background: linear-gradient(135deg, #0D1829, #182B48);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(24,43,72,0.35);
            color: #fff;
        }

        .role-badges {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-top: 25px;
            flex-wrap: wrap;
        }

        .role-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .alert { border-radius: 10px; font-size: 14px; }

        .quran-verse {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #182B48;
            font-size: 16px;
            line-height: 2;
        }

        /* Mobile Optimizations */
        @media (max-width: 480px) {
            body { padding: 15px; }
            .login-card {
                padding: 30px 20px;
                border-radius: 15px;
            }
            .logo-circle {
                width: 70px;
                height: 70px;
            }
            .logo-circle i { font-size: 30px; }
            .login-logo img {
                width: 140px !important;
                height: 90px !important;
            }
            .login-logo h4 { font-size: 18px; }
            .login-logo p { font-size: 12px; }
            .form-control {
                padding: 14px 15px;
                font-size: 16px; /* Prevent zoom on iOS */
            }
            .btn-login {
                padding: 15px;
                font-size: 15px;
            }
            .quran-verse {
                font-size: 14px;
                margin-top: 20px;
                padding-top: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-logo">
            <img src="{{ asset('images/muddaker-logo-main-nobg.png') }}" alt="Logo" style="width:180px;height:120px;">
            <p>نظام متابعة وتسميع القرآن الكريم</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i>
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label class="form-label">
                    <i class="fas fa-user me-1"></i>
                    اسم المستخدم أو البريد الإلكتروني أو رقم الهاتف
                </label>
                <input type="text"
                       name="login"
                       class="form-control @error('login') is-invalid @enderror"
                       placeholder="أدخل اسم المستخدم أو بريدك أو رقم هاتفك"
                       value="{{ old('login') }}"
                       required
                       autofocus>
            </div>

            <div class="mb-4">
                <label class="form-label">
                    <i class="fas fa-lock me-1"></i>
                    كلمة المرور
                </label>
                <div class="input-group">
                    <input type="password"
                           name="password"
                           id="passwordInput"
                           class="form-control @error('password') is-invalid @enderror"
                           placeholder="أدخل كلمة المرور"
                           required>
                    <span class="input-group-text" style="cursor:pointer; border-right:none; border-left: 2px solid #e9ecef; border-radius:0 10px 10px 0;"
                          onclick="togglePassword()">
                        <i class="fas fa-eye" id="eyeIcon"></i>
                    </span>
                </div>
            </div>

            <div class="mb-4 d-flex justify-content-between align-items-center">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label" for="remember" style="font-size:13px;">تذكرني</label>
                </div>
            </div>

            <button type="submit" class="btn btn-login">
                <i class="fas fa-sign-in-alt me-2"></i>
                تسجيل الدخول
            </button>
        </form>

        <div class="quran-verse">
            ﴿ وَلَقَدْ يَسَّرْنَا الْقُرْآنَ لِلذِّكْرِ فَهَلْ مِنْ مُدَّكِرٍ ﴾
        </div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('passwordInput');
            const icon = document.getElementById('eyeIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'fas fa-eye';
            }
        }
    </script>
</body>
</html>
