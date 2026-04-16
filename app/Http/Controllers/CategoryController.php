<?php

namespace App\Http\Controllers;

use App\Models\category;
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

    public function store(Request $request)
    {
        // 1. التحقق من البيانات (إضافة شرط الصورة)
        $validator = Validator::make($request->all(), [
            'name'  => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // حد أقصى 2MB
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        $imagePath = null;
        // 2. معالجة رفع الصورة
        if ($request->hasFile('image')) {
            // تخزين الصورة في مجلد storage/app/public/categories
            $imagePath = $request->file('image')->store('categories', 'public');
        }
        // 3. الحفظ في قاعدة البيانات
        category::create([
            'name'  => $request->name,
            'image' => $imagePath,
        ]);

        return back()->with('success', 'تمت إضافة القسم بنجاح');
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
        // 1. جلب القسم أو إرجاع خطأ 404 إذا لم يوجد
        $category = category::findOrFail($id);

        // 2. التحقق من البيانات
        $validator = Validator::make($request->all(), [
            'name'  => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // 3. تجهيز البيانات للتحديث
        $data = [
            'name' => $request->name,
        ];

        // 4. معالجة الصورة في حال تم رفع ملف جديد
        if ($request->hasFile('image')) {

            // حذف الصورة القديمة من المجلد (Storage) لتوفير المساحة
            if ($category->image && Storage::disk('public')->exists($category->image)) {
                Storage::disk('public')->delete($category->image);
            }

            // تخزين الصورة الجديدة وتحديث المسار في مصفوفة البيانات
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        // 5. تنفيذ التحديث
        $category->update($data);

        return back()->with('success', 'تم تحديث القسم بنجاح');
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
       $category= category::where('id', $id)->first();
        // حذف الصورة القديمة من المجلد (Storage) لتوفير المساحة
        if ($category->image && Storage::disk('public')->exists($category->image)) {
            Storage::disk('public')->delete($category->image);
        }
        $category->delete();
        return back();
    }
}
