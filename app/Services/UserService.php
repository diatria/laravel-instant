<?php
namespace App\Services;

use Carbon\Carbon;
use App\Utils\Token;
use App\Models\User;
use App\Utils\Response;
use App\Utils\HandleToken;
use Illuminate\Support\Str;
use App\Utils\ErrorException;
use App\Http\Response\UserResponse;
use App\Traits\InstantServiceTrait;
use Illuminate\Support\Facades\Hash;

class UserService
{
    use InstantServiceTrait;

    /**
     * Class model yang digunakan
     *
     * @var App\Models\User
     */
    protected $model;

    /**
     * Path pagination
     *
     * @var string
     */
    protected $paginationPath = "/users/table";

    /**
     * List kolom yang akan ditampilkan
     *
     * @var array
     */
    protected $columns = ["name", "email", "phone_number"];

    /**
     * List kolom yang required ketika akan menyimpan data
     *
     * @var array
     */
    protected $columnsRequired = ["name", "email", "password"];

    /**
     * Class Response untuk formating data
     */
    protected $responseFormatClass;

    /**
     * @param App\Models\User $model class
     */
    public function initModel()
    {
        $this->model = new User();
        $this->responseFormatClass = new UserResponse();
        return $this;
    }

    public function check()
    {
        return Token::check();
    }

    public function login(array $params)
    {
        try {
            $appName = env("APP_NAME");
            $user = $this->model->where("email", $params["email"])->first();
            $isUserAuth = Hash::check($params["password"], $user->password);
            if ($user && $isUserAuth) {
                $token = Token::create([
                    "email" => $user->email,
                    "role_id" => $user->role_id,
                ]);

                setcookie(
                    "{$appName}_token",
                    $token["token"],
                    Carbon::now()->addHours(6)->getTimestamp(),
                    "/",
                    "localhost",
                    false,
                    true
                );

                return [
                    ...$token,
                    "email" => $user->email,
                    "name" => $user->name,
                    "phone_number" => $user->phone_number,
                ];
            }
        } catch (ErrorException $e) {
            return Response::error($e->getErrorCode(), $e->getMessage());
        }
    }
}
