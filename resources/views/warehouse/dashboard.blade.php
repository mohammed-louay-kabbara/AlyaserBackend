<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة تحكم المستودع</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Tajawal', sans-serif;
            background-color: #f8f9fa;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-processing { background-color: #d1ecf1; color: #0c5460; }
        .status-ready { background-color: #d4edda; color: #155724; }
        .status-delivered { background-color: #155724; color: white; }
        .status-cancelled { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container">
            <span class="navbar-brand mb-0 h1">لوحة تحكم المستودع</span>
            <span class="navbar-text text-white">
                مرحباً، {{ Auth::user()->name }}
            </span>
        </div>
    </nav>

    <div class="container">
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card mb-4">
            <div class="card-body">
                <form action="{{ route('warehouse.dashboard') }}" method="GET" class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="البحث برقم الطلب أو اسم العميل" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">جميع الحالات</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                            <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>قيد المعالجة</option>
                            <option value="ready" {{ request('status') == 'ready' ? 'selected' : '' }}>جاهز</option>
                            <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>تم التوصيل</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>ملغي</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">بحث</button>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('warehouse.dashboard') }}" class="btn btn-outline-secondary w-100">إعادة تعيين</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">جميع الطلبات ({{ $orders->count() }})</h5>
            </div>
            <div class="card-body">
                @if($orders->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <p>لا توجد طلبات</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>رقم الطلب</th>
                                    <th>العميل</th>
                                    <th>التاريخ</th>
                                    <th>المبلغ الإجمالي</th>
                                    <th>الحالة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orders as $order)
                                    <tr>
                                        <td><strong>#{{ $order->id }}</strong></td>
                                        <td>
                                            <div>{{ $order->user->name ?? '-' }}</div>
                                            <small class="text-muted">{{ $order->user->phone ?? '-' }}</small>
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($order->created_at)->format('Y-m-d') }}</td>
                                        <td>{{ number_format($order->total_amount, 0) }} ل.س</td>
                                        <td>
                                            @php
                                                $statusClass = 'status-' . $order->status;
                                                $statusLabel = match($order->status) {
                                                    'pending' => 'قيد الانتظار',
                                                    'processing' => 'قيد المعالجة',
                                                    'ready' => 'جاهز',
                                                    'delivered' => 'تم التوصيل',
                                                    'cancelled' => 'ملغي',
                                                    default => $order->status
                                                };
                                            @endphp
                                            <span class="status-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('warehouse.print', $order->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                    طباعة
                                                </a>
                                                @if($order->status != 'ready' && $order->status != 'delivered')
                                                    <button type="button" class="btn btn-sm btn-success" onclick="markAsReady({{ $order->id }})">
                                                        جاهز
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function markAsReady(orderId) {
            if (confirm('هل أنت متأكد من تحديث حالة الطلب إلى جاهز؟')) {
                fetch(`/warehouse/orders/${orderId}/ready`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({})
                })
                .then(response => response.json())
                .then(data => {
                    if (data.message) {
                        alert(data.message);
                        location.reload();
                    } else if (data.error) {
                        alert(data.error);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('حدث خطأ أثناء تحديث الحالة');
                });
            }
        }
    </script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</body>
</html>
