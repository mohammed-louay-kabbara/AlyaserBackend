@extends('layouts.app')

@section('content')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <main class="main-content position-relative border-radius-lg ">
        <div class="container-fluid py-4">
            <div class="row">
                <div class="col-12">
                    <div class="card mb-4">
                        <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                            <h6>إدارة الطلبات</h6>
                            <div class="d-flex mb-3 gap-2 align-items-center">
                                <button type="button" class="btn btn-info mb-0" onclick="prepareWarehouseModal()">
                                    <i class="fas fa-paper-plane me-1"></i> إرسال المحدد للمستودع
                                </button>
                                <button type="button" class="btn btn-secondary mb-0" onclick="printSelectedOrders()">
                                    <i class="fas fa-print me-1"></i> طباعة المحدد كفاتورة
                                </button>
                            </div>
                        </div>

                        <div class="card-body">
                            <form action="{{ url()->current() }}" method="GET" class="mb-4">
                                <div class="row align-items-center">
                                    <div class="col-md-3 col-sm-6 mb-3">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                                            <input type="text" class="form-control" name="search"
                                                placeholder="اسم الزبون..." value="{{ request('search') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-sm-6 mb-3">
                                        <input type="date" class="form-control" name="date"
                                            value="{{ request('date') }}">
                                    </div>
                                    <div class="col-md-3 col-sm-6 mb-3">
                                        <select name="status" class="form-control">
                                            <option value="">جميع الحالات</option>
                                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>
                                                قيد التجهيز</option>
                                            <option value="completed"
                                                {{ request('status') == 'completed' ? 'selected' : '' }}>منجز</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 col-sm-6 mb-3">
                                        <button class="btn btn-primary mb-0 w-100" type="submit">فرز وبحث</button>
                                    </div>
                                </div>
                            </form>

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
                                        @foreach ($orders as $order)
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

                                                <td class="align-middle">
                                                    <div class="d-flex justify-content-center gap-2">
                                                        <button type="button" class="btn btn-sm btn-primary mb-0"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#editModal{{ $order->id }}" title="تعديل">
                                                            تعجيل الطلب
                                                        </button>

                                                        <form action="{{ route('Order.destroy', $order->id) }}"
                                                            method="POST"
                                                            onsubmit="return confirm('هل أنت متأكد من حذف هذه الطلبية نهائياً؟');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-danger mb-0"
                                                                title="حذف">حذف</button>
                                                        </form>
                                                    </div>
                                                </td>

                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                @foreach ($orders as $order)
                                    <div class="modal fade" id="editModal{{ $order->id }}" tabindex="-1"
                                        aria-hidden="true">
                                        <div class="modal-dialog modal-lg text-end" dir="rtl">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">تعديل طلبية #{{ $order->id }}
                                                    </h5>
                                                    <button type="button" class="btn-close m-0" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <form action="{{ route('Order.update', $order->id) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-body">
                                                        <div class="row">
                                                            <div class="form-group mb-3">
                                                                <label>الملاحظات</label>
                                                                <textarea name="notes" class="form-control" rows="1">{{ $order->notes }}</textarea>
                                                            </div>
                                                        </div>
                                                        <hr>
                                                        <h6 class="mb-3 font-weight-bold">المنتجات المطلوبة
                                                        </h6>

                                                        <div class="table-responsive">
                                                            <table class="table table-bordered text-center"
                                                                id="itemsTable{{ $order->id }}">
                                                                <thead class="table-light">
                                                                    <tr>
                                                                        <th>اسم المنتج</th>
                                                                        <th>نوع الشراء</th>
                                                                        <th>الكمية</th>
                                                                        <th>سعر الوحدة</th>
                                                                        <th>إجراء</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody id="itemsBody{{ $order->id }}">
                                                                    @foreach ($order->items as $index => $item)
                                                                        <tr class="item-row">
                                                                            <input type="hidden"
                                                                                name="items[{{ $index }}][item_id]"
                                                                                value="{{ $item->id }}">

                                                                            <td>
                                                                                <input type="text"
                                                                                    name="items[{{ $index }}][product_id]"
                                                                                    class="form-control"
                                                                                    value="{{ $item->product->name }}"
                                                                                    required>
                                                                            </td>
                                                                            <td>
                                                                                <input type="text"
                                                                                    name="items[{{ $index }}][purchase_type]"
                                                                                    class="form-control"
                                                                                    value="{{ $item->purchase_type }}"
                                                                                    required>
                                                                            </td>
                                                                            <td>
                                                                                <input type="number"
                                                                                    name="items[{{ $index }}][quantity]"
                                                                                    class="form-control qty-input"
                                                                                    value="{{ $item->quantity }}"
                                                                                    min="1" required>
                                                                            </td>
                                                                            <td>
                                                                                <input type="number" step="0.01"
                                                                                    name="items[{{ $index }}][unit_price]"
                                                                                    class="form-control price-input"
                                                                                    value="{{ $item->unit_price }}"
                                                                                    required>
                                                                            </td>
                                                                            <td>
                                                                                <button type="button"
                                                                                    class="btn btn-sm btn-danger remove-row">حذف</button>
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>

                                                        <button type="button" class="btn btn-sm btn-success"
                                                            onclick="addNewItemRow({{ $order->id }})">
                                                            <i class="fas fa-plus"></i> إضافة منتج جديد
                                                        </button>

                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">إغلاق</button>
                                                        <button type="submit" class="btn"
                                                            style="background-color: #990a24; color:#fff">حفظ
                                                            التعديلات</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="d-flex justify-content-center mt-4">
                                {{ $orders->withQueryString()->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <div class="modal fade" id="warehouseModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog text-end" dir="rtl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">إرسال الطلبات إلى المستودع</h5>
                    <button type="button" class="btn-close m-0" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('Order.bulkSend') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="order_ids" id="selected_order_ids">

                        <div class="form-group">
                            <label>اختر المستودع الوجهة:</label>
                            <select name="warehouse_id" class="form-control" required>
                                <option value="">-- يرجى اختيار المستودع --</option>
                                @foreach ($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                        <button type="submit" class="btn btn-info">تأكيد الإرسال</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // تحديد الكل / إلغاء تحديد الكل
        const selectAllCheckbox = document.getElementById('selectAll');
        const orderCheckboxes = document.querySelectorAll('.order-checkbox');

        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                orderCheckboxes.forEach(checkbox => {
                    checkbox.checked = selectAllCheckbox.checked;
                });
            });
        }
    });

    // الحصول على الطلبات المحددة
    function getSelectedIds() {
        const selectedCheckboxes = document.querySelectorAll('.order-checkbox:checked');
        return Array.from(selectedCheckboxes).map(cb => cb.value);
    }

    // فتح مودال المستودع
    function prepareWarehouseModal() {
        const ids = getSelectedIds();
        if (ids.length === 0) {
            alert('الرجاء تحديد طلبية واحدة على الأقل لإرسالها.');
            return;
        }

        // وضع الأرقام في الحقل المخفي مفصولة بفاصلة
        document.getElementById('selected_order_ids').value = ids.join(',');

        // فتح المودال باستخدام Bootstrap 5
        var myModal = new bootstrap.Modal(document.getElementById('warehouseModal'));
        myModal.show();
    }

    // طباعة الطلبات
    function printSelectedOrders() {
        const ids = getSelectedIds();
        if (ids.length === 0) {
            alert('الرجاء تحديد طلبية واحدة على الأقل لطباعتها.');
            return;
        }

        // فتح صفحة الطباعة في نافذة جديدة مع تمرير الـ IDs كـ Query String
        const printUrl = '{{ route('Order.print') }}?ids=' + ids.join(',');
        window.open(printUrl, '_blank');
    }
