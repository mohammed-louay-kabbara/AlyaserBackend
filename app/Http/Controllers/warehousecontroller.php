<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\Order;


class warehousecontroller extends Controller
{

    public function index(Request $request)
    {
        $search = $request->input('search');
        $warehouses = User::where('role', 3)
            ->when($search, function ($query, $search) {
                return $query->where('name', 'LIKE', "%{$search}%")
                            ->orWhere('phone', 'LIKE', "%{$search}%");
            })
            ->get();
            
        return view('warehouses', compact('warehouses'));
    }
    public function show_orders($id)
    {
        $Orders = Order::where('warehouse_id', $id)->get();
        return view('warehouse_show', compact('Orders'));
    }

    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        
        Validator::make($request->all(), [
            'name'      => 'required|string|max:255',
            'phone'     => 'required|string|unique:users,phone',
            'zone'      => 'required|string',
            'address'   => 'required|string',
            'password'  => 'required|string|min:8', 
        ]);
        User::create([
            'name' => $request->name,
            'phone'=> $request->phone,
            'address'=> $request->address,
            'zone' => $request->zone,
            'password'  => Hash::make($request->password),
            'role' => 3,
            'activated' => 1
        ]);
        return back();
    }

    /**
     * Display the specified resource.
     */


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        Validator::make($request->all(), [
            'name'      => 'required|string|max:255',
            'phone'     => 'required|string|unique:users,phone',
            'zone'      => 'required|string',
            'address'   => 'required|string',
            'password'  => 'required|string|min:8', 
        ]);
        User::where('id',$id)->update([
            'name' => $request->name,
            'phone'=> $request->phone,
            'address'=> $request->address,
            'zone' => $request->zone,
            'password'  => Hash::make($request->password),
            'role' => 3,
            'activated' => 1
        ]);
        return back();
    }

    public function destroy($id)
    {
        User::where('id',$id)->delete();
        return back();
    }
}
