<?php

namespace Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Diatria\LaravelInstant\Utils\QueryMaker;
use Diatria\LaravelInstant\Utils\ErrorException;

class QueryMakerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        $this->app['db']->connection()->getSchemaBuilder()->create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('status')->nullable();
            $table->unsignedInteger('price')->nullable();
            $table->timestamps();
        });

        DB::table('products')->insert([
            ['name' => 'Phone', 'status' => 'active', 'price' => 100, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Laptop', 'status' => 'active', 'price' => 200, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Cable', 'status' => null, 'price' => 10, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function testUnknownQueryFieldIsRejected(): void
    {
        $this->expectException(ErrorException::class);
        $this->expectExceptionCode(422);

        (new QueryMaker())->initial(collect([
            'model' => new class extends Model {
                protected $table = 'products';
                protected $fillable = ['name', 'status', 'price'];
            },
            'queries' => [['field' => 'password_hash', 'value' => 'x']],
            'mode' => 'get',
        ]))->create();
    }

    public function testInvalidOrderDirectionIsRejected(): void
    {
        $this->expectException(ErrorException::class);
        $this->expectExceptionCode(422);

        (new QueryMaker())->initial(collect([
            'model' => new class extends Model {
                protected $table = 'products';
                protected $fillable = ['name', 'status', 'price'];
            },
            'order' => 'name:drop table',
            'mode' => 'get',
        ]))->create();
    }

    public function testComparisonAndRangeOperatorsWork(): void
    {
        $model = new class extends Model {
            protected $table = 'products';
            protected $fillable = ['name', 'status', 'price'];
        };

        $results = (new QueryMaker())->initial(collect([
            'model' => $model,
            'queries' => [['field' => 'price', 'op' => 'gte', 'value' => 100]],
            'mode' => 'get',
        ]))->create();

        $this->assertCount(2, $results);

        $results = (new QueryMaker())->initial(collect([
            'model' => $model,
            'queries' => [['field' => 'price', 'op' => 'between', 'value' => [10, 100]]],
            'mode' => 'get',
        ]))->create();

        $this->assertCount(2, $results);
    }

    public function testListAndNullOperatorsWork(): void
    {
        $model = new class extends Model {
            protected $table = 'products';
            protected $fillable = ['name', 'status', 'price'];
        };

        $results = (new QueryMaker())->initial(collect([
            'model' => $model,
            'queries' => [['field' => 'name', 'op' => 'in', 'value' => ['Phone', 'Cable']]],
            'mode' => 'get',
        ]))->create();

        $this->assertCount(2, $results);

        $results = (new QueryMaker())->initial(collect([
            'model' => $model,
            'queries' => [['field' => 'status', 'op' => 'null']],
            'mode' => 'get',
        ]))->create();

        $this->assertCount(1, $results);
    }

    public function testUnsupportedOperatorIsRejected(): void
    {
        $this->expectException(ErrorException::class);
        $this->expectExceptionCode(422);

        (new QueryMaker())->initial(collect([
            'model' => new class extends Model {
                protected $table = 'products';
                protected $fillable = ['name', 'status', 'price'];
            },
            'queries' => [['field' => 'price', 'op' => 'regex', 'value' => 'x']],
            'mode' => 'get',
        ]))->create();
    }
}
