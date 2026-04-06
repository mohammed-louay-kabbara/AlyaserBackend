<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - مركز الياسر</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    
    <style>
        :root {
            --brand-maroon: #8a031a; /* اللون العنابي من الشعار */
            --brand-cream: #fcf9f2;  /* اللون السكري من الشعار */
        }
        
        body {
            background-color: var(--brand-cream);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }

        .main-container {
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            max-width: 900px;
            width: 95%;
        }

        /* قسم الشعار */
        .logo-section {
            background-color: var(--brand-cream); /* أو يمكنك جعلها أبيض بالكامل */
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            border-left: 1px solid #eee; /* فاصل بسيط */
        }

        .logo-section img {
            max-width: 100%;
            height: auto;
            transition: transform 0.3s ease;
        }
        
        .logo-section img:hover {
            transform: scale(1.05);
        }

        /* قسم الفورم */
        .login-section {
            padding: 50px;
        }

        .form-label {
            font-weight: 500;
            color: #444;
        }

        .form-control {
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #ddd;
        }

        .form-control:focus {
            border-color: var(--brand-maroon);
            box-shadow: 0 0 0 0.25rem rgba(138, 3, 26, 0.1);
        }

        .btn-brand {
            background-color: var(--brand-maroon);
            color: white;
            padding: 12px;
            border-radius: 8px;
            font-weight: 600;
            border: none;
            transition: 0.3s;
        }

        .btn-brand:hover {
            background-color: #6b0214;
            color: white;
        }

        .text-brand {
            color: var(--brand-maroon);
            text-decoration: none;
        }

        /* تحسينات للشاشات الصغيرة */
        @media (max-width: 768px) {
            .logo-section {
                padding: 20px;
                border-left: none;
                border-bottom: 1px solid #eee;
            }
            .login-section {
                padding: 30px;
            }
        }
    </style>
</head>
<body>

<div class="main-container">
    <div class="row g-0">
        <div class="col-md-6 logo-section">
            <img src="{{ asset('logo.png') }}" alt="AL YASER Logo">
        </div>

        <div class="col-md-6 login-section">
            <h3 class="mb-4 fw-bold" style="color: var(--brand-maroon);">مرحباً بك</h3>
            <p class="text-muted mb-4">الرجاء إدخال بياناتك للوصول إلى الحساب</p>
            
            <form>
                <div class="mb-3">
                    <label class="form-label">اسم المستخدم</label>
                    <input type="text" class="form-control" placeholder="أدخل اسم المستخدم">
                </div>
                
                <div class="mb-4">
                    <label class="form-label">كلمة المرور</label>
                    <input type="password" class="form-control" placeholder="••••••••">
                </div>
                <button type="submit" class="btn btn-brand w-100">دخول</button>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>