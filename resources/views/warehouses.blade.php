@extends('layouts.app')

@section('content')
    <main class="main-content position-relative border-radius-lg">
        <div class="container-fluid py-4">
            <div class="row">
                <div class="col-12">
                    <div class="card mb-4">
                        <div class="card-header pb-0">
                            <h6>إدارة المستودعات</h6>
                        </div>

                        <div class="card-body">

                            {{-- رسالة نجاح --}}
                            @if (session('success'))
                                <div class="alert alert-success">
                                    {{ session('success') }}
                                </div>
                            @endif

                            {{-- رسالة أخطاء --}}
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="container-fluid py-4">
                                <div class="row mb-4 align-items-center">
                                    <div class="col-md-6 col-lg-4 mb-3 mb-md-0">
                                        <form action="{{ url()->current() }}" method="GET">
                                            <div class="input-group">
                                                <span class="input-group-text text-body">
                                                    <i class="fas fa-search" aria-hidden="true"></i>
                                                </span>
                                                <input type="text" class="form-control" name="search"
                                                    placeholder="ابحث باستخدام الاسم..."
                                                    value="{{ request()->query('search') }}">
                                                <button class="btn btn-primary mb-0" type="submit">بحث</button>
                                            </div>
                                        </form>
                                    </div>

                                    <div class="col-md-6 col-lg-8 text-end">
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                            data-bs-target="#createWarehouseModal">
                                            إضافة مستودع جديد
                                        </button>
                                    </div>
                                </div>

                                {{-- Modal إضافة --}}
                                <div class="modal fade" id="createWarehouseModal" tabindex="-1"
                                    aria-labelledby="createWarehouseModalLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="createWarehouseModalLabel">
                                                    إضافة مستودع جديد
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>

                                            <form action="{{ route('warehouse.store') }}" method="post">
                                                @csrf
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">اسم المستودع</label>
                                                        <input type="text" name="name" class="form-control"
                                                            placeholder="ضع هنا اسم المستودع">
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label">رقم الهاتف</label>
                                                        <input type="text" name="phone" class="form-control"
                                                            placeholder="ضع هنا رقم الهاتف">
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label">المنطقة</label>
                                                        <input type="text" name="zone" class="form-control"
                                                            placeholder="ضع هنا المنطقة">
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label">العنوان</label>
                                                        <input type="text" name="address" class="form-control"
                                                            placeholder="ضع هنا العنوان">
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label">كلمة المرور</label>
                                                        <input type="password" name="password" class="form-control"
                                                            placeholder="ضع هنا كلمة المرور">
                                                    </div>
                                                </div>

                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">إغلاق</button>
                                                    <button type="submit" class="btn"
                                                        style="background-color: #990a24; color:#fff">
                                                        حفظ
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <form action="{{ route('Notifications.send') }}" method="POST" id="notificationForm">
                                    @csrf

                                    <div class="row mb-4 bg-light p-3 border-radius-md">

                                        <div class="col-md-12 mb-3">
                                            <label class="form-label">عنوان الإشعار</label>
                                            <input type="text" class="form-control" name="title" required>
                                        </div>

                                        <div class="col-md-12 mb-3">
                                            <label class="form-label">نص الرسالة</label>
                                            <textarea class="form-control" name="body" rows="4" required></textarea>
                                        </div>

                                        {{-- checkboxes داخل نفس الفورم --}}
                                        <div class="col-md-12">
                                            <label class="form-label">اختر المستخدمين</label>

                                            <div class="mb-2">
                                                <input type="checkbox" id="selectAll"> تحديد الكل
                                            </div>

                                            @foreach ($warehouses as $u)
                                                <div>
                                                    <input type="checkbox" name="user_ids[]" value="{{ $u->id }}"
                                                        class="user-checkbox">
                                                    {{ $u->name }}
                                                </div>
                                            @endforeach
                                        </div>

                                        <div class="col-md-12 text-end mt-3">
                                            <button type="submit" class="btn"
                                                style="background-color: #990a24; color:#fff;">
                                                إرسال الإشعار
                                            </button>
                                        </div>

                                    </div>
                                </form>
                                <div class="card">
                                    <div class="table-responsive">
                                        <table class="table align-items-center mb-0">
                                            <thead>
                                                <tr>
                                                    <th>الاسم</th>
                                                    <th>الهاتف</th>
                                                    <th>المنطقة</th>
                                                    <th>العنوان</th>
                                                    <th>الحالة</th>
                                                    <th>تعديل</th>
                                                    <th>حذف</th>
                                                </tr>
                                            </thead>

                                            <tbody>
                                                @forelse ($warehouses as $u)
                                                    <tr>

                                                        <td>{{ $u->name }}</td>
                                                        <td>{{ $u->phone }}</td>
                                                        <td>{{ $u->zone }}</td>
                                                        <td>{{ $u->address }}</td>

                                                        <td>
                                                            @if ($u->activated == 1)
                                                                <span class="badge bg-success">مفعل</span>
                                                            @else
                                                                <span class="badge bg-secondary">غير مفعل</span>
                                                            @endif
                                                        </td>

                                                        {{-- =========================
                            زر التعديل
                        ========================= --}}
                                                        <td>
                                                            <button class="btn btn-primary" data-bs-toggle="modal"
                                                                data-bs-target="#editModal{{ $u->id }}">
                                                                تعديل
                                                            </button>
                                                        </td>

                                                        {{-- =========================
                            زر الحذف (form مستقل)
                        ========================= --}}
                                                        <td>
                                                            <form action="{{ route('warehouse.destroy', $u->id) }}"
                                                                method="POST"
                                                                onsubmit="return confirm('هل أنت متأكد من الحذف؟');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button class="btn btn-danger">حذف</button>
                                                            </form>
                                                        </td>

                                                    </tr>

                                                    {{-- =========================
                        Modal التعديل
                    ========================= --}}
                                                    <div class="modal fade" id="editModal{{ $u->id }}"
                                                        tabindex="-1">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">

                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">تعديل المستودع</h5>
                                                                    <button type="button" class="btn-close"
                                                                        data-bs-dismiss="modal"></button>
                                                                </div>

                                                                <form action="{{ route('warehouse.update', $u->id) }}"
                                                                    method="POST">
                                                                    @csrf
                                                                    @method('PUT')

                                                                    <div class="modal-body">

                                                                        <div class="mb-3">
                                                                            <label>الاسم</label>
                                                                            <input type="text" name="name"
                                                                                value="{{ $u->name }}"
                                                                                class="form-control">
                                                                        </div>

                                                                        <div class="mb-3">
                                                                            <label>الهاتف</label>
                                                                            <input type="text" name="phone"
                                                                                value="{{ $u->phone }}"
                                                                                class="form-control">
                                                                        </div>

                                                                        <div class="mb-3">
                                                                            <label>المنطقة</label>
                                                                            <input type="text" name="zone"
                                                                                value="{{ $u->zone }}"
                                                                                class="form-control">
                                                                        </div>

                                                                        <div class="mb-3">
                                                                            <label>العنوان</label>
                                                                            <input type="text" name="address"
                                                                                value="{{ $u->address }}"
                                                                                class="form-control">
                                                                        </div>

                                                                        <div class="mb-3">
                                                                            <label>كلمة المرور</label>
                                                                            <input type="password" name="password"
                                                                                class="form-control">
                                                                        </div>

                                                                    </div>

                                                                    <div class="modal-footer">
                                                                        <button class="btn btn-secondary"
                                                                            data-bs-dismiss="modal">إغلاق</button>
                                                                        <button type="submit" class="btn"
                                                                            style="background-color:#990a24;color:#fff;">
                                                                            حفظ
                                                                        </button>
                                                                    </div>

                                                                </form>

                                                            </div>
                                                        </div>
                                                    </div>

                                                @empty
                                                    <tr>
                                                        <td colspan="7" class="text-center">لا توجد بيانات</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </main>
@endsection

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // يمكن تركه فارغاً إذا لم تستخدم تحديد الكل
    });
</script>

<!-- Core JS Files -->
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
        };
        Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
    }
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAllCheckbox = document.getElementById('selectAll');
        const userCheckboxes = document.querySelectorAll('.user-checkbox');
        const notificationForm = document.getElementById('notificationForm');

        // وظيفة "تحديد الكل"
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                userCheckboxes.forEach(checkbox => {
                    checkbox.checked = selectAllCheckbox.checked;
                });
            });
        }

        // التحقق قبل الإرسال للتأكد من اختيار مستخدم واحد على الأقل
        if (notificationForm) {
            notificationForm.addEventListener('submit', function(e) {
                const selectedUsers = document.querySelectorAll('.user-checkbox:checked');
                if (selectedUsers.length === 0) {
                    e.preventDefault(); // إيقاف إرسال النموذج
                    alert('الرجاء تحديد مستخدم واحد على الأقل لإرسال الإشعار.');
                }
            });
        }
    });
</script>

<script async defer src="https://buttons.github.io/buttons.js"></script>
<script src="../assets/js/argon-dashboard.min.js?v=2.1.0"></script>

</html>
