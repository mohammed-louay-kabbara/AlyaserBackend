<?php

namespace App\Http\Controllers;

use App\Models\category;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories=category::get();
        return response()->json($categories, 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);
        if ($validator->fails()) {
            return back();
        } 
        category::create([
            'name' => $request->name
        ]);
        return response()->json(['تم حفظ الصنف بنجاح'], 200);
    }


    public function show(category $category)
    {
        
    }


    public function edit(category $category)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
          $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);
        if ($validator->fails()) {
            return back();
        } 
        category::where('id',$id)->update([
            'name' => $request->name
        ]);
        return response()->json(['تم حفظ الصنف بنجاح'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        category::where('id',$id)->delete();
        return response()->json(['تم الحذف بنجاح'], 200);
    }
}
