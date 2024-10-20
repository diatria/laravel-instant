<?php

namespace Diatria\LaravelInstant\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Diatria\LaravelInstant\Utils\Helper;
use Diatria\LaravelInstant\Utils\Response;
use Diatria\LaravelInstant\Utils\ErrorException;

trait InstantControllerTrait
{
    /**
     * Retrieves only one selected data
     */
    public function find(Request $request)
    {
        try {
            // Permission
            $permission = config("laravel-instant.class_permission", \Diatria\LaravelInstant\Utils\Permission::class);
            (new $permission($this->permission ?? null))->can("view");
            // Call Service Find
            $data = $this->service->find(collect($request->all())->put("id", $request->id));
            return Response::json($data, "Data berhasil diambil dengan id: {$request->id}");
        } catch (ErrorException $e) {
            return $e->getResponse();
        } catch (\Exception $e) {
            return $e;
            return Response::getResponse($e);
        }
    }

    public function all(Request $request)
    {
        try {
            // Permission
            $permission = config("laravel-instant.class_permission", \Diatria\LaravelInstant\Utils\Permission::class);
            (new $permission($this->permission ?? null))->can("view");

            // Call Service All
            $data = $this->service->all(collect($request));
            return Response::json($data, "Data berhasil diambil semua");
        } catch (ErrorException $e) {
            return Response::errorJson($e);
        } catch (\Exception $e) {
            return Response::errorJson($e);
        }
    }

    public function table(Request $request)
    {
        try {
            // Permission
            $permission = config("laravel-instant.class_permission", \Diatria\LaravelInstant\Utils\Permission::class);
            (new $permission($this->permission ?? null))->can("view");

            // Call Service Table
            $data = $this->service->table(collect($request->all()));
            return Response::json($data, "Data halaman {$data->currentPage()} dari {$data->total()} berhasil diambil");
        } catch (ErrorException $e) {
            return Response::errorJson($e);
        } catch (\Exception $e) {
            return Response::errorJson($e);
        }
    }

    public function create(Request $request)
    {
        // Permission
        $permission = config("laravel-instant.class_permission", \Diatria\LaravelInstant\Utils\Permission::class);
        (new $permission($this->permission ?? null))->can("create");

        // Call Service Store
        DB::beginTransaction();
        try {
            // Make collections
            $params = collect($request->toArray());

            $data = $this->service->store($params);

            // Set variable untuk response
            $isSaved = collect($data)->isNotEmpty();
            $modelName = Helper::getModelName($this->model);
            $message = $isSaved ? "Data {$modelName} berhasil disimpan" : "Terjadi kesalahan saat menyimpan data {$modelName}";

            DB::commit();
            return Response::json($data, $message, 201);
        } catch (ErrorException $e) {
            DB::rollBack();
            return Response::errorJson($e);
        } catch (\Exception $e) {
            DB::rollBack();
            return Response::errorJson($e);
        }
    }

    public function update(Request $request)
    {
        // Permission
        $permission = config("laravel-instant.class_permission", \Diatria\LaravelInstant\Utils\Permission::class);
        (new $permission($this->permission ?? null))->can("update");

        // Call Service Store
        DB::beginTransaction();
        try {
            // Make collections
            $params = collect($request->toArray())->put("id", $request->id);

            $data = $this->service->store($params);

            // Set variable untuk response
            $saved = collect($data)->isNotEmpty();
            $modelName = Helper::getModelName($this->model);
            $message = $saved ? "Data {$modelName} berhasil diperbaharui" : "Terjadi kesalahan saat memperbaharui data {$modelName}";

            if (!$saved) {
                throw new ErrorException("Terjadi kesalahan saat memperbaharui data {$modelName}", 500);
            }

            DB::commit();
            return Response::json($data, $message);
        } catch (ErrorException $e) {
            DB::rollBack();
            return Response::errorJson($e);
        } catch (\Exception $e) {
            DB::rollBack();
            return Response::errorJson($e);
        }
    }

    public function remove(Request $request)
    {
        // Permission
        $permission = config("laravel-instant.class_permission", \Diatria\LaravelInstant\Utils\Permission::class);
        (new $permission($this->permission ?? null))->can("delete");

        // Call Service Remove
        DB::beginTransaction();
        try {
            $removeData = $this->service->remove($request->id);
            DB::commit();
            return Response::json($removeData, __("application.deleted"));
        } catch (ErrorException $e) {
            DB::rollBack();
            return Response::errorJson($e);
        } catch (\Exception $e) {
            DB::rollBack();
            return Response::errorJson($e);
        }
    }
}
