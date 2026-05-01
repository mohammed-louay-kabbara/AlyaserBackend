<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles
        $adminRole = Role::firstOrCreate(['name' => 'admin'], [
            'name_ar' => 'مدير النظام',
        ]);

        $managerRole = Role::firstOrCreate(['name' => 'manager'], [
            'name_ar' => 'مدير',
        ]);

        $warehouseManagerRole = Role::firstOrCreate(['name' => 'warehouse_manager'], [
            'name_ar' => 'مدير المستودع',
        ]);

        $driverRole = Role::firstOrCreate(['name' => 'driver'], [
            'name_ar' => 'سائق',
        ]);

        $customerRole = Role::firstOrCreate(['name' => 'customer'], [
            'name_ar' => 'عميل',
        ]);

        // Get all permissions
        $allPermissions = Permission::all()->pluck('id')->toArray();

        // Admin gets all permissions
        $adminRole->permissions()->sync($allPermissions);

        // Manager permissions (all except some admin-specific ones)
        $managerPermissions = Permission::whereIn('name', [
            'view_dashboard', 'view_analytics',
            'view_users', 'create_users', 'edit_users', 'manage_user_roles',
            'view_products', 'create_products', 'edit_products', 'delete_products',
            'view_categories', 'create_categories', 'edit_categories', 'delete_categories',
            'view_offers', 'create_offers', 'edit_offers', 'delete_offers',
            'view_orders', 'create_orders', 'edit_orders', 'delete_orders', 'manage_orders', 'send_to_warehouse',
            'view_warehouses', 'create_warehouses', 'edit_warehouses', 'delete_warehouses',
            'view_notifications', 'send_notifications',
            'view_rates', 'edit_rates'
        ])->pluck('id')->toArray();
        $managerRole->permissions()->sync($managerPermissions);

        // Warehouse Manager permissions
        $warehousePermissions = Permission::whereIn('name', [
            'view_dashboard', 'view_analytics',
            'view_products', 'edit_products',
            'view_categories',
            'view_offers',
            'view_orders', 'edit_orders', 'manage_orders',
            'view_warehouses', 'edit_warehouses', 'view_warehouse_orders',
            'view_notifications',
            'view_rates'
        ])->pluck('id')->toArray();
        $warehouseManagerRole->permissions()->sync($warehousePermissions);

        // Driver permissions
        $driverPermissions = Permission::whereIn('name', [
            'view_dashboard',
            'view_orders', 'edit_orders',
            'view_notifications'
        ])->pluck('id')->toArray();
        $driverRole->permissions()->sync($driverPermissions);

        // Customer permissions (limited)
        $customerPermissions = Permission::whereIn('name', [
            'view_products',
            'view_categories',
            'view_offers',
            'view_orders',
            'view_notifications'
        ])->pluck('id')->toArray();
        $customerRole->permissions()->sync($customerPermissions);

        $this->command->info('Roles and permissions seeded successfully!');
    }
}
