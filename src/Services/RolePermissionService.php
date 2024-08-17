<?php
namespace Diatria\LaravelInstant\Services;

use App\Models\RolePermission;
use Diatria\LaravelInstant\Utils\Helper;
use Diatria\LaravelInstant\Utils\Response;
use Diatria\LaravelInstant\Utils\ErrorException;
use Diatria\LaravelInstant\Traits\InstantServiceTrait;
use Diatria\LaravelInstant\Http\Responses\RolePermissionResponse;

class RolePermissionService
{
    use InstantServiceTrait {
        all as protected allTrait;
        find as protected findTrait;
        store as protected storeTrait;
        table as protected tableTrait;
    }

    /**
     * Class model yang digunakan
     *
     * @var Diatria\LaravelInstant\Models\RolePermission
     */
    protected $model;

    /**
     * Path pagination
     *
     * @var string
     */
    protected $paginationPath = "/role-permission/table";

    /**
     * List kolom yang akan ditampilkan
     *
     * @var array
     */
    protected $columns = [
        "role_id",
        "permission_id",
        "created_at",
        "updated_at",
        "deleted_at",
    ];

    /**
     * List kolom yang required ketika akan menyimpan data
     *
     * @var array
     */
    protected $columnsRequired = ["role_id", "permission_id"];

    /**
     * Class Response untuk formating data
     */
    protected $responseFormatClass;

    /**
     * @param Diatria\LaravelInstant\Models\RolePermission $model class
     */
    public function initModel()
    {
        $this->model = new RolePermission();
        $this->responseFormatClass = new RolePermissionResponse();
        return $this;
    }

    /**
     * Mengambil semua data pada table
     */
    public function all()
    {
        try {
            $model = $this->model->with(["Role", "Permission"])->get();
            return (new RolePermissionResponse())->array(
                Helper::toArray($model)
            );
        } catch (ErrorException $e) {
            return Response::error($e->getErrorCode(), $e->getMessage());
        }
    }

    /**
     * Mengambil hanya satu data terpilih
     * @param int|string $id 'id' atau 'uid'
     */
    public function find(int|string $id)
    {
        try {
            $query = $this->model
                ->where("id", $id)
                ->with(["Role", "Permission"])
                ->first();

            if (!$query) {
                throw new ErrorException(__("application.not_found"), 404);
            }

            return (new RolePermissionResponse())->object($query);
        } catch (ErrorException $e) {
            return Response::error($e->getErrorCode(), $e->getMessage());
        }
    }
}
