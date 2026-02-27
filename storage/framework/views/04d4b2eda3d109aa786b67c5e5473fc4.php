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
            background: linear-gradient(135deg, #1a5276 0%, #0d3349 40%, #1a5276 100%);
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
            background: rgba(201,162,39,0.1);
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
            background: linear-gradient(135deg, #1a5276, #2980b9);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            box-shadow: 0 10px 25px rgba(26,82,118,0.3);
        }

        .logo-circle i { color: #fff; font-size: 40px; }

        .login-logo h4 {
            color: #1a5276;
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
            border-color: #1a5276;
            box-shadow: 0 0 0 0.2rem rgba(26,82,118,0.15);
        }

        .input-group-text {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-left: none;
            border-radius: 0 10px 10px 0;
            color: #1a5276;
        }

        .btn-login {
            background: linear-gradient(135deg, #1a5276, #2980b9);
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
            background: linear-gradient(135deg, #0d3349, #1a5276);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(26,82,118,0.35);
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
            color: #1a5276;
            font-size: 16px;
            line-height: 2;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-logo">
            <div class="logo-circle">
                <i class="fas fa-quran"></i>
            </div>
            <h4>جمعية المحافظة على القرآن الكريم</h4>
            <p>نظام متابعة وتسميع القرآن الكريم</p>
        </div>

        <?php if(session('success')): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i><?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>

        <?php if($errors->any()): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo e($errors->first()); ?>

            </div>
        <?php endif; ?>

        <form action="<?php echo e(route('login.post')); ?>" method="POST">
            <?php echo csrf_field(); ?>

            <div class="mb-3">
                <label class="form-label">
                    <i class="fas fa-user me-1"></i>
                    البريد الإلكتروني أو رقم الهاتف
                </label>
                <input type="text"
                       name="login"
                       class="form-control <?php $__errorArgs = ['login'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                       placeholder="أدخل بريدك أو رقم هاتفك"
                       value="<?php echo e(old('login')); ?>"
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
                           class="form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
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

        <div class="role-badges">
            <span class="role-badge bg-primary text-white"><i class="fas fa-user-shield me-1"></i>إدارة</span>
            <span class="role-badge bg-success text-white"><i class="fas fa-user-tie me-1"></i>مشرف</span>
            <span class="role-badge bg-info text-white"><i class="fas fa-user-graduate me-1"></i>طالب</span>
            <span class="role-badge bg-warning text-dark"><i class="fas fa-users me-1"></i>ولي أمر</span>
        </div>

        <div class="quran-verse">
            ﴿ وَرَتِّلِ الْقُرْآنَ تَرْتِيلًا ﴾
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
<?php /**PATH C:\xampp\htdocs\quran-system\resources\views/auth/login.blade.php ENDPATH**/ ?>