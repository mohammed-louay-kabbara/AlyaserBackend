@extends('layouts.app')

@section('content')
    <main class="main-content position-relative border-radius-lg ">
        <div class="container-fluid py-4">
            <div class="row">
                <div class="col-12">
                    <div class="card mb-4">
                        <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                            <h6>إرسال إشعارات للمستخدمين</h6>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('Notifications.send') }}" method="POST" id="notificationForm">
                                @csrf
                                
                                <div class="row mb-4 bg-light p-3 border-radius-md">
                                    <div class="col-md-12 mb-3">
                                        <label for="title" class="form-label">عنوان الإشعار <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="title" id="title" placeholder="أدخل عنوان الإشعار هنا" required>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label for="body" class="form-label">نص الرسالة <span class="text-danger">*</span></label>
                                        <textarea class="form-control" name="body" id="body" rows="4" placeholder="اكتب نص الرسالة هنا..." required></textarea>
                                    </div>
                                    <div class="col-md-12 text-end">
                                        <button type="submit" class="btn" style="background-color: #990a24; color:#fff;">
                                            <i class="fas fa-paper-plane me-2"></i> إرسال الإشعار
                                        </button>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table align-items-center mb-0">
                                        <thead>
                                            <tr>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7" style="width: 50px;">
                                                    <div class="form-check d-flex justify-content-center">
                                                        <input class="form-check-input" type="checkbox" id="selectAll">
                                                    </div>
                                                </th>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                    اسم المستخدم
                                                </th>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                    البريد الإلكتروني
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($users as $u)
                                                <tr>
                                                    <td>
                                                        <div class="form-check d-flex justify-content-center">
                                                            <input class="form-check-input user-checkbox" type="checkbox" name="user_ids[]" value="{{ $u->id }}">
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex px-2 py-1">
                                                            <div class="d-flex flex-column justify-content-center">
                                                                <h6 class="mb-0 text-sm">{{ $u->name }}</h6>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <p class="text-xs font-weight-bold mb-0">{{ $u->email }}</p>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </form>
                            </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

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
@endsection