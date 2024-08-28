<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\RolePermission;
use Diatria\LaravelInstant\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

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
