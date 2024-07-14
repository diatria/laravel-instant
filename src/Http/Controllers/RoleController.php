<?php

namespace Diatria\LaravelInstant\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Diatria\LaravelInstant\Models\Role;
use Diatria\LaravelInstant\Services\RoleService;
use Diatria\LaravelInstant\Traits\InstantControllerTrait;

class RoleController extends Controller
{
    use InstantControllerTrait;

    protected $service, $model;
    protected $permission = [
        "create" => "can_create_role",
        "view" => "can_view_role",
        "update" => "can_update_role",
        "delete" => "can_delete_role",
    ];

    public function __construct(Role $model, RoleService $service)
    {
        $this->model = $model;
        $this->service = $service->initModel();
    }
}
