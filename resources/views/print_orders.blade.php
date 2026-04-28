<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>طباعة الطلبات</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #fff; color: #000; margin: 0; padding: 20px; }
        .invoice-box { padding: 20px; border: 1px solid #ccc; margin-bottom: 20px; page-break-after: always; }
        .invoice-box:last-child { page-break-after: auto; }
        .invoice-header { border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
        th { background-color: #f5f5f5; }
        .text-start { text-align: right; }
        h2, h4 { margin: 5px 0; }
        p { margin: 3px 0; }
    </style>
</head>
<body>

    @foreach($orders as $order)
    <div class="invoice-box">
        <div class="invoice-header">
            <div>
                <h2>فاتورة طلبية رقم #{{ $order->id }}</h2>
                <p><strong>التاريخ:</strong> {{ $order->created_at->format('Y-m-d H:i') }}</p>
                <p><strong>الملاحظات:</strong> {{ $order->notes ?? 'لا يوجد' }}</p>
            </div>
            <div class="text-start">
                <h4>الزبون: {{ $order->user->name ?? 'غير معروف' }}</h4>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>رقم المنتج</th>
                    <th>نوع الشراء</th>
                    <th>الكمية</th>
                    <th>سعر الوحدة</th>
                    <th>المجموع</th>
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
                    <td colspan="6">لا توجد عناصر في هذه الطلبية</td>
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

</body>
</html>