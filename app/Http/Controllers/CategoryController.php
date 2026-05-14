<?php

namespace App\Http\Controllers;

use App\Models\category;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = category::get();
        return response()->json($categories, 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {}
    public function show_admin(Request $request)
    {
        $query = category::query();
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        $categories = $query->get();
        return view('categories', compact('categories'));
    }

    public function getAdminCategories(Request $request)
    {
        $query = category::query();
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        $categories = $query->latest()->get();
        return response()->json($categories);
    }

    public function store(Request $request)
    {
        // Build validation rules dynamically
        $rules = [
            'name'  => 'required|string|max:255',
        ];
        
        // Only add image validation if file is present
        if ($request->hasFile('image')) {
            $rules['image'] = 'image|mimes:jpeg,png,jpg,gif|max:2048';
        }
        
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
        
        $imagePath = null;
        // معالجة رفع الصورة
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $imagePath = $request->file('image')->store('categories', 'public');
        }
        
        // الحفظ في قاعدة البيانات
        $category = category::create([
            'name'  => $request->name,
            'image' => $imagePath,
        ]);

        return response()->json(['message' => 'تمت إضافة القسم بنجاح', 'category' => $category], 201);
    }


    public function show(category $category) {}


    public function edit(category $category)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // جلب القسم أو إرجاع خطأ 404 إذا لم يوجد
        $category = category::findOrFail($id);

        // Build validation rules dynamically
        $rules = [
            'name'  => 'required|string|max:255',
        ];
        
        // Only add image validation if file is present
        if ($request->hasFile('image')) {
            $rules['image'] = 'image|mimes:jpeg,png,jpg,gif|max:2048';
        }
        
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // تجهيز البيانات للتحديث
        $data = [
            'name' => $request->name,
        ];

        // معالجة الصورة في حال تم رفع ملف جديد
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            // حذف الصورة القديمة من المجلد (Storage) لتوفير المساحة
            if ($category->image && Storage::disk('public')->exists($category->image)) {
                Storage::disk('public')->delete($category->image);
            }
            // تخزين الصورة الجديدة وتحديث المسار في مصفوفة البيانات
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        // تنفيذ التحديث
        $category->update($data);

        return response()->json(['message' => 'تم تحديث القسم بنجاح', 'category' => $category], 200);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
       $category= category::where('id', $id)->first();
        if (!$category) {
            return response()->json(['error' => 'القسم غير موجود'], 404);
        }
        // حذف الصورة القديمة من المجلد (Storage) لتوفير المساحة
        if ($category->image && Storage::disk('public')->exists($category->image)) {
            Storage::disk('public')->delete($category->image);
        }
        $category->delete();
        return response()->json(['message' => 'تم حذف القسم بنجاح'], 200);
    }

    public function assignProducts(Request $request, $id)
    {
        $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'integer|exists:products,id'
        ]);

        $category = category::findOrFail($id);

        // Update category_id for each product (one-to-many relationship)
        Product::whereIn('id', $request->product_ids)->update(['category_id' => $id]);

        return response()->json(['message' => 'تم إضافة المنتجات بنجاح'], 200);
    }

    public function removeProduct($categoryId, $productId)
    {
        $category = category::findOrFail($categoryId);
        $product = Product::findOrFail($productId);

        // Remove product from category by setting category_id to null
        $product->update(['category_id' => null]);

        return response()->json(['message' => 'تم إزالة المنتج من الصنف بنجاح'], 200);
    }
}
