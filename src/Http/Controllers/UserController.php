<?php

namespace Diatria\LaravelInstant\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Diatria\LaravelInstant\Models\User;
use Diatria\LaravelInstant\Utils\Response;
use Diatria\LaravelInstant\Services\UserService;
use Diatria\LaravelInstant\Utils\ErrorException;
use Diatria\LaravelInstant\Traits\InstantControllerTrait;

class UserController extends Controller
{
    use InstantControllerTrait;

    protected $service, $model;

    public function __construct(User $model, UserService $service)
    {
        $this->model = $model;
        $this->service = $service->initModel($model);
    }

    public function check()
    {
        try {
            $data = $this->service->check();
            return Response::json($data);
        } catch (ErrorException $e) {
            return $e->getResponse();
        }
    }

    public function login(Request $request)
    {
        try {
            $data = $this->service->login([
                "email" => $request->email,
                "password" => $request->password,
            ]);
            return Response::json($data);
        } catch (ErrorException $e) {
            return Response::errorJson($e);
        } catch (\Exception $e) {
            return Response::errorJson($e);
        }
    }

    public function register(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = $this->service->store(
                collect([
                    "uuid" => Str::uuid(),
                    "name" => $request->name,
                    "email" => $request->email,
                    "phone_number" => $request->phone_number,
                    "password" => $request->password,
                    "role_id" => $request->role_id,
                ])
            );
            DB::commit();
            return Response::json($data);
        } catch (ErrorException $e) {
            DB::rollBack();
            return Response::errorJson($e);
        } catch (\Exception $e) {
            DB::rollBack();
            return Response::errorJson($e);
        }
    }
}
