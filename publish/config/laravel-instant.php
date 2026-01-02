<?php

return [
	'auth' => [
		'driver' => 'jwt',
		'algorithm' => env('LI_ALGORITHM'),
		'secret_key' => env('LI_SECRET_KEY'),
		'access_token_name' => env('LI_ACCESS_TOKEN_NAME'),
		'refresh_token_name' => env('LI_REFRESH_TOKEN_NAME'),
		'access_token_expires' => 86400, // 1 day
		'refresh_token_expires' => 86400 * 2,
	],
	"response" => [
		"read_class" => [
			"App\\",
			"Diatria\\"
		]
	],
	"route_prefix" => "li"
];
