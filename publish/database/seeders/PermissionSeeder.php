<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            "can_view_permission",
            "can_create_permission",
            "can_update_permission",
            "can_delete_permission",
            "can_view_role",
            "can_create_role",
            "can_update_role",
            "can_delete_role_permission",
            "can_view_role_permission",
            "can_create_role_permission",
            "can_update_role_permission",
            "can_delete_role_permission",
            "can_view_user",
            "can_create_user",
            "can_update_user",
            "can_delete_user",
        ];

        foreach ($permissions as $permission) {
            Permission::create([
                "name" => $permission,
            ]);
        }
    }
}
