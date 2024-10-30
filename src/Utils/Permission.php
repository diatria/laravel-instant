<?php
namespace Diatria\LaravelInstant\Utils;

use App\Models\User;

class Permission
{
    protected $permission, $action;
    public function __construct($permission)
    {
        $this->permission = $permission;
    }

    public function can($action)
    {
        try {
            if (config('laravel-instant.disable_permissions')) {
                return true;
            }

            $this->action = $action;

            $tokenInfo = Token::info();
            $user = User::where("uuid", $tokenInfo["uuid"])->first();

            if (!$user) {
                throw new ErrorException("Unauthorized", 401);
            }

            $haveAccess = in_array(
                $this->getAction(),
                $user->permissions->toArray()
            );

            if (!$haveAccess) {
                throw new ErrorException("Permission denied", 403);
            }

            return $haveAccess;
        } catch (ErrorException $e) {
            throw new ErrorException($e->getMessage(), $e->getCode());
        } catch (\Exception $e) {
            throw new ErrorException($e->getMessage(), $e->getCode());
        }
    }
    public function getAction()
    {
        return Helper::get($this->permission, $this->action);
    }
}
