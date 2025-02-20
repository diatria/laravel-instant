<?php
namespace Diatria\LaravelInstant\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Diatria\LaravelInstant\Models\User;
use Diatria\LaravelInstant\Utils\Token;
use Diatria\LaravelInstant\Utils\Helper;
use Illuminate\Support\Facades\Validator;
use Diatria\LaravelInstant\Utils\Response;
use Diatria\LaravelInstant\Utils\ErrorException;
use Diatria\LaravelInstant\Traits\InstantServiceTrait;
use Diatria\LaravelInstant\Http\Responses\UserResponse;

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
    protected $columns = ["name", 'role_id', "email", "phone_number"];

    /**
     * List kolom yang required ketika akan menyimpan data
     *
     * @var array
     */
    protected $columnsRequired = ["name", "role_id", "email", "password"];

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

    public function getID()
    {
        if (Token::check()) {
            $token = Token::info();
            $user = $this->model->where("uuid", $token["uuid"])->first();
            return $user->id;
        }
    }

    public function login(array $params)
    {
        // Validator request
        $validator = Validator::make($params, $this->columnsRequired);
        if ($validator->fails()) {
            $message = $validator->errors()->first();
            throw new ErrorException($message, 500);
        }

        if (!isset($_SERVER["HTTP_ORIGIN"])) {
            $_SERVER["HTTP_ORIGIN"] = env("APP_URL");
        }

        $user = $this->model->where("email", $params["email"])->first();
        if (!$user) {
            throw new ErrorException("User not found", 404);
        }

        $isUserAuth = Hash::check($params["password"], $user->password);
        if ($user && $isUserAuth) {
            $token = Token::create([
                "user_id" => $user->id,
                "uuid" => $user->uuid ?? null,
                "email" => $user->email,
                "role_id" => $user->role_id ?? null,
            ]);

            // Set tooken cookies
            Token::setToken($token["token"]);

            return [
                ...$token,
                "user_id" => $user->id,
                "uuid" => $user->uuid ?? null,
                "email" => $user->email,
                "name" => $user->name,
                "phone_number" => $user->phone_number,
                "redirect_to" => "redirect",
            ];
        } else {
            throw new ErrorException("Wrong username or password", 401);
        }
    }

    /**
     * Melakukan refresh token dan melakukan set ulang cookies
     */
    public function refreshToken ($refreshToken) {
        return Token::refreshToken($refreshToken);
    }
}
