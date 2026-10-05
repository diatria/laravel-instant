<?php
namespace Diatria\LaravelInstant\Services;

use Diatria\LaravelInstant\Models\Role;
use Diatria\LaravelInstant\Http\Responses\RoleResponse;
use Diatria\LaravelInstant\Traits\InstantServiceTrait;

class RoleService
{
    use InstantServiceTrait;

    /**
     * Class model yang digunakan
     *
     * @var Diatria\LaravelInstant\Models\Role
     */
    protected $model;

    /**
     * Path pagination
     *
     * @var string
     */
    protected $paginationPath = "/role/table";

    /**
     * List kolom yang akan ditampilkan
     *
     * @var array
     */
    protected $columns = ["name"];

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
     * @param Diatria\LaravelInstant\Models\Role $model class
     */
    public function initModel()
    {
        $this->model = new Role();
        $this->responseFormatClass = new RoleResponse();
        return $this;
    }
}
