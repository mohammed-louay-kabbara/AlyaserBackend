<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    /**
     * Get all roles with their permissions
     */
    public function index()
    {
        $roles = Role::with('permissions')->get();
        return response()->json([
            'status' => true,
            'data' => $roles
        ]);
    }

    /**
     * Get a specific role with permissions
     */
    public function show($id)
    {
        $role = Role::with('permissions')->find($id);
        
        if (!$role) {
            return response()->json([
                'status' => false,
                'message' => 'الدور غير موجود'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $role
        ]);
    }

    /**
     * Create a new role
     */
    public function store(Request $request)
    {
        $request->validate([
            'name_en' => 'required|string|unique:roles,name_en',
            'name_ar' => 'required|string',
            // 'permissions' => 'array',
            // 'permissions.*' => 'exists:permissions,id'
        ]);

        DB::beginTransaction();
        try {
            $role = Role::create([
                'name_en' => $request->name_en,
                'name_ar' => $request->name_ar,
            ]);

            if ($request->has('permissions')) {
                $role->permissions()->attach($request->permissions);
            }

            DB::commit();
            
            return response()->json([
                'status' => true,
                'message' => 'تم إنشاء الدور بنجاح',
                'data' => $role->load('permissions')
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'فشل في إنشاء الدور: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a role
     */
    public function update(Request $request, $id)
    {
        $role = Role::find($id);
        
        if (!$role) {
            return response()->json([
                'status' => false,
                'message' => 'الدور غير موجود'
            ], 404);
        }

        $request->validate([
            'name_en' => 'sometimes|required|string|unique:roles,name_en,' . $id,
            'name_ar' => 'sometimes|required|string',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        DB::beginTransaction();
        try {
            if ($request->has('name_en')) {
                $role->name_en = $request->name_en;
            }
            if ($request->has('name_ar')) {
                $role->name_ar = $request->name_ar;
            }
            $role->save();

            if ($request->has('permissions')) {
                $role->permissions()->sync($request->permissions);
            }

            DB::commit();
            
            return response()->json([
                'status' => true,
                'message' => 'تم تحديث الدور بنجاح',
                'data' => $role->load('permissions')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'فشل في تحديث الدور: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a role
     */
    public function destroy($id)
    {
        $role = Role::find($id);
        
        if (!$role) {
            return response()->json([
                'status' => false,
                'message' => 'الدور غير موجود'
            ], 404);
        }

        // Check if role has users
        if ($role->users()->count() > 0) {
            return response()->json([
                'status' => false,
                'message' => 'لا يمكن حذف الدور لأنه مرتبط بمستخدمين'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $role->permissions()->detach();
            $role->delete();
            
            DB::commit();
            
            return response()->json([
                'status' => true,
                'message' => 'تم حذف الدور بنجاح'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'فشل في حذف الدور: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all permissions
     */
    public function getPermissions()
    {
        $permissions = Permission::all();
        return response()->json([
            'status' => true,
            'data' => $permissions
        ]);
    }

    /**
     * Seed default roles and permissions
     */
    public function seed()
    {
        DB::beginTransaction();
        try {
            // Create default permissions
            $permissions = [
                ['name' => 'view_dashboard', 'label_ar' => 'عرض لوحة التحكم', 'label_en' => 'View Dashboard', 'category' => 'dashboard'],
                ['name' => 'manage_users', 'label_ar' => 'إدارة المستخدمين', 'label_en' => 'Manage Users', 'category' => 'users'],
                ['name' => 'view_users', 'label_ar' => 'عرض المستخدمين', 'label_en' => 'View Users', 'category' => 'users'],
                ['name' => 'manage_roles', 'label_ar' => 'إدارة الأدوار', 'label_en' => 'Manage Roles', 'category' => 'users'],
                ['name' => 'manage_products', 'label_ar' => 'إدارة المنتجات', 'label_en' => 'Manage Products', 'category' => 'products'],
                ['name' => 'view_products', 'label_ar' => 'عرض المنتجات', 'label_en' => 'View Products', 'category' => 'products'],
                ['name' => 'manage_orders', 'label_ar' => 'إدارة الطلبات', 'label_en' => 'Manage Orders', 'category' => 'orders'],
                ['name' => 'view_orders', 'label_ar' => 'عرض الطلبات', 'label_en' => 'View Orders', 'category' => 'orders'],
                ['name' => 'manage_offers', 'label_ar' => 'إدارة العروض', 'label_en' => 'Manage Offers', 'category' => 'offers'],
                ['name' => 'view_offers', 'label_ar' => 'عرض العروض', 'label_en' => 'View Offers', 'category' => 'offers'],
                ['name' => 'manage_warehouse', 'label_ar' => 'إدارة المستودع', 'label_en' => 'Manage Warehouse', 'category' => 'warehouse'],
                ['name' => 'view_warehouse', 'label_ar' => 'عرض المستودع', 'label_en' => 'View Warehouse', 'category' => 'warehouse'],
                ['name' => 'manage_categories', 'label_ar' => 'إدارة الأصناف', 'label_en' => 'Manage Categories', 'category' => 'products'],
                ['name' => 'manage_analytics', 'label_ar' => 'إدارة التحليلات', 'label_en' => 'Manage Analytics', 'category' => 'analytics'],
                ['name' => 'view_analytics', 'label_ar' => 'عرض التحليلات', 'label_en' => 'View Analytics', 'category' => 'analytics'],
            ];

            foreach ($permissions as $permission) {
                Permission::firstOrCreate(
                    ['name' => $permission['name']],
                    $permission
                );
            }

            // Create default roles with permissions
            $roles = [
                [
                    'name_en' => 'admin',
                    'name_ar' => 'مدير النظام',
                    'permissions' => ['view_dashboard', 'manage_users', 'view_users', 'manage_roles', 'manage_products', 'view_products', 'manage_orders', 'view_orders', 'manage_offers', 'view_offers', 'manage_warehouse', 'view_warehouse', 'manage_categories', 'manage_analytics', 'view_analytics']
                ],
                [
                    'name_en' => 'manager',
                    'name_ar' => 'مدير',
                    'permissions' => ['view_dashboard', 'view_users', 'manage_products', 'view_products', 'manage_orders', 'view_orders', 'manage_offers', 'view_offers', 'manage_categories', 'view_analytics']
                ],
                [
                    'name_en' => 'warehouse_manager',
                    'name_ar' => 'مدير المستودع',
                    'permissions' => ['view_dashboard', 'view_products', 'view_orders', 'manage_warehouse', 'view_warehouse']
                ],
                [
                    'name_en' => 'driver',
                    'name_ar' => 'سائق',
                    'permissions' => ['view_orders']
                ],
                [
                    'name_en' => 'customer',
                    'name_ar' => 'عميل',
                    'permissions' => ['view_products', 'view_offers']
                ],
            ];

            foreach ($roles as $roleData) {
                $role = Role::firstOrCreate(
                    ['name_en' => $roleData['name_en']],
                    ['name_ar' => $roleData['name_ar']]
                );

                $permissionIds = Permission::whereIn('name', $roleData['permissions'])->pluck('id');
                $role->permissions()->sync($permissionIds);
            }

            DB::commit();
            
            return response()->json([
                'status' => true,
                'message' => 'تم إنشاء الأدوار والصلاحيات الافتراضية بنجاح'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'فشل في إنشاء الأدوار والصلاحيات: ' . $e->getMessage()
            ], 500);
        }
    }
}
