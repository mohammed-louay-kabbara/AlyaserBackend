<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class StaffController extends Controller
{
    public function getAdminStaff(Request $request)
    {
        $query = User::where('role_id', '!=', 2);

        $search = $request->input('search');
        $status = $request->input('status');
        $perPage = $request->input('per_page', 10);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        if ($status) {
            if ($status == 'pending') {
                $query->where('status', 'pending');
            } elseif ($status == '1') {
                $query->where('activated', 1);
            } elseif ($status == '0') {
                $query->where('activated', 0);
            }
        }

        $staff = $query->latest()->paginate($perPage);

        return response()->json($staff);
    }

    public function createStaff(Request $request)
    {
        $defaultPassword = '12345678';
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:users,phone',
            'address' => 'nullable|string',
            'zone' => 'nullable|string',
            'role_id' => 'required|exists:roles,id',
            'activated' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'خطأ في البيانات المدخلة',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $staff = User::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'address' => $request->address,
                'zone' => $request->zone,
                'password' => Hash::make($defaultPassword),
                'force_password_change' => 1,
                'role_id' => $request->role_id,
                'activated' => $request->filled('activated') ? $request->activated : 1
            ]);
            $staff->update(['user_number' => 'emp' . '_' . str_pad($staff->id, 6, '0', STR_PAD_LEFT)]);

            return response()->json([
                'status' => 'success',
                'message' => 'تم إضافة الموظف بنجاح',
                'staff' => $staff
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'فشل في إضافة الموظف',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateStaff(Request $request, $id)
    {
        $staff = User::find($id);

        if (!$staff) {
            return response()->json([
                'status' => 'error',
                'message' => 'الموظف غير موجود'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'phone' => 'sometimes|required|string|unique:users,phone,' . $id,
            'address' => 'nullable|string',
            'zone' => 'nullable|string',
            'password' => 'nullable|string|min:8',
            'role_id' => 'sometimes|required',
            'activated' => 'sometimes|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'خطأ في البيانات المدخلة',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $updateData = [
                'name' => $request->filled('name') ? $request->name : $staff->name,
                'phone' => $request->filled('phone') ? $request->phone : $staff->phone,
                'address' => $request->filled('address') ? $request->address : $staff->address,
                'zone' => $request->filled('zone') ? $request->zone : $staff->zone,
                'role_id' => $request->filled('role_id') ? $request->role_id : $staff->role_id,
                'activated' => $request->filled('activated') ? $request->activated : $staff->activated
            ];

            if ($request->filled('password') && !empty($request->password)) {
                $updateData['password'] = Hash::make($request->password);
            }

            $staff->update($updateData);

            return response()->json([
                'status' => 'success',
                'message' => 'تم تحديث الموظف بنجاح',
                'staff' => $staff
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'فشل في تحديث الموظف',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteStaff($id)
    {
        $staff = User::find($id);

        if (!$staff) {
            return response()->json([
                'status' => 'error',
                'message' => 'الموظف غير موجود'
            ], 404);
        }

        try {
            $staff->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'تم حذف الموظف بنجاح'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'فشل في حذف الموظف',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getStaffDetail($id)
    {
        $staff = User::with(['orders' => function($query) {
            $query->select('id', 'user_id', 'total_amount', 'status', 'created_at')
                  ->latest()
                  ->limit(10);
        }])->find($id);

        if (!$staff) {
            return response()->json([
                'status' => 'error',
                'message' => 'الموظف غير موجود'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'staff' => $staff
        ]);
    }
}
