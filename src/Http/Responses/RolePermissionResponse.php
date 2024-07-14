<?php
namespace Diatria\LaravelInstant\Http\Responses;

use Diatria\LaravelInstant\Utils\ResponseFormat;

class RolePermissionResponse extends ResponseFormat
{
    public $tablePath = "roles-permissions/table";

    public function format(object $field)
    {
        $role = new RoleResponse();
        $permission = new PermissionResponse();
        return [
            "id" => $field->id,
            "role_id" => $field->role_id,
            "permission_id" => $field->permission_id,
            "role" => $role->object($field->role ?? null),
            "permission" => $permission->object($field->permission ?? null),
        ];
    }
}
