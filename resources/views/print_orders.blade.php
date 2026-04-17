<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>طباعة الطلبات</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Tajawal', sans-serif; background-color: #fff; color: #000; }
        .invoice-box { padding: 30px; border: 1px solid #eee; margin-bottom: 30px; page-break-after: always; }
        .invoice-box:last-child { page-break-after: auto; }
        .invoice-header { border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
        @media print {
            .no-print { display: none; }
            body { margin: 0; padding: 0; }
        }
    </style>
</head>
<body>

    <div class="container mt-4 no-print text-center">
        <button onclick="window.print()" class="btn btn-primary btn-lg"><i class="fas fa-print"></i> طباعة الآن</button>
        <button onclick="window.close()" class="btn btn-secondary btn-lg">إغلاق</button>
        <hr>
    </div>

    @foreach($orders as $order)
    <div class="invoice-box container">
        <div class="invoice-header d-flex justify-content-between align-items-center">
            <div>
                <h2>فاتورة طلبية رقم #{{ $order->id }}</h2>
                <p class="mb-0"><strong>التاريخ:</strong> {{ $order->created_at->format('Y-m-d H:i') }}</p>
                <p class="mb-0"><strong>الملاحظات:</strong> {{ $order->notes ?? 'لا يوجد' }}</p>
            </div>
            <div class="text-start">
                <h4 class="mb-0">الزبون: {{ $order->user->name ?? 'غير معروف' }}</h4>
            </div>
        </div>

        <table class="table table-bordered text-center">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>رقم المنتج (Product ID)</th>
                    <th>نوع الشراء</th>
                    <th>الكمية</th>
                    <th>سعر الوحدة</th>
                    <th>المجموع (Sub Total)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($order->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->product_id }}</td> 
                    <td>{{ $item->purchase_type }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format($item->unit_price, 2) }}</td>
                    <td>{{ number_format($item->sub_total, 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center">لا توجد عناصر في هذه الطلبية</td>
                </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="5" class="text-start">الإجمالي الكلي:</th>
                    <th>{{ number_format($order->total_amount, 2) }}</th>
                </tr>
            </tfoot>
        </table>
    </div>
    @endforeach

    <script>
        // تفعيل الطباعة التلقائية عند فتح الصفحة
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>