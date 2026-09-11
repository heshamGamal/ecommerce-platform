<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RbacSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Dashboard
            ['name' => 'View Dashboard', 'slug' => 'dashboard.view', 'group' => 'dashboard'],

            // Products
            ['name' => 'View Products', 'slug' => 'products.view', 'group' => 'products'],
            ['name' => 'Create Products', 'slug' => 'products.create', 'group' => 'products'],
            ['name' => 'Update Products', 'slug' => 'products.update', 'group' => 'products'],
            ['name' => 'Delete Products', 'slug' => 'products.delete', 'group' => 'products'],

            // Categories
            ['name' => 'View Categories', 'slug' => 'categories.view', 'group' => 'catalog'],
            ['name' => 'Create Categories', 'slug' => 'categories.create', 'group' => 'catalog'],
            ['name' => 'Update Categories', 'slug' => 'categories.update', 'group' => 'catalog'],
            ['name' => 'Delete Categories', 'slug' => 'categories.delete', 'group' => 'catalog'],

            // Brands
            ['name' => 'View Brands', 'slug' => 'brands.view', 'group' => 'catalog'],
            ['name' => 'Create Brands', 'slug' => 'brands.create', 'group' => 'catalog'],
            ['name' => 'Update Brands', 'slug' => 'brands.update', 'group' => 'catalog'],
            ['name' => 'Delete Brands', 'slug' => 'brands.delete', 'group' => 'catalog'],

            // Inventory
            ['name' => 'View Inventory', 'slug' => 'inventory.view', 'group' => 'inventory'],
            ['name' => 'Adjust Inventory', 'slug' => 'inventory.adjust', 'group' => 'inventory'],
            ['name' => 'Transfer Inventory', 'slug' => 'inventory.transfer', 'group' => 'inventory'],

            // Orders
            ['name' => 'View Orders', 'slug' => 'orders.view', 'group' => 'orders'],
            ['name' => 'Verify Orders', 'slug' => 'orders.verify', 'group' => 'orders'],
            ['name' => 'Confirm Orders', 'slug' => 'orders.confirm', 'group' => 'orders'],
            ['name' => 'Edit Orders', 'slug' => 'orders.edit', 'group' => 'orders'],
            ['name' => 'Cancel Orders', 'slug' => 'orders.cancel', 'group' => 'orders'],
            ['name' => 'Cancel Orders After Shipping', 'slug' => 'orders.cancel_after_shipping', 'group' => 'orders'],
            ['name' => 'Refund Orders', 'slug' => 'orders.refund', 'group' => 'orders'],
            ['name' => 'Return Orders', 'slug' => 'orders.return', 'group' => 'orders'],

            // Customers
            ['name' => 'View Customers', 'slug' => 'customers.view', 'group' => 'customers'],
            ['name' => 'Create Customers', 'slug' => 'customers.create', 'group' => 'customers'],
            ['name' => 'Update Customers', 'slug' => 'customers.update', 'group' => 'customers'],
            ['name' => 'View Own Customer Profile', 'slug' => 'customer.profile.view', 'group' => 'customer-profile'],
            ['name' => 'Update Own Customer Profile', 'slug' => 'customer.profile.update', 'group' => 'customer-profile'],

            // Assistants
            ['name' => 'View Assistants', 'slug' => 'assistants.view', 'group' => 'assistants'],
            ['name' => 'Create Assistants', 'slug' => 'assistants.create', 'group' => 'assistants'],
            ['name' => 'Update Assistants', 'slug' => 'assistants.update', 'group' => 'assistants'],
            ['name' => 'Delete Assistants', 'slug' => 'assistants.delete', 'group' => 'assistants'],

            // Roles & Permissions
            ['name' => 'View Roles', 'slug' => 'roles.view', 'group' => 'authorization'],
            ['name' => 'Create Roles', 'slug' => 'roles.create', 'group' => 'authorization'],
            ['name' => 'Update Roles', 'slug' => 'roles.update', 'group' => 'authorization'],
            ['name' => 'Delete Roles', 'slug' => 'roles.delete', 'group' => 'authorization'],
            ['name' => 'Manage Permissions', 'slug' => 'permissions.manage', 'group' => 'authorization'],

            // Payments
            ['name' => 'View Payments', 'slug' => 'payments.view', 'group' => 'payments'],
            ['name' => 'Manage Payments', 'slug' => 'payments.manage', 'group' => 'payments'],
            ['name' => 'Refund Payments', 'slug' => 'payments.refund', 'group' => 'payments'],

            // Shipping
            ['name' => 'View Shipping', 'slug' => 'shipping.view', 'group' => 'shipping'],
            ['name' => 'Manage Shipping', 'slug' => 'shipping.manage', 'group' => 'shipping'],

            // Discounts
            ['name' => 'View Discounts', 'slug' => 'discounts.view', 'group' => 'discounts'],
            ['name' => 'Create Discounts', 'slug' => 'discounts.create', 'group' => 'discounts'],
            ['name' => 'Update Discounts', 'slug' => 'discounts.update', 'group' => 'discounts'],
            ['name' => 'Delete Discounts', 'slug' => 'discounts.delete', 'group' => 'discounts'],

            // Affiliates
            ['name' => 'View Affiliates', 'slug' => 'affiliates.view', 'group' => 'affiliates'],
            ['name' => 'Manage Affiliates', 'slug' => 'affiliates.manage', 'group' => 'affiliates'],
            ['name' => 'Manage Affiliate Payouts', 'slug' => 'affiliates.payouts', 'group' => 'affiliates'],

            // Reviews
            ['name' => 'View Reviews', 'slug' => 'reviews.view', 'group' => 'reviews'],
            ['name' => 'Manage Reviews', 'slug' => 'reviews.manage', 'group' => 'reviews'],

            // Wishlist
            ['name' => 'View Wishlist', 'slug' => 'wishlist.view', 'group' => 'wishlist'],

            // CMS
            ['name' => 'View CMS', 'slug' => 'cms.view', 'group' => 'cms'],
            ['name' => 'Manage CMS', 'slug' => 'cms.manage', 'group' => 'cms'],

            // SEO
            ['name' => 'View SEO', 'slug' => 'seo.view', 'group' => 'seo'],
            ['name' => 'Manage SEO', 'slug' => 'seo.manage', 'group' => 'seo'],

            // Settings
            ['name' => 'View Settings', 'slug' => 'settings.view', 'group' => 'settings'],
            ['name' => 'Update Settings', 'slug' => 'settings.update', 'group' => 'settings'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['slug' => $permission['slug']],
                $permission
            );
        }

        $allPermissions = Permission::query()->get();
        $customerProfilePermissions = $allPermissions->whereIn('slug', [
            'customer.profile.view',
            'customer.profile.update',
        ]);

        $roles = [
            'admin' => [
                'name' => 'Administrator',
                'description' => 'System administrator with full access.',
                'is_system' => true,
                'permissions' => $allPermissions,
            ],

            'owner' => [
                'name' => 'Owner',
                'description' => 'Store owner with full store management access.',
                'is_system' => true,
                'permissions' => $allPermissions,
            ],

            'assistant' => [
                'name' => 'Assistant',
                'description' => 'Store assistant with permissions assigned by the owner.',
                'is_system' => true,
                'permissions' => [],
            ],

            'customer' => [
                'name' => 'Customer',
                'description' => 'Store customer account.',
                'is_system' => true,
                'permissions' => $customerProfilePermissions,
            ],
        ];

        foreach ($roles as $slug => $roleData) {
            $role = Role::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $roleData['name'],
                    'description' => $roleData['description'],
                    'is_system' => $roleData['is_system'],
                ]
            );

            $role->permissions()->sync(
                collect($roleData['permissions'])
                    ->pluck('id')
                    ->all()
            );
        }
    }
}
