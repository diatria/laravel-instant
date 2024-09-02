<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use App\Models\RolePermission;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissionID = Permission::query()->pluck("id")->toArray();
        $role = Role::where('name', 'Admin')->first();
        foreach ($permissionID as $id) {
            RolePermission::create([
                "role_id" => $role->id,
                "permission_id" => $id,
            ]);
        }
    }
}
