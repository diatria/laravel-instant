<?php
namespace Diatria\LaravelInstant\Services;

use App\Models\Permission;
use Illuminate\Support\Collection;
use Diatria\LaravelInstant\Utils\Helper;
use Diatria\LaravelInstant\Utils\Response;
use Diatria\LaravelInstant\Utils\ErrorException;
use Diatria\LaravelInstant\Traits\InstantServiceTrait;
use Diatria\LaravelInstant\Http\Responses\PermissionResponse;

class PermissionService
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
     * @var Diatria\LaravelInstant\Models\Permission
     */
    protected $model;

    /**
     * Path pagination
     *
     * @var string
     */
    protected $paginationPath = "/permission/table";

    /**
     * List kolom yang akan ditampilkan
     *
     * @var array
     */
    protected $columns = ["name", "created_by", "updated_by", "deleted_by"];

    /**
     * List kolom yang required ketika akan menyimpan data
     *
     * @var array
     */
    protected $columnsRequired = ["name"];

    /**
     * Class Response untuk formating data
     */
    protected $responseFormatClass;

    /**
     * @param Diatria\LaravelInstant\Models\Permission $model class
     */
    public function initModel()
    {
        $this->model = new Permission();
        $this->responseFormatClass = new PermissionResponse();
        return $this;
    }

    public function all()
    {
        try {
            $model = $this->model->with(["application"])->get();
            return (new PermissionResponse())->array(Helper::toArray($model));
        } catch (ErrorException $e) {
            return Response::error($e->getErrorCode(), $e->getMessage());
        }
    }

    /**
     * Create new data
     * @param \Illuminate\Support\Collection $params Form Data
     * - appplication_id    required    number
     * - name               required    string
     * - id                 optional    number  'untuk melakukan edit data'
     */
    public function store(Collection $params)
    {
        try {
            $stored = $this->storeTrait($params);
            return (new PermissionResponse())->object(
                Helper::toObject($stored)
            );
        } catch (ErrorException $e) {
            return Response::error($e->getErrorCode(), $e->getMessage());
        }
    }

    public function table(Collection $params)
    {
        try {
            $tables = $this->tableTrait($params);
            return (new PermissionResponse())->table($tables);
        } catch (ErrorException $e) {
            return Response::error($e->getErrorCode(), $e->getMessage());
        }
    }
}
