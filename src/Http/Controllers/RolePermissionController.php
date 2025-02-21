<?php

namespace Diatria\LaravelInstant\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Diatria\LaravelInstant\Utils\Response;
use Diatria\LaravelInstant\Utils\ErrorException;
use Diatria\LaravelInstant\Models\RolePermission;
use Diatria\LaravelInstant\Services\RolePermissionService;
use Diatria\LaravelInstant\Traits\InstantControllerTrait;

class RolePermissionController extends Controller
{
    use InstantControllerTrait;

    protected $service, $model;
    protected $permission = [
        "create" => "can_create_role_permission",
        "view" => "can_view_role_permission",
        "update" => "can_update_role_permission",
        "delete" => "can_delete_role_permission",
    ];

    public function __construct(
        RolePermission $model,
        RolePermissionService $service
    ) {
        $this->model = $model;
        $this->service = $service->initModel($model);
    }

    public function table(Request $request)
    {
        try {
            $data = $this->service->table(
                collect([
                    "queries" => $request->queries,
                    "relations" => ["Role", "Permission"],
                ])
            );
            return Response::json(
                $data,
                "Data halaman {$data->currentPage()} dari {$data->total()} berhasil diambil"
            );
        } catch (ErrorException $e) {
            return $e->getResponse();
        }
    }
}
