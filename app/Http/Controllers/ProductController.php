<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $Products= Product::get();
        return response()->json($Products, 200);
    }

public function search(Request $request)
{
    // 1. التحقق من صحة المدخلات (Validation)
    $request->validate([
        'name' => 'required|string|min:1'
    ], [
        'name.required' => 'يرجى إدخال محرف واحد على الأقل للبحث',
    ]);
    // 2. البحث في قاعدة البيانات مع جلب النتائج كصفحات (20 منتج في الصفحة)
    $products = Product::where('name', 'LIKE', '%' . $request->name . '%')
                       ->get(); 
    // 3. التحقق مما إذا كانت النتيجة فارغة
    if ($products->isEmpty()) {
        return response()->json([
            'status' => false,
            'message' => 'لم يتم العثور على منتجات مطابقة لبحثك',
            'data' => []
        ], 404); // 404 Not Found
    }
    // 4. إرجاع النتائج بنجاح
    return response()->json([
        'status' => true,
        'message' => 'تم جلب النتائج بنجاح',
        'data' => $products
    ], 200);
}

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        //
    }
}
