<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;


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

    public function getAdminWarehouses(Request $request)
    {
        $search = $request->input('search');
        $warehouses = User::where('role', 3)
            ->when($search, function ($query, $search) {
                return $query->where('name', 'LIKE', "%{$search}%")
                            ->orWhere('phone', 'LIKE', "%{$search}%");
            })
            ->latest()
            ->get();

        return response()->json($warehouses);
    }
    public function show_orders($id)
    {
        $Orders = Order::where('warehouse_id', $id)->get();
        return view('warehouse_show', compact('Orders'));
    }

    public function create()
    {
        
    }
    


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'      => 'required|string|max:255',
            'phone'     => 'required|string|unique:users,phone',
            'zone'      => 'required|string',
            'address'   => 'required|string',
            'password'  => 'required|string|min:8', 
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
        $warehouse = User::create([
            'name' => $request->name,
            'phone'=> $request->phone,
            'address'=> $request->address,
            'zone' => $request->zone,
            'password'  => Hash::make($request->password),
            'role' => 3,
            'activated' => 1
        ]);
        return response()->json(['message' => 'تمت إضافة المستودع بنجاح', 'warehouse' => $warehouse], 201);
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
        $validator = Validator::make($request->all(), [
            'name'      => 'required|string|max:255',
            'phone'     => 'required|string|unique:users,phone,' . $id,
            'zone'      => 'required|string',
            'address'   => 'required|string',
            'password'  => 'nullable|string|min:8', 
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $data = [
            'name' => $request->name,
            'phone'=> $request->phone,
            'address'=> $request->address,
            'zone' => $request->zone,
            'role' => 3,
            'activated' => 1
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $warehouse = User::where('id', $id)->first();
        if (!$warehouse) {
            return response()->json(['error' => 'المستودع غير موجود'], 404);
        }

        $warehouse->update($data);

        return response()->json(['message' => 'تم تحديث المستودع بنجاح', 'warehouse' => $warehouse], 200);
    }

    public function destroy($id)
    {
        $warehouse = User::where('id', $id)->first();
        if (!$warehouse) {
            return response()->json(['error' => 'المستودع غير موجود'], 404);
        }

        $warehouse->delete();
        return response()->json(['message' => 'تم حذف المستودع بنجاح'], 200);
    }

    public function dashboard(Request $request)
    {
        $user = Auth::user();
        if (!$user || $user->role != 3) {
            return redirect('/')->with('error', 'غير مصرح');
        }

        $search = $request->input('search');
        $statusFilter = $request->input('status');

        $orders = Order::with(['items.product', 'user'])
            ->where('warehouse_id', $user->id)
            ->when($search, function ($query, $search) {
                return $query->where('id', 'LIKE', "%{$search}%")
                            ->orWhereHas('user', function ($q) use ($search) {
                                $q->where('name', 'LIKE', "%{$search}%");
                            });
            })
            ->when($statusFilter, function ($query, $statusFilter) {
                return $query->where('status', $statusFilter);
            })
            ->latest()
            ->get();

        return view('warehouse.dashboard', compact('orders'));
    }

    public function markAsReady(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user || $user->role != 3) {
            return response()->json(['error' => 'غير مصرح'], 403);
        }

        $order = Order::where('id', $id)->where('warehouse_id', $user->id)->first();
        if (!$order) {
            return response()->json(['error' => 'الطلب غير موجود'], 404);
        }
        $order->status = 'processing';
        $order->save();
        return response()->json(['message' => 'تم تحديث حالة الطلب بنجاح'], 200);
    }

    public function printOrder($id)
    {
        $user = Auth::user();
        if (!$user || $user->role != 3) {
            return redirect('/')->with('error', 'غير مصرح');
        }

        $order = Order::with(['items.product', 'user'])
            ->where('id', $id)
            ->where('warehouse_id', $user->id)
            ->first();

        if (!$order) {
            return redirect('/warehouse/dashboard')->with('error', 'الطلب غير موجود');
        }

        return view('warehouse.print', compact('order'));
    }
}
