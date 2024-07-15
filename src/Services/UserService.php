<?php
namespace Diatria\LaravelInstant\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Diatria\LaravelInstant\Utils\Token;
use Diatria\LaravelInstant\Models\User;
use Diatria\LaravelInstant\Utils\Response;
use Diatria\LaravelInstant\Utils\ErrorException;
use Diatria\LaravelInstant\Traits\InstantServiceTrait;
use Diatria\LaravelInstant\Http\Responses\UserResponse;
use Diatria\LaravelInstant\Utils\Helper;

class UserService
{
    use InstantServiceTrait;

    /**
     * Class model yang digunakan
     *
     * @var Diatria\LaravelInstant\Models\User
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
     * @param Diatria\LaravelInstant\Models\User $model class
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
            $user = $this->model->where("email", $params["email"])->first();
            $isUserAuth = Hash::check($params["password"], $user->password);
            if ($user && $isUserAuth) {
                $token = Token::create([
                    "email" => $user->email,
                    "role_id" => $user->role_id ?? null,
                ]);

                setcookie(
                    "token_" . strtolower(env("APP_NAME")),
                    $token["token"],
                    Carbon::now()->addHours(6)->getTimestamp(),
                    "/",
                    Helper::getHost(),
                    false,
                    true
                );

                return [
                    ...$token,
                    "email" => $user->email,
                    "name" => $user->name,
                    "phone_number" => $user->phone_number,
                    "redirect_to" => "oauth",
                ];
            } else {
                throw new ErrorException("Wrong username or password", 401);
            }
        } catch (ErrorException $e) {
            return Response::error($e->getErrorCode(), $e->getMessage());
        }
    }
}
