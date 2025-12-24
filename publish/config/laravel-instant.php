<?php

return [
	"app" => [
		'name' => env("APP_URL"),
		'secret_key' => env('LI_SECRET_KEY')
	],
	"response" => [
		"read_class" => [
			"App\\",
			"Diatria\\"
		]
	],
	"route_prefix" => "",
	"cookies" => [
		'name' => env("LI_COOKIE_NAME"),
		"expires" => 86400, // 1 Hari
		"path" => "/",
		"secure" => true,
		"httponly" => true,
		"samesite" => "none",
		"domain" => env("COOKIES_DOMAIN")
	],
];
