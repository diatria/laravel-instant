<?php
namespace Diatria\LaravelInstant\Http\Responses;

use Diatria\LaravelInstant\Utils\ResponseFormat;

class UserResponse extends ResponseFormat
{
    public $tablePath = "users/table";

    public function format(object $field)
    {
        return [
            "id" => $field->id,
            "role_id" => $field->role_id ?? null,
            "name" => $field->name,
            "email" => $field->email,
            "phone_number" => $field->phone_number,
        ];
    }
}
