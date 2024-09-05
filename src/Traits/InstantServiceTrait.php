<?php

namespace Diatria\LaravelInstant\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Diatria\LaravelInstant\Utils\Helper;
use Illuminate\Support\Facades\Validator;
use Diatria\LaravelInstant\Utils\QueryMaker;
use Diatria\LaravelInstant\Utils\GeneralConfig;
use Illuminate\Pagination\LengthAwarePaginator;
use Diatria\LaravelInstant\Utils\ErrorException;

trait InstantServiceTrait
{
    protected $responseFormatClass;

    /**
     * Retrieve all data
     */
    public function all()
    {
        try {
            $query = $this->model->query();
            if (Helper::hasUserID($this->model)) {
                $query = $this->model->where("user_id", Helper::getUserID());
                if (!empty($this->columns)) {
                    $query = $query->select(["id", ...$this->columns]);
                }
            }

            if ($this->responseFormatClass) {
                return $this->responseFormatClass->array(
                    $query->get()->toArray()
                );
            }

            return $query->get();
        } catch (ErrorException $e) {
            throw new ErrorException($e->getMessage(), $e->getCode());
        } catch (\Exception $e) {
            throw new ErrorException($e->getMessage(), $e->getCode());
        } catch (\PDOException $e) {
            throw new ErrorException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Retrieves only one selected data
     * @param int $id "id" column of the database
     * @param \Illuminate\Http\Request $request parameters to create a query, permitted columns:
     * - relations  | optional  | array
     */
    public function find(Request $request)
    {
        try {
            $params = collect($request->all());
            $params->only(["relations"]);

            // search for data based on the "id" field
            $params->put("queries", [
                ["field" => "id", "value" => $request->id, "strict" => true],
            ]);

            // displays data along with relationships
            $params->put(
                "relations",
                $params->get("relations", $this->responseFormatRelations ?? [])
            );
            $params->put("mode", "first"); // retrieves only one data

            // create queries and retrieve data
            $query = $this->query($params);

            // perform data formatting
            if ($this->responseFormatClass) {
                $response = $this->responseFormatClass;
                $response->with(
                    $params->get("relations", $this->responseFormatRelations ?? [])
                );
                return $response->object($query);
            }

            return $query;
        } catch (ErrorException $e) {
            throw new ErrorException($e->getMessage(), $e->getCode());
        } catch (\Exception $e) {
            throw new ErrorException($e->getMessage(), $e->getCode());
        } catch (\PDOException $e) {
            throw new ErrorException($e->getMessage(), $e->getCode());
        }
    }

    public function getTable()
    {
        return $this->model->getTable();
    }

    /**
     * @param Collection $request queries | array
     * @param Collection $request columns | array
     * @param Collection $request limit | int
     * @param Collection $request mode | 'first' or 'get'
     * @return Collection \App\Models\User
     */
    public function query(Collection $request)
    {
        try {
            $query = (new QueryMaker())->initial(
                collect([
                    "model" => $this->model,
                    "queries" => $request->get("queries"),
                    "columns" => Helper::get(
                        $request,
                        "columns",
                        $this->columns
                    ),
                    "limit" => $request->get("limit"),
                    "order" => $request->get("order"),
                    "pagination" => $request->get("pagination"),
                    "mode" => $request->get("mode"),
                    "authentication" => $request->get("authentication"),
                ])
            );

            if ($request->get("relations")) {
                $query = $query->setRelations($request->get("relations"));
            }
            return $query->create();
        } catch (ErrorException $e) {
            throw new ErrorException($e->getMessage(), $e->getCode());
        } catch (\Exception $e) {
            throw new ErrorException($e->getMessage(), $e->getCode());
        } catch (\PDOException $e) {
            throw new ErrorException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Remove data from database
     * @param int|array $id
     */
    public function remove(int|array $id): void
    {
        try {
            if (is_array($id)) {
                $this->model->destroy($id);
            } else {
                $data = $this->model->find($id);
                if ($data) {
                    $data->delete();
                } else {
                    throw new ErrorException("Data not found!", 404);
                }
            }
        } catch (ErrorException $e) {
            throw new ErrorException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @param \Illuminate\Support\Collection $params => array, http_request
     */
    public function store(Collection $params): Collection
    {
        try {
            $data = [];
            // Validator request
            $validator = Validator::make(
                $params->all(),
                $this->columnsRequired
            );
            if ($validator->fails()) {
                $message = $validator->errors()->first();
                throw new ErrorException($message, 500);
            }

            // Filter field only specific by fillable model
            $params = $params->only(["id", ...$this->model->getFillable()]);

            // auto append if field contains `user_id`
            $params = Helper::appendUserID($this->model, $params)->toArray();

            $haveID = Helper::get($params, "id", null);
            if ($haveID) {
                // Action Update
                $updated = $this->model->where("id", $haveID)->update($params);
                if ($updated) {
                    $data = $this->model->find($haveID);
                }
            } else {
                // Action Create
                $data = $this->model->create($params);
            }

            if ($this->responseFormatClass) {
                return collect($this->responseFormatClass->object($data));
            }

            return collect($data);
        } catch (\Exception $e) {
            throw new ErrorException($e->getMessage(), $e->getCode());
        } catch (ErrorException $e) {
            throw new ErrorException($e->getMessage(), $e->getCode());
        } catch (\PDOException $e) {
            throw new ErrorException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * $params
     * - queries            optional
     * - columns            optional
     * - pagination         optional
     * - pagination_length  optional
     * - pagination_path    optional
     * - relations          optional    array
     * - mode               optional    default:get
     */
    public function table(Collection $params)
    {
        try {
            $paginate = $params->get(
                "pagination_length",
                GeneralConfig::PAGINATE_PER_PAGE
            );
            $paginationPath =
                $this->paginationPath ?? $params->get("pagination_path");
            $pageOptions = [
                "path" => env("APP_URL") . $paginationPath,
                "pageName" => "page",
            ];

            $query = (new QueryMaker())
                ->initial(
                    collect([
                        "model" => $this->model,
                        "queries" => $params->get("queries"),
                        "columns" => Helper::get(
                            $params,
                            "columns",
                            $this->columns
                        ),
                        "limit" => $params->get("limit"),
                        "order" => $params->get("order"),
                        "pagination" => true,
                        "mode" => "get",
                        "authentication" => $params->get("authentication"),
                    ])
                )
                ->setRelations($params->get("relations"))
                ->setPagination($paginate)
                ->create();

            // Data dari database yang diubah ke Array
            $userCollection = Helper::arrayOnly(
                $query->items(),
                $params->get("column")
            );

            // Membuat ulang pagination
            $paginator = new LengthAwarePaginator(
                $userCollection,
                $query->total(),
                $query->perPage(),
                $query->currentPage(),
                $pageOptions
            );

            if ($this->responseFormatClass) {
                $response = $this->responseFormatClass;
                if ($params->get("relations")) {
                    $response->with($params->get("relations"));
                }
                return $response->table($paginator);
            }

            return $paginator;
        } catch (ErrorException $e) {
            throw new ErrorException($e->getMessage(), $e->getCode());
        } catch (\Exception $e) {
            throw new ErrorException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @param Collection $request query | model_pagination(items, total, perPage, currentPage)
     */
    // [DEPRECATED]
    public function tableCreate(Collection $request)
    {
        try {
            $queryService = $request->get("query");
            $paginationPath =
                $this->paginationPath ?? $request->get("pagination_path");
            $pageOptions = [
                "path" => env("APP_URL") . $paginationPath,
                "pageName" => "page",
            ];

            $userCollection = Helper::arrayOnly(
                $queryService->items(),
                $request->get("column")
            );
            $paginator = new LengthAwarePaginator(
                $userCollection,
                $queryService->total(),
                $queryService->perPage(),
                $queryService->currentPage(),
                $pageOptions
            );
            return Helper::toArrayCollection($paginator);
        } catch (ErrorException $e) {
            throw new ErrorException($e->getMessage(), $e->getCode());
        }
    }
}
