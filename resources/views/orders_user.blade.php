@extends('layouts.app')
@section('content')
    <main class="main-content position-relative border-radius-lg ">
        <div class="container-fluid py-4">
            <div class="row">
                <div class="col-12">
                    <div class="card mb-4">
                        <div class="card-header pb-0">
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
                                                        placeholder="ابحث باستخدام رقم الفاتورة..."
                                                        value="{{ request()->query('search') }}">
                                                    <button class="btn btn-primary mb-0" type="submit">بحث</button>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="col-md-6 col-lg-8 text-end">
                                            <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                                data-bs-target="#exampleModalcreatecategory"> تصدير pdf
                                            </button>
                                        </div>
                                    </div>

                                    <div class="card">
                                        <div class="table-responsive">
                                            <table class="table align-items-center mb-0">
                                                <thead>
                                                    <tr>
                                                        <th class="">
                                                            id</th>
                                                        <th class="">
                                                            المجموع</th>
                                                        <th class="">
                                                            تاريخ الطلب
                                                        </th>
                                                        <th class="">

                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($Orders as $u)
                                                        <tr>

                                                            <td>
                                                                <div class="d-flex px-2 py-1">
                                                                    <div class="d-flex flex-column justify-content-center">
                                                                        <h6 class="mb-0 ">{{ $u->id }}</h6>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div class="d-flex px-2 py-1">
                                                                    <div class="d-flex flex-column justify-content-center">
                                                                        <h6 style="color: #000" class="mb-0 ">
                                                                            {{ $u->total_amount }}</h6>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div class="d-flex px-2 py-1">
                                                                    <div class="d-flex flex-column justify-content-center">
                                                                        <h6 style="color: #000" class="mb-0 ">
                                                                            {{ $u->created_at }}</h6>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td class="align-middle text-center text-sm">
                                                                <div class="d-flex">
                                                                    <form action="" method="get">
                                                                        <button class="btn btn-primary">تفاصيل
                                                                            الطلب</button>
                                                                    </form>
                                                                    <form action="{{ route('Order.destroy', $u->id) }}"
                                                                        method="post"
                                                                        onsubmit="return confirm('هل أنت متأكد من حذف هذا الصنف؟ لا يمكن التراجع عن هذه العملية!');">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit"
                                                                            class="btn btn-danger">حذف</button>
                                                                    </form>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <div class="modal fade" id="exampleModal{{ $u->id }}"
                                                            tabindex="-1" aria-labelledby="exampleModalLabel"
                                                            aria-hidden="true">
                                                            <div class="modal-dialog">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title" id="exampleModalLabel">
                                                                             تفاصيل 
                                                                        </h5>
                                                                        <button type="button" class="btn-close"
                                                                            data-bs-dismiss="modal"
                                                                            aria-label="Close"></button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        <form action="{{ route('forgot-password') }}"
                                                                            method="post">
                                                                            @csrf
                                                                            <input type="hidden" name="user_id"
                                                                                value="{{ $u->id }}">
                                                                            <input type="text"
                                                                                placeholder="ضع هنا كلمة المرور الجديدة"
                                                                                name="password" class="form-control"
                                                                                id="">
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary"
                                                                            data-bs-dismiss="modal">إغلاق</button>
                                                                        <button type="submit" class="btn "
                                                                            style="background-color: #990a24; color:#fff">
                                                                            حفظ</button>
                                                                        </form>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
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
