<?php

namespace Diatria\LaravelInstant\Utils;

use Illuminate\Pagination\LengthAwarePaginator;

class TableMaker
{
    /**
     * Melakukan pembuatan ulang table dengan merubah table items
     */
    public function reCreate(
        object $table,
        array $tableItems,
        array $options = []
    ) {
        $pageOptions = [
            "path" => env("APP_URL") . "/api/" . Helper::get($options, "path"),
            "pageName" => "page",
        ];

        return new LengthAwarePaginator(
            $tableItems,
            $table->total(),
            $table->perPage(),
            $table->currentPage(),
            $pageOptions
        );
    }
}
