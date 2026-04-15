@extends('layouts.app')
@section('content')
    <main class="main-content position-relative border-radius-lg">
        <div class="container-fluid py-4">
            <div class="row">
                <div class="col-12">
                    <div class="card mb-4">
                        <div class="card-header pb-0">
                            <h6>إدارة المنتجات</h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4 align-items-center">
                                <form action="{{ route('Product-search') }}" method="GET">
                                    <div class="row mb-4 align-items-center">
                                        <div class="col-md-4">
                                            <div class="input-group">
                                                <span class="input-group-text text-body"><i
                                                        class="fas fa-search"></i></span>
                                                <input type="text" class="form-control" name="search"
                                                    placeholder="ابحث بالاسم..." value="{{ request('search') }}">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="btn-group" role="group" aria-label="Filter products">
                                                <input type="radio" class="btn-check" name="stock_status" id="all"
                                                    value="all"
                                                    {{ request('stock_status', 'all') == 'all' ? 'checked' : '' }}
                                                    onchange="this.form.submit()">
                                                <label class="btn btn-outline-primary mb-0" for="all">الكل</label>

                                                <input type="radio" class="btn-check" name="stock_status" id="available"
                                                    value="available"
                                                    {{ request('stock_status') == 'available' ? 'checked' : '' }}
                                                    onchange="this.form.submit()">
                                                <label class="btn btn-outline-primary mb-0" for="available">متوفرة</label>

                                                <input type="radio" class="btn-check" name="stock_status"
                                                    id="out_of_stock" value="out_of_stock"
                                                    {{ request('stock_status') == 'out_of_stock' ? 'checked' : '' }}
                                                    onchange="this.form.submit()">
                                                <label class="btn btn-outline-primary mb-0"
                                                    for="out_of_stock">منتهية</label>
                                            </div>
                                        </div>

                                        <div class="col-md-2">
                                            <button class="btn btn-primary w-100 mb-0" type="submit">تطبيق</button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <div class="table-responsive p-0">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th
                                                class="text-center text-uppercase text-secondary  font-weight-bolder opacity-7">
                                                صورة</th>
                                            <th class="text-uppercase text-secondary  font-weight-bolder opacity-7 ps-2">
                                                الاسم</th>
                                            <th
                                                class="text-center text-uppercase text-secondary  font-weight-bolder opacity-7">
                                                سعر القطعة</th>
                                            <th
                                                class="text-center text-uppercase text-secondary  font-weight-bolder opacity-7">
                                                سعر الجملة</th>
                                            <th
                                                class="text-center text-uppercase text-secondary  font-weight-bolder opacity-7">
                                                الكمية</th>
                                            <th
                                                class="text-center text-uppercase text-secondary  font-weight-bolder opacity-7">
                                                اسم الصنف</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($Products as $u)
                                            <tr>
                                                <td class="align-middle text-center">
                                                    <div class="d-flex justify-content-center align-items-center"
                                                        id="image-container-{{ $u->id }}">
                                                        @if ($u->image)
                                                            <div class="position-relative">
                                                                <img src="{{ asset($u->image) }}" class="avatar avatar-sm"
                                                                    alt="صورة المنتج">
                                                                <button type="button"
                                                                    class="btn btn-sm btn-danger p-1 px-2 position-absolute"
                                                                    style="top: -10px; right: -10px; border-radius: 50%;"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#deleteImageModal"
                                                                    onclick="prepareDelete({{ $u->id }})">
                                                                    <i class="fas fa-times "></i>
                                                                </button>
                                                            </div>
                                                        @else
                                                            <label for="file-{{ $u->id }}"
                                                                class="btn btn-sm btn-outline-primary mb-0">رفع</label>
                                                            <input type="file" id="file-{{ $u->id }}"
                                                                class="d-none image-upload-input"
                                                                data-id="{{ $u->id }}">
                                                        @endif
                                                    </div>
                                                </td>
                                                <td>
                                                    <h6 class="mb-0 ">{{ $u->name }}</h6>
                                                </td>
                                                <td class="align-middle text-center">
                                                    <span
                                                        class="text-secondary  font-weight-bold">{{ number_format($u->retail_price, 2) }}</span>
                                                </td>
                                                <td class="align-middle text-center">
                                                    <span
                                                        class="text-secondary  font-weight-bold">{{ number_format($u->wholesale_price, 2) }}</span>
                                                </td>
                                                <td class="align-middle text-center">
                                                    <span
                                                        class="text-secondary  font-weight-bold">{{ $u->quantity }}</span>
                                                </td>
                                                <td class="align-middle text-center">
                                                    <span class="text-secondary  font-weight-bold">
                                                        {{ $u->category_id ? $u->category->name : 'لم يتم الربط' }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-center py-4">
                                {{ $Products->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="deleteImageModal" tabindex="-1" aria-labelledby="deleteImageModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteImageModalLabel">تأكيد حذف الصورة</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-start">
                        هل أنت متأكد من رغبتك في حذف صورة هذا المنتج؟
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <form id="confirmDeleteForm" method="POST" action="">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">نعم، احذف الصورة</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
<script>
    // دالة تمرير المعرف للمودال
    function prepareDelete(productId) {
        const form = document.getElementById('confirmDeleteForm');
        // تأكد من أن هذا الرابط يطابق الـ Route المبرمج في Laravel
        form.action = `/admin/products/${productId}/delete-image`;
    }

    // كود رفع الصور (AJAX) كما هو مع إضافة تصحيح بسيط للكونتينر
    document.addEventListener('DOMContentLoaded', function() {
        const uploadInputs = document.querySelectorAll('.image-upload-input');
        uploadInputs.forEach(input => {
            input.addEventListener('change', function() {
                let file = this.files[0];
                if (!file) return;

                let productId = this.getAttribute('data-id');
                let formData = new FormData();
                formData.append('image', file);

                let label = document.querySelector('label[for="file-' + productId + '"]');
                let originalText = label.innerHTML;
                label.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                label.classList.add('disabled');

                fetch(`/admin/products/${productId}/upload-image`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload(); // أسهل طريقة لتحديث الواجهة بعد الرفع
                        } else {
                            alert('خطأ: ' + data.message);
                            label.innerHTML = originalText;
                            label.classList.remove('disabled');
                        }
                    });
            });
        });
    });
</script>
