@extends('layouts.app')
@section('content')
    <main class="main-content position-relative border-radius-lg ">
        <div class="container-fluid py-4">
            <div class="row">
                <div class="col-12">
                    <div class="card mb-4">
                        <div class="card-header pb-0">
                            <h6>إدارة الإعلانات </h6>
                        </div>
                        <div class="card-body">
                            <div class="container-fluid py-4">
                                
                                <div class="row mb-4 align-items-center">
                                    {{-- <div class="col-md-6 col-lg-4 mb-3 mb-md-0">
                                        <form action="{{ url()->current() }}" method="GET">
                                            <div class="input-group">
                                                <span class="input-group-text text-body"><i class="fas fa-search" aria-hidden="true"></i></span>
                                                <input type="text" class="form-control" name="search"
                                                    placeholder="ابحث باستخدام الوصف..."
                                                    value="{{ request()->query('search') }}">
                                                <button class="btn btn-primary mb-0" type="submit">بحث</button>
                                            </div>
                                        </form>
                                    </div> --}}
                                    <div class="">
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                            data-bs-target="#modalCreateOffer">
                                            إضافة إعلان جديد
                                        </button>
                                    </div>
                                </div>

                                <div class="modal fade" id="modalCreateOffer" tabindex="-1" aria-labelledby="modalCreateOfferLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="modalCreateOfferLabel">إضافة إعلان جديد</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="{{ route('Offer.store') }}" method="post" enctype="multipart/form-data">
                                                <div class="modal-body">
                                                    @csrf
                                                    <div class="mb-3 text-start">
                                                        <label class="form-label">صورة الإعلان</label>
                                                        <input type="file" name="image" class="form-control" required>
                                                    </div>
                                                    <div class="mb-3 text-start">
                                                        <label class="form-label">الوصف</label>
                                                        <input type="text" placeholder="اكتب وصف الإعلان" name="description" class="form-control" required>
                                                    </div>
                                                    <div class="mb-3 text-start">
                                                        <label class="form-label">المنتج المرتبط</label>
                                                        <select name="product_id" class="form-control" required>
                                                            <option value="" disabled selected>اختر المنتج</option>
                                                            @foreach($products as $product)
                                                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="mb-3 text-start">
                                                        <label class="form-label">تاريخ الانتهاء</label>
                                                        <input type="date" name="expires_at" class="form-control" required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                                                    <button type="submit" class="btn" style="background-color: #990a24; color:#fff">حفظ الإعلان</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="table-responsive">
                                        <table class="table align-items-center mb-0">
                                            <thead>
                                                <tr>
                                                    <th>الصورة</th>
                                                    <th>الوصف</th>
                                                    <th>اسم المنتج</th>
                                                    <th>تاريخ انتهاء العرض</th>
                                                    <th class="text-center">العمليات</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($offers as $offer)
                                                    <tr>
                                                        <td>
                                                            <img src="{{ asset('storage/' . $offer->image) }}" width="60px" height="60px" style="object-fit: cover; border-radius: 8px;">
                                                        </td>
                                                        <td>
                                                            <div class="d-flex px-2 py-1">
                                                                <h6 class="mb-0">{{ $offer->description }}</h6>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="d-flex px-2 py-1">
                                                                <h6 class="mb-0">{{ $offer->product->name ?? 'منتج محذوف/غير متوفر' }}</h6>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="d-flex px-2 py-1">
                                                                <h6 class="mb-0">{{ \Carbon\Carbon::parse($offer->expires_at)->format('Y-m-d') }}</h6>
                                                            </div>
                                                        </td>
                                                        <td class="align-middle text-center text-sm">
                                                            <div class="d-flex justify-content-center gap-2">
                                                                <button type="button" class="btn btn-primary btn-sm mb-0" data-bs-toggle="modal" data-bs-target="#modalEdit{{ $offer->id }}">
                                                                    تعديل
                                                                </button>

                                                                <form action="{{ route('Offer.destroy', $offer->id) }}" method="post" onsubmit="return confirm('هل أنت متأكد من حذف هذا الإعلان؟ لا يمكن التراجع عن هذه العملية!');">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-danger btn-sm mb-0">حذف</button>
                                                                </form>
                                                            </div>

                                                            <div class="modal fade" id="modalEdit{{ $offer->id }}" tabindex="-1" aria-labelledby="modalEditLabel" aria-hidden="true">
                                                                <div class="modal-dialog">
                                                                    <div class="modal-content text-start">
                                                                        <div class="modal-header">
                                                                            <h5 class="modal-title" id="modalEditLabel">تعديل الإعلان</h5>
                                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                        </div>
                                                                        <form action="{{ route('Offer.update', $offer->id) }}" method="post" enctype="multipart/form-data">
                                                                            <div class="modal-body">
                                                                                @method('PUT')
                                                                                @csrf
                                                                                <div class="mb-3">
                                                                                    <label class="form-label">الصورة (اتركها فارغة إذا لم ترد تغييرها)</label>
                                                                                    <input type="file" name="image" class="form-control">
                                                                                </div>
                                                                                <div class="mb-3">
                                                                                    <label class="form-label">الوصف</label>
                                                                                    <input type="text" name="description" value="{{ $offer->description }}" class="form-control" required>
                                                                                </div>
                                                                                <div class="mb-3">
                                                                                    <label class="form-label">المنتج المرتبط</label>
                                                                                    <select name="product_id" class="form-control" required>
                                                                                        @foreach($products as $product)
                                                                                            <option value="{{ $product->id }}" {{ $offer->product_id == $product->id ? 'selected' : '' }}>
                                                                                                {{ $product->name }}
                                                                                            </option>
                                                                                        @endforeach
                                                                                    </select>
                                                                                </div>
                                                                                <div class="mb-3">
                                                                                    <label class="form-label">تاريخ الانتهاء</label>
                                                                                    <input type="date" name="expires_at" value="{{ \Carbon\Carbon::parse($offer->expires_at)->format('Y-m-d') }}" class="form-control" required>
                                                                                </div>
                                                                            </div>
                                                                            <div class="modal-footer">
                                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                                                                                <button type="submit" class="btn" style="background-color: #990a24; color:#fff">حفظ التعديلات</button>
                                                                            </div>
                                                                        </form>
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
    </main>
@endsection
<script src="../assets/js/core/popper.min.js"></script>
<script src="../assets/js/core/bootstrap.min.js"></script>
<script src="../assets/js/plugins/perfect-scrollbar.min.js"></script>
<script src="../assets/js/plugins/smooth-scrollbar.min.js"></script>
<script src="../assets/js/plugins/chartjs.min.js"></script>
<script>
    var win = navigator.platform.indexOf('Win') > -1;
    if (win && document.querySelector('#sidenav-scrollbar')) {
        var options = { damping: '0.5' }
        Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
    }
</script>
<script async defer src="https://buttons.github.io/buttons.js"></script>
<script src="../assets/js/argon-dashboard.min.js?v=2.1.0"></script>