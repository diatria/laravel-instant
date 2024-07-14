<?php

namespace Diatria\LaravelInstant\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Diatria\LaravelInstant\Models\Permission;
use Diatria\LaravelInstant\Services\PermissionService;
use Diatria\LaravelInstant\Traits\InstantControllerTrait;

class PermissionController extends Controller
{
    use InstantControllerTrait;

    protected $service, $model;
    protected $permission = [
        "create" => "can_create_permission",
        "view" => "can_view_permission",
        "update" => "can_update_permission",
        "delete" => "can_delete_permission",
    ];

    public function __construct(Permission $model, PermissionService $service)
    {
        $this->model = $model;
        $this->service = $service->initModel($model);
    }
}
