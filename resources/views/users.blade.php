@extends('layouts.app')
@section('content')
    <main class="main-content position-relative border-radius-lg ">
        <div class="container-fluid py-4">
            <div class="row">
                <div class="col-12">
                    <div class="card mb-4">
                        <div class="card-header pb-0">
                            <h6>إدارة المستخدمين</h6>
                        </div>
                        <div class="card-body">
                            <div class="">
                                <div class="container-fluid py-4">
                                    <div class="row mb-4 align-items-center">
                                        <div class="col-md-6 col-lg-4 mb-3 mb-md-0">
                                            <form action="{{ url()->current() }}" method="GET">
                                                <div class="input-group">
                                                    <span class="input-group-text text-body"><i class="fas fa-search"
                                                            aria-hidden="true"></i></span>
                                                    <input type="text" class="form-control" name="search"
                                                        placeholder="ابحث باستخدام الاسم..."
                                                        value="{{ request()->query('search') }}">
                                                    <button class="btn btn-primary mb-0" type="submit">بحث</button>
                                                </div>
                                            </form>
                                        </div>

                                        <div class="col-md-6 col-lg-8 text-end">
                                            <button type="button" class="btn btn-success mb-0 mx-1"
                                                onclick="submitBulkAction(1)">
                                                <i class="fas fa-check me-1"></i> تفعيل المحدد
                                            </button>
                                            <button type="button" class="btn btn-secondary mb-0 mx-1"
                                                onclick="submitBulkAction(0)">
                                                <i class="fas fa-ban me-1"></i> تجميد المحدد
                                            </button>
                                        </div>
                                        <br> <br>

                                        <form action="{{ url()->current() }}" method="GET" id="filterForm">
                                            <input type="hidden" name="search" value="{{ request()->query('search') }}">

                                            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                                <label
                                                    class="btn btn-outline-primary {{ request('activated') == '' || request('activated') == 'all' ? 'active' : '' }}">
                                                    <input type="radio" name="activated" value="all"
                                                        onchange="this.form.submit()"
                                                        {{ request('activated') == '' || request('activated') == 'all' ? 'checked' : '' }}>
                                                    الكل
                                                </label>

                                                <label
                                                    class="btn btn-outline-success {{ request('activated') == '1' ? 'active' : '' }}">
                                                    <input type="radio" name="activated" value="1"
                                                        onchange="this.form.submit()"
                                                        {{ request('activated') == '1' ? 'checked' : '' }}> المقبولين
                                                </label>
                                                <label
                                                    class="btn btn-outline-danger {{ request('activated') == '0' ? 'active' : '' }}">
                                                    <input type="radio" name="activated" value="0"
                                                        onchange="this.form.submit()"
                                                        {{ request('activated') == '0' ? 'checked' : '' }}> غير المقبولين
                                                </label>
                                            </div>
                                        </form>

                                    </div>

                                    <div class="card">
                                        <div class="table-responsive">
                                            <table class="table align-items-center mb-0">
                                                <thead>
                                                    <tr>
                                                        <th class="text-center text-secondary text-xxs font-weight-bolder opacity-7"
                                                            style="width: 50px;">
                                                            <div class="form-check d-flex justify-content-center mb-0">
                                                                <input class="form-check-input" type="checkbox"
                                                                    id="selectAll">
                                                            </div>
                                                        </th>
                                                        <th class="">
                                                            الاسم</th>
                                                        <th class="">
                                                            اسم المحل</th>
                                                        <th class="">
                                                            الرقم</th>
                                                        <th class="">
                                                            اسم المنطقة</th>
                                                        <th class="">
                                                            الدور</th>
                                                        <th class="">
                                                            الحالة</th>
                                                        <th class="">
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($users as $u)
                                                        <tr>
                                                            <td class="align-middle text-center">
                                                                <div class="form-check d-flex justify-content-center mb-0">
                                                                    <input class="form-check-input user-checkbox"
                                                                        type="checkbox" value="{{ $u->id }}">
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div class="d-flex px-2 py-1">
                                                                    <div class="d-flex flex-column justify-content-center">
                                                                        <a href="{{ route('orders_user',$u->id) }}" class="mb-0 ">{{ $u->name }}</h6>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td class="align-middle text-center">
                                                                <p class="text-xs font-weight-bold mb-0">
                                                                    {{ $u->shop_name }}
                                                                </p>
                                                            </td>
                                                            <td class="align-middle text-center">
                                                                <p class="text-xs font-weight-bold mb-0">
                                                                    {{ $u->phone }}
                                                                </p>
                                                            </td>
                                                            <td class="align-middle text-center">
                                                                <p class="text-xs font-weight-bold mb-0">
                                                                    {{ $u->zone }}
                                                                </p>
                                                            </td>
                                                            <td class="align-middle text-center">
                                                                @if ($u->role == 1)
                                                                    <span
                                                                        class="text-xs font-weight-bold mb-0 text-danger">أدمن</span>
                                                                @elseif ($u->role == 2)
                                                                    <span
                                                                        class="text-xs font-weight-bold mb-0 text-primary">زبون</span>
                                                                @else
                                                                    <span
                                                                        class="text-xs font-weight-bold mb-0 text-info">مستودع</span>
                                                                @endif
                                                            </td>
                                                            <td class="align-middle text-center text-sm">
                                                                @if ($u->activated == 0)
                                                                    <span
                                                                        class="badge badge-sm bg-gradient-secondary">مجمّد</span>
                                                                @else
                                                                    <span
                                                                        class="badge badge-sm bg-gradient-success">مفعّل</span>
                                                                @endif
                                                            </td>
                                                            <td class="align-middle text-center text-sm">
                                                                <button type="button" class="btn btn-primary"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#exampleModal{{ $u->id }}">
                                                                    تعيين كلمة المرور
                                                                </button>
                                                                <div class="modal fade"
                                                                    id="exampleModal{{ $u->id }}" tabindex="-1"
                                                                    aria-labelledby="exampleModalLabel"
                                                                    aria-hidden="true">
                                                                    <div class="modal-dialog">
                                                                        <div class="modal-content">
                                                                            <div class="modal-header">
                                                                                <h5 class="modal-title"
                                                                                    id="exampleModalLabel">تغير كلمة السر
                                                                                </h5>
                                                                                <button type="button" class="btn-close"
                                                                                    data-bs-dismiss="modal"
                                                                                    aria-label="Close"></button>
                                                                            </div>
                                                                            <div class="modal-body">
                                                                                <form
                                                                                    action="{{ route('forgot-password') }}"
                                                                                    method="post">
                                                                                    @csrf
                                                                                    <input type="hidden" name="user_id"
                                                                                        value="{{ $u->id }}">
                                                                                    <input type="text"
                                                                                        placeholder="ضع هنا كلمة المرور الجديدة"
                                                                                        name="password"
                                                                                        class="form-control"
                                                                                        id="">
                                                                            </div>
                                                                            <div class="modal-footer">
                                                                                <button type="button"
                                                                                    class="btn btn-secondary"
                                                                                    data-bs-dismiss="modal">إغلاق</button>
                                                                                <button type="submit" class="btn "
                                                                                    style="background-color: #990a24; color:#fff">
                                                                                    حفظ</button>
                                                                                </form>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
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
                </div>
            </div>
        </div>
    </main>
