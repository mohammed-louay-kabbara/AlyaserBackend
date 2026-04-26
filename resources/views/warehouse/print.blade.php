<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>طباعة الطلب #{{ $order->id }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Tajawal', sans-serif;
            padding: 20px;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                padding: 0;
            }
        }
        .print-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 20px;
        }
        .order-info {
            margin-bottom: 20px;
        }
        .order-info p {
            margin: 5px 0;
        }
        .table {
            margin-bottom: 20px;
        }
        .total-row {
            font-weight: bold;
            font-size: 18px;
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="no-print mb-3">
            <button onclick="window.print()" class="btn btn-primary">طباعة</button>
            <a href="{{ route('warehouse.dashboard') }}" class="btn btn-secondary">إغلاق</a>
        </div>

        <div id="print-content">
            <div class="print-header">
                <h2>فاتورة طلب</h2>
                <p>رقم الطلب: #{{ $order->id }}</p>
                <p>التاريخ: {{ \Carbon\Carbon::parse($order->created_at)->format('Y-m-d') }}</p>
            </div>

            <div class="order-info">
                <h4>معلومات العميل</h4>
                <p><strong>الاسم:</strong> {{ $order->user->name ?? '-' }}</p>
                <p><strong>رقم الهاتف:</strong> {{ $order->user->phone ?? '-' }}</p>
                <p><strong>العنوان:</strong> {{ $order->user->address ?? '-' }}</p>
                @php
                    $statusLabel = match($order->status) {
                        'pending' => 'قيد الانتظار',
                        'processing' => 'قيد المعالجة',
                        'ready' => 'جاهز',
                        'delivered' => 'تم التوصيل',
                        'cancelled' => 'ملغي',
                        default => $order->status
                    };
                @endphp
                <p><strong>الحالة:</strong> {{ $statusLabel }}</p>
            </div>

            <h4>تفاصيل الطلب</h4>
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>المنتج</th>
                        <th>الكمية</th>
                        <th>السعر</th>
                        <th>المجموع</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        <tr>
                            <td>{{ $item->product->name ?? '-' }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ number_format($item->price, 0) }} ل.س</td>
                            <td>{{ number_format($item->quantity * $item->price, 0) }} ل.س</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="3" class="text-end">المجموع الكلي:</td>
                        <td>{{ number_format($order->total_amount, 0) }} ل.س</td>
                    </tr>
                </tfoot>
            </table>

            <div class="text-center mt-4">
                <p class="text-muted">شكراً لتعاملكم معنا</p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