</script>
<!--   Core JS Files   -->
<script src="../assets/js/core/popper.min.js"></script>
<script src="../assets/js/core/bootstrap.min.js"></script>
<script src="../assets/js/plugins/perfect-scrollbar.min.js"></script>
<script src="../assets/js/plugins/smooth-scrollbar.min.js"></script>
<script src="../assets/js/plugins/chartjs.min.js"></script>

<script>
    var win = navigator.platform.indexOf('Win') > -1;
    if (win && document.querySelector('#sidenav-scrollbar')) {
        var options = {
            damping: '0.5'
        }
        Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
    }
</script>
<!-- Github buttons -->
<script async defer src="https://buttons.github.io/buttons.js"></script>
<!-- Control Center for Soft Dashboard: parallax effects, scripts for the example pages etc -->
<script src="../assets/js/argon-dashboard.min.js?v=2.1.0"></script>
<script>
    // تحويل بيانات المنتجات من PHP إلى JavaScript
    const allProducts = @json($products);

    function addNewItemRow(orderId) {
        const tbody = document.getElementById('itemsBody' + orderId);
        const index = new Date().getTime();

        const newRow = `
            <tr class="item-row">
                <input type="hidden" name="items[${index}][item_id]" value="new">
                <td style="width: 40%">
                    <select name="items[${index}][product_id]" class="form-control product-select" required>
                        <option value="">اختر المنتج...</option>
                        ${allProducts.map(p => `<option value="${p.id}" data-price="${p.price}">${p.name}</option>`).join('')}
                    </select>
                </td>
                <td>
                    <input type="text" name="items[${index}][purchase_type]" class="form-control" value="قطعة" required>
                </td>
                <td>
                    <input type="number" name="items[${index}][quantity]" class="form-control qty-input" value="1" min="1" required>
                </td>
                <td>
                    <input type="number" step="0.01" name="items[${index}][unit_price]" class="form-control price-input" value="0" required>
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger remove-row">حذف</button>
                </td>
            </tr>
        `;

        tbody.insertAdjacentHTML('beforeend', newRow);

        // تفعيل Select2 للسطر الجديد للبحث داخل الـ 4000 منتج
        $('.product-select').select2({
            placeholder: "ابحث عن منتج...",
            allowClear: true,
            width: '100%'
        });
    }

    // مستمع لتغيير المنتج لتحديث السعر تلقائياً
    $(document).on('change', '.product-select', function() {
        const selectedOption = $(this).find(':selected');
        const price = selectedOption.data('price'); // جلب السعر من الـ data attribute
        const row = $(this).closest('tr');

        // وضع السعر في حقل "سعر الوحدة" مع إمكانية تعديله يدوياً لاحقاً
        if (price) {
            row.find('.price-input').val(price);
        }
    });

    // دالة الحذف (كما هي)
    document.addEventListener('click', function(e) {
        if (e.target && (e.target.classList.contains('remove-row') || e.target.closest('.remove-row'))) {
            const row = e.target.closest('tr');
            if (confirm('هل أنت متأكد من حذف هذا المنتج من الطلبية؟')) {
                row.remove();
            }
        }
    });
</script>