@endsection

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // وظيفة "تحديد الكل"
        const selectAllCheckbox = document.getElementById('selectAll');
        const userCheckboxes = document.querySelectorAll('.user-checkbox');

        selectAllCheckbox.addEventListener('change', function() {
            userCheckboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });
        });
    });

    // وظيفة إرسال الإجراء الجماعي للسيرفر
    function submitBulkAction(status) {
        // جمع معرفات (IDs) المستخدمين المحددين
        const selectedCheckboxes = document.querySelectorAll('.user-checkbox:checked');
        const selectedIds = Array.from(selectedCheckboxes).map(cb => cb.value);

        // التحقق من أنه تم تحديد مستخدم واحد على الأقل
        if (selectedIds.length === 0) {
            alert('الرجاء تحديد مستخدم واحد على الأقل أولاً.');
            return;
        }

        // تأكيد الإجراء من المدير
        const actionName = status === 1 ? 'تفعيل' : 'تجميد';
        if (!confirm('هل أنت متأكد أنك تريد ' + actionName + ' الحسابات المحددة؟')) {
            return;
        }

        // إرسال الطلب إلى السيرفر
        fetch('/admin/users/bulk-toggle-status', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    ids: selectedIds,
                    activated: status
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // إعادة تحميل الصفحة لرؤية التغييرات فوراً (أو يمكنك تحديث الـ Badges برمجياً)
                    window.location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('حدث خطأ أثناء الاتصال بالسيرفر.');
            });
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


</html>
