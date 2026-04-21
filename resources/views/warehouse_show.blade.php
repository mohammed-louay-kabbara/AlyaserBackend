  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@extends('layouts.app')

@section('content')
  
    <main class="main-content position-relative border-radius-lg ">
        <div class="container-fluid py-4">
            <div class="row">
                <div class="col-12">
                    <div class="card mb-4">
                        <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                            <h6>إدارة الطلبات</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table align-items-center mb-0 text-center">
                                    <thead>
                                        <tr>
                                            <th>
                                                <input type="checkbox" id="selectAll" class="form-check-input"
                                                    style="border: 1px solid #ccc;">
                                            </th>
                                            <th class="text-secondary  font-weight-bolder opacity-7">رقم الطلب</th>
                                            <th class="text-secondary  font-weight-bolder opacity-7">الزبون</th>
                                            <th class="text-secondary  font-weight-bolder opacity-7">التاريخ</th>
                                            <th class="text-secondary  font-weight-bolder opacity-7">الإجمالي</th>
                                            <th class="text-secondary  font-weight-bolder opacity-7">الحالة</th>
                                            <th class="text-secondary  font-weight-bolder opacity-7">ملاحظات</th>
                                            <th class="text-secondary  font-weight-bolder opacity-7">الإجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($Orders as $order)
                                            <tr>
                                                <td>
                                                    <input type="checkbox" class="form-check-input order-checkbox"
                                                        value="{{ $order->id }}" style="border: 1px solid #ccc;">
                                                </td>
                                                <td>
                                                    <p class="text-xs font-weight-bold mb-0">#{{ $order->id }}</p>
                                                </td>
                                                <td>
                                                    <h6 class="mb-0 text-sm">{{ $order->user->name ?? 'غير معروف' }}</h6>
                                                </td>
                                                <td>
                                                    <p class="text-xs font-weight-bold mb-0">
                                                        {{ $order->created_at->format('Y-m-d') }}</p>
                                                </td>
                                                <td>
                                                    <p class="text-xs font-weight-bold mb-0">{{ $order->total_amount }}</p>
                                                </td>
                                                <td>
                                                    @if ($order->status == 'completed')
                                                        <span class="badge badge-sm bg-gradient-success">منجز</span>
                                                    @elseif ($order->status == 'confirmed')
                                                        <span class="badge badge-sm bg-gradient-success">يجهزها
                                                            المستودع</span>
                                                    @elseif ($order->status == 'processing')
                                                        <span class="badge badge-sm bg-gradient-success">تم تجهيز
                                                            الطلبية</span>
                                                    @else
                                                        <span class="badge badge-sm bg-gradient-warning">تنتظر قبولك</span>
                                                    @endif

                                                    @if ($order->is_synced)
                                                        <span class="badge badge-sm bg-gradient-info mt-1 d-block"><i
                                                                class="fas fa-check-double"></i> تم الإرسال
                                                            للمستودع</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <p class="text-xs text-secondary mb-0"
                                                        style="max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"
                                                        title="{{ $order->notes }}">{{ $order->notes ?? 'لا يوجد' }}</p>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!--   Core JS Files   -->
<script src="../assets/js/core/popper.min.js"></script>
<script src="../assets/js/core/bootstrap.min.js"></script>
<script src="../assets/js/plugins/perfect-scrollbar.min.js"></script>
<script src="../assets/js/plugins/smooth-scrollbar.min.js"></script>
<script src="../assets/js/plugins/chartjs.min.js"></script>
<!-- Github buttons -->
<script async defer src="https://buttons.github.io/buttons.js"></script>
<!-- Control Center for Soft Dashboard: parallax effects, scripts for the example pages etc -->

