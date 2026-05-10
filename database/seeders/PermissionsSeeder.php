<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Dashboard permissions
        Permission::firstOrCreate(['name' => 'view_dashboard'], [
            'label_ar' => 'عرض لوحة التحكم',
            'label_en' => 'View Dashboard',
            'category' => 'dashboard'
        ]);

        Permission::firstOrCreate(['name' => 'view_analytics'], [
            'label_ar' => 'عرض التحليلات',
            'label_en' => 'View Analytics',
            'category' => 'dashboard'
        ]);

        // Users permissions
        Permission::firstOrCreate(['name' => 'view_users'], [
            'label_ar' => 'عرض المستخدمين',
            'label_en' => 'View Users',
            'category' => 'users'
        ]);

        Permission::firstOrCreate(['name' => 'create_users'], [
            'label_ar' => 'إنشاء مستخدمين',
            'label_en' => 'Create Users',
            'category' => 'users'
        ]);

        Permission::firstOrCreate(['name' => 'edit_users'], [
            'label_ar' => 'تعديل المستخدمين',
            'label_en' => 'Edit Users',
            'category' => 'users'
        ]);

        Permission::firstOrCreate(['name' => 'delete_users'], [
            'label_ar' => 'حذف المستخدمين',
            'label_en' => 'Delete Users',
            'category' => 'users'
        ]);

        Permission::firstOrCreate(['name' => 'manage_user_roles'], [
            'label_ar' => 'إدارة أدوار المستخدمين',
            'label_en' => 'Manage User Roles',
            'category' => 'users'
        ]);

        Permission::firstOrCreate(['name' => 'manage_users'], [
            'label_ar' => 'إدارة المستخدمين',
            'label_en' => 'Manage Users',
            'category' => 'users'
        ]);

        Permission::firstOrCreate(['name' => 'manage_roles'], [
            'label_ar' => 'إدارة الأدوار',
            'label_en' => 'Manage Roles',
            'category' => 'users'
        ]);

        // Products permissions
        Permission::firstOrCreate(['name' => 'view_products'], [
            'label_ar' => 'عرض المنتجات',
            'label_en' => 'View Products',
            'category' => 'products'
        ]);

        Permission::firstOrCreate(['name' => 'create_products'], [
            'label_ar' => 'إنشاء منتجات',
            'label_en' => 'Create Products',
            'category' => 'products'
        ]);

        Permission::firstOrCreate(['name' => 'edit_products'], [
            'label_ar' => 'تعديل المنتجات',
            'label_en' => 'Edit Products',
            'category' => 'products'
        ]);

        Permission::firstOrCreate(['name' => 'delete_products'], [
            'label_ar' => 'حذف المنتجات',
            'label_en' => 'Delete Products',
            'category' => 'products'
        ]);

        Permission::firstOrCreate(['name' => 'manage_products'], [
            'label_ar' => 'إدارة المنتجات',
            'label_en' => 'Manage Products',
            'category' => 'products'
        ]);

        Permission::firstOrCreate(['name' => 'export_products'], [
            'label_ar' => 'تصدير المنتجات',
            'label_en' => 'Export Products',
            'category' => 'products'
        ]);

        // Categories permissions
        Permission::firstOrCreate(['name' => 'view_categories'], [
            'label_ar' => 'عرض الأصناف',
            'label_en' => 'View Categories',
            'category' => 'categories'
        ]);

        Permission::firstOrCreate(['name' => 'create_categories'], [
            'label_ar' => 'إنشاء أصناف',
            'label_en' => 'Create Categories',
            'category' => 'categories'
        ]);

        Permission::firstOrCreate(['name' => 'edit_categories'], [
            'label_ar' => 'تعديل الأصناف',
            'label_en' => 'Edit Categories',
            'category' => 'categories'
        ]);

        Permission::firstOrCreate(['name' => 'delete_categories'], [
            'label_ar' => 'حذف الأصناف',
            'label_en' => 'Delete Categories',
            'category' => 'categories'
        ]);

        // Offers permissions
        Permission::firstOrCreate(['name' => 'view_offers'], [
            'label_ar' => 'عرض العروض',
            'label_en' => 'View Offers',
            'category' => 'offers'
        ]);

        Permission::firstOrCreate(['name' => 'create_offers'], [
            'label_ar' => 'إنشاء عروض',
            'label_en' => 'Create Offers',
            'category' => 'offers'
        ]);

        Permission::firstOrCreate(['name' => 'edit_offers'], [
            'label_ar' => 'تعديل العروض',
            'label_en' => 'Edit Offers',
            'category' => 'offers'
        ]);

        Permission::firstOrCreate(['name' => 'delete_offers'], [
            'label_ar' => 'حذف العروض',
            'label_en' => 'Delete Offers',
            'category' => 'offers'
        ]);

        // Orders permissions
        Permission::firstOrCreate(['name' => 'view_orders'], [
            'label_ar' => 'عرض الطلبات',
            'label_en' => 'View Orders',
            'category' => 'orders'
        ]);

        Permission::firstOrCreate(['name' => 'create_orders'], [
            'label_ar' => 'إنشاء طلبات',
            'label_en' => 'Create Orders',
            'category' => 'orders'
        ]);

        Permission::firstOrCreate(['name' => 'edit_orders'], [
            'label_ar' => 'تعديل الطلبات',
            'label_en' => 'Edit Orders',
            'category' => 'orders'
        ]);

        Permission::firstOrCreate(['name' => 'delete_orders'], [
            'label_ar' => 'حذف الطلبات',
            'label_en' => 'Delete Orders',
            'category' => 'orders'
        ]);

        Permission::firstOrCreate(['name' => 'manage_orders'], [
            'label_ar' => 'إدارة الطلبات',
            'label_en' => 'Manage Orders',
            'category' => 'orders'
        ]);

        Permission::firstOrCreate(['name' => 'send_to_warehouse'], [
            'label_ar' => 'إرسال إلى المستودع',
            'label_en' => 'Send to Warehouse',
            'category' => 'orders'
        ]);

        Permission::firstOrCreate(['name' => 'print_orders'], [
            'label_ar' => 'طباعة الطلبات',
            'label_en' => 'Print Orders',
            'category' => 'orders'
        ]);

        // Warehouses permissions
        Permission::firstOrCreate(['name' => 'view_warehouses'], [
            'label_ar' => 'عرض المستودعات',
            'label_en' => 'View Warehouses',
            'category' => 'warehouses'
        ]);

        Permission::firstOrCreate(['name' => 'create_warehouses'], [
            'label_ar' => 'إنشاء مستودعات',
            'label_en' => 'Create Warehouses',
            'category' => 'warehouses'
        ]);

        Permission::firstOrCreate(['name' => 'edit_warehouses'], [
            'label_ar' => 'تعديل المستودعات',
            'label_en' => 'Edit Warehouses',
            'category' => 'warehouses'
        ]);

        Permission::firstOrCreate(['name' => 'delete_warehouses'], [
            'label_ar' => 'حذف المستودعات',
            'label_en' => 'Delete Warehouses',
            'category' => 'warehouses'
        ]);

        Permission::firstOrCreate(['name' => 'view_warehouse_orders'], [
            'label_ar' => 'عرض طلبات المستودع',
            'label_en' => 'View Warehouse Orders',
            'category' => 'warehouses'
        ]);

        // Notifications permissions
        Permission::firstOrCreate(['name' => 'view_notifications'], [
            'label_ar' => 'عرض الإشعارات',
            'label_en' => 'View Notifications',
            'category' => 'notifications'
        ]);

        Permission::firstOrCreate(['name' => 'send_notifications'], [
            'label_ar' => 'إرسال إشعارات',
            'label_en' => 'Send Notifications',
            'category' => 'notifications'
        ]);

        // Staff permissions
        Permission::firstOrCreate(['name' => 'view_staff'], [
            'label_ar' => 'عرض الموظفين',
            'label_en' => 'View Staff',
            'category' => 'staff'
        ]);

        Permission::firstOrCreate(['name' => 'create_staff'], [
            'label_ar' => 'إنشاء موظفين',
            'label_en' => 'Create Staff',
            'category' => 'staff'
        ]);

        Permission::firstOrCreate(['name' => 'manage_staff'], [
            'label_ar' => 'إدارة الموظفين',
            'label_en' => 'Manage Staff',
            'category' => 'staff'
        ]);

        Permission::firstOrCreate(['name' => 'delete_staff'], [
            'label_ar' => 'حذف الموظفين',
            'label_en' => 'Delete Staff',
            'category' => 'staff'
        ]);

        // Exchange Rates permissions
        Permission::firstOrCreate(['name' => 'view_rates'], [
            'label_ar' => 'عرض أسعار الصرف',
            'label_en' => 'View Exchange Rates',
            'category' => 'rates'
        ]);

        Permission::firstOrCreate(['name' => 'edit_rates'], [
            'label_ar' => 'تعديل أسعار الصرف',
            'label_en' => 'Edit Exchange Rates',
            'category' => 'rates'
        ]);

        $this->command->info('Permissions seeded successfully!');
    }
}
