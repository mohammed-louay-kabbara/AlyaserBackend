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
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id'
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
                ['name' => 'view_analytics', 'label_ar' => 'عرض التحليلات', 'label_en' => 'View Analytics', 'category' => 'dashboard'],
                ['name' => 'view_users', 'label_ar' => 'عرض المستخدمين', 'label_en' => 'View Users', 'category' => 'users'],
                ['name' => 'create_users', 'label_ar' => 'إنشاء مستخدمين', 'label_en' => 'Create Users', 'category' => 'users'],
                ['name' => 'edit_users', 'label_ar' => 'تعديل المستخدمين', 'label_en' => 'Edit Users', 'category' => 'users'],
                ['name' => 'delete_users', 'label_ar' => 'حذف المستخدمين', 'label_en' => 'Delete Users', 'category' => 'users'],
                ['name' => 'manage_user_roles', 'label_ar' => 'إدارة أدوار المستخدمين', 'label_en' => 'Manage User Roles', 'category' => 'users'],
                ['name' => 'manage_users', 'label_ar' => 'إدارة المستخدمين', 'label_en' => 'Manage Users', 'category' => 'users'],
                ['name' => 'manage_roles', 'label_ar' => 'إدارة الأدوار', 'label_en' => 'Manage Roles', 'category' => 'users'],
                ['name' => 'view_products', 'label_ar' => 'عرض المنتجات', 'label_en' => 'View Products', 'category' => 'products'],
                ['name' => 'create_products', 'label_ar' => 'إنشاء منتجات', 'label_en' => 'Create Products', 'category' => 'products'],
                ['name' => 'edit_products', 'label_ar' => 'تعديل المنتجات', 'label_en' => 'Edit Products', 'category' => 'products'],
                ['name' => 'delete_products', 'label_ar' => 'حذف المنتجات', 'label_en' => 'Delete Products', 'category' => 'products'],
                ['name' => 'manage_products', 'label_ar' => 'إدارة المنتجات', 'label_en' => 'Manage Products', 'category' => 'products'],
                ['name' => 'export_products', 'label_ar' => 'تصدير المنتجات', 'label_en' => 'Export Products', 'category' => 'products'],
                ['name' => 'view_categories', 'label_ar' => 'عرض الأصناف', 'label_en' => 'View Categories', 'category' => 'categories'],
                ['name' => 'create_categories', 'label_ar' => 'إنشاء أصناف', 'label_en' => 'Create Categories', 'category' => 'categories'],
                ['name' => 'edit_categories', 'label_ar' => 'تعديل الأصناف', 'label_en' => 'Edit Categories', 'category' => 'categories'],
                ['name' => 'delete_categories', 'label_ar' => 'حذف الأصناف', 'label_en' => 'Delete Categories', 'category' => 'categories'],
                ['name' => 'view_offers', 'label_ar' => 'عرض العروض', 'label_en' => 'View Offers', 'category' => 'offers'],
                ['name' => 'create_offers', 'label_ar' => 'إنشاء عروض', 'label_en' => 'Create Offers', 'category' => 'offers'],
                ['name' => 'edit_offers', 'label_ar' => 'تعديل العروض', 'label_en' => 'Edit Offers', 'category' => 'offers'],
                ['name' => 'delete_offers', 'label_ar' => 'حذف العروض', 'label_en' => 'Delete Offers', 'category' => 'offers'],
                ['name' => 'view_orders', 'label_ar' => 'عرض الطلبات', 'label_en' => 'View Orders', 'category' => 'orders'],
                ['name' => 'create_orders', 'label_ar' => 'إنشاء طلبات', 'label_en' => 'Create Orders', 'category' => 'orders'],
                ['name' => 'edit_orders', 'label_ar' => 'تعديل الطلبات', 'label_en' => 'Edit Orders', 'category' => 'orders'],
                ['name' => 'delete_orders', 'label_ar' => 'حذف الطلبات', 'label_en' => 'Delete Orders', 'category' => 'orders'],
                ['name' => 'manage_orders', 'label_ar' => 'إدارة الطلبات', 'label_en' => 'Manage Orders', 'category' => 'orders'],
                ['name' => 'send_to_warehouse', 'label_ar' => 'إرسال إلى المستودع', 'label_en' => 'Send to Warehouse', 'category' => 'orders'],
                ['name' => 'print_orders', 'label_ar' => 'طباعة الطلبات', 'label_en' => 'Print Orders', 'category' => 'orders'],
                ['name' => 'view_warehouses', 'label_ar' => 'عرض المستودعات', 'label_en' => 'View Warehouses', 'category' => 'warehouses'],
                ['name' => 'create_warehouses', 'label_ar' => 'إنشاء مستودعات', 'label_en' => 'Create Warehouses', 'category' => 'warehouses'],
                ['name' => 'edit_warehouses', 'label_ar' => 'تعديل المستودعات', 'label_en' => 'Edit Warehouses', 'category' => 'warehouses'],
                ['name' => 'delete_warehouses', 'label_ar' => 'حذف المستودعات', 'label_en' => 'Delete Warehouses', 'category' => 'warehouses'],
                ['name' => 'view_warehouse_orders', 'label_ar' => 'عرض طلبات المستودع', 'label_en' => 'View Warehouse Orders', 'category' => 'warehouses'],
                ['name' => 'view_staff', 'label_ar' => 'عرض الموظفين', 'label_en' => 'View Staff', 'category' => 'staff'],
                ['name' => 'create_staff', 'label_ar' => 'إنشاء موظفين', 'label_en' => 'Create Staff', 'category' => 'staff'],
                ['name' => 'manage_staff', 'label_ar' => 'إدارة الموظفين', 'label_en' => 'Manage Staff', 'category' => 'staff'],
                ['name' => 'delete_staff', 'label_ar' => 'حذف الموظفين', 'label_en' => 'Delete Staff', 'category' => 'staff'],
                ['name' => 'view_notifications', 'label_ar' => 'عرض الإشعارات', 'label_en' => 'View Notifications', 'category' => 'notifications'],
                ['name' => 'send_notifications', 'label_ar' => 'إرسال إشعارات', 'label_en' => 'Send Notifications', 'category' => 'notifications'],
                ['name' => 'view_rates', 'label_ar' => 'عرض أسعار الصرف', 'label_en' => 'View Exchange Rates', 'category' => 'rates'],
                ['name' => 'edit_rates', 'label_ar' => 'تعديل أسعار الصرف', 'label_en' => 'Edit Exchange Rates', 'category' => 'rates'],
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
                    'permissions' => ['view_dashboard', 'view_analytics', 'view_users', 'create_users', 'edit_users', 'delete_users', 'manage_user_roles', 'manage_users', 'manage_roles', 'view_products', 'create_products', 'edit_products', 'delete_products', 'manage_products', 'export_products', 'view_categories', 'create_categories', 'edit_categories', 'delete_categories', 'view_offers', 'create_offers', 'edit_offers', 'delete_offers', 'view_orders', 'create_orders', 'edit_orders', 'delete_orders', 'manage_orders', 'send_to_warehouse', 'print_orders', 'view_warehouses', 'create_warehouses', 'edit_warehouses', 'delete_warehouses', 'view_warehouse_orders', 'view_staff', 'create_staff', 'manage_staff', 'delete_staff', 'view_notifications', 'send_notifications', 'view_rates', 'edit_rates']
                ],
                [
                    'name_en' => 'manager',
                    'name_ar' => 'مدير',
                    'permissions' => ['view_dashboard', 'view_analytics', 'view_users', 'edit_users', 'view_products', 'create_products', 'edit_products', 'manage_products', 'view_categories', 'create_categories', 'edit_categories', 'view_offers', 'create_offers', 'edit_offers', 'view_orders', 'edit_orders', 'manage_orders', 'send_to_warehouse', 'view_warehouses', 'edit_warehouses', 'view_warehouse_orders', 'view_staff', 'manage_staff', 'view_notifications', 'send_notifications', 'view_rates']
                ],
                [
                    'name_en' => 'warehouse_manager',
                    'name_ar' => 'مدير المستودع',
                    'permissions' => ['view_dashboard', 'view_orders', 'edit_orders', 'manage_orders', 'view_warehouse_orders', 'view_warehouses', 'edit_warehouses']
                ],
                [
                    'name_en' => 'driver',
                    'name_ar' => 'سائق',
                    'permissions' => ['view_dashboard', 'view_orders', 'edit_orders']
                ],
                [
                    'name_en' => 'customer',
                    'name_ar' => 'عميل',
                    'permissions' => ['view_dashboard', 'view_products', 'view_offers', 'view_orders', 'create_orders', 'edit_orders']
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
