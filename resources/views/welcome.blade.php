<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - مركز الياسر</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    
    <style>
        :root {
            /* الألوان المستخرجة من الشعار */
            --brand-primary: #8a031a; /* اللون العنابي للنص والشعار */
            --brand-bg: #fcf9f2;      /* اللون السكري للخلفية */
            --brand-hover: #6b0214;   /* لون داكن قليلاً لتأثير التحويم (Hover) */
        }
        
        body {
            background-color: var(--brand-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }

        /* تصميم حاوية تسجيل الدخول بنمط مسطح ونظيف */
        .login-card {
            background: #ffffff;
            border: none;
            border-radius: 6px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
            padding: 2.5rem;
            width: 100%;
            max-width: 420px;
        }

        /* حاوية الشعار */
        .logo-container {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo-container img {
            max-width: 70%; /* يمكنك تعديل الحجم بما يتناسب مع دقة الشعار */
            height: auto;
        }

        /* تخصيص حقول الإدخال */
        .form-control {
            border-radius: 4px;
            border: 1px solid #ced4da;
            padding: 0.75rem 1rem;
        }

        .form-control:focus {
            border-color: var(--brand-primary);
            box-shadow: 0 0 0 0.25rem rgba(138, 3, 26, 0.15);
        }

        /* تخصيص زر الدخول */
        .btn-brand {
            background-color: var(--brand-primary);
            border-color: var(--brand-primary);
            color: #ffffff;
            border-radius: 4px;
            padding: 0.75rem;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }

        .btn-brand:hover, .btn-brand:focus {
            background-color: var(--brand-hover);
            border-color: var(--brand-hover);
            color: #ffffff;
        }

        /* تخصيص الروابط */
        .text-brand {
            color: var(--brand-primary);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .text-brand:hover {
            color: var(--brand-hover);
            text-decoration: underline;
        }

        .form-label {
            font-weight: 500;
            color: #333;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="logo-container">
            <img src="{{ asset('logo.png') }}" alt="شعار الياسر AL YASER">
        </div>

        <form action="#" method="POST">
            <div class="mb-3">
                <label for="usernameInput" class="form-label">اسم المستخدم أو البريد الإلكتروني</label>
                <input type="text" class="form-control" id="usernameInput" placeholder="أدخل اسم المستخدم" required>
            </div>
            
            <div class="mb-3">
                <label for="passwordInput" class="form-label">كلمة المرور</label>
                <input type="password" class="form-control" id="passwordInput" placeholder="أدخل كلمة المرور" required>
            </div>
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="" id="rememberMe">
                    <label class="form-check-label" for="rememberMe" style="font-size: 0.9rem;">
                        تذكر بياناتي
                    </label>
                </div>
                <a href="#" class="text-brand">نسيت كلمة المرور؟</a>
            </div>
            
            <button type="submit" class="btn btn-brand w-100">تسجيل الدخول</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>