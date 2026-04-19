<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">

    <style>
        body {
            font-family: cairo;
            direction: rtl;
            text-align: right;
        }

        h1 {
            text-align: center;
            margin-bottom: 10px;
        }

        p {
            text-align: right;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th {
            background: #eee;
        }

        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
        }
    </style>
</head>

<body>

    <h1>قائمة المنتجات - نظام الياسر</h1>

    <p>تاريخ الاستخراج: {{ date('Y-m-d H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>اسم المنتج</th>
                <th>سعر القطعة</th>
                <th>سعر الجملة</th>
                <th>الصنف</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($products as $index => $product)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $product->name }}</td>
                    <td>{{ number_format($product->retail_price, 2) }}</td>
                    <td>{{ number_format($product->wholesale_price, 2) }}</td>
                    <td>{{ $product->category->name ?? 'غير مصنف' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p style="margin-top:20px;">
        إجمالي عدد المنتجات: {{ $products->count() }}
    </p>

</body>
</html>