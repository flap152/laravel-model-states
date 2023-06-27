<?php

namespace Spatie\ModelStates\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{

    public const NUMERIC_STATE_TEST_TABLE = 'numeric_state_field_models';
    public const DEFAULT_STATE_TEST_TABLE = 'test_models';
    public const STATE_ID_TEST_TABLE = 'state_id_test_models';
    public const STATE_ID_FIELD_NAME = 'state_id';
    public const STATE_FIELD_NAME = 'state';



    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();
    }

    protected function setUpDatabase():void
    {
//        $this->createTestStateTable('test_models');
        $this->createDefaultStateTable(self::DEFAULT_STATE_TEST_TABLE);
//        $this->createNumericStateTable(self::DEFAULT_STATE_TEST_TABLE);

//        $this->createNumericStateTable(self::NUMERIC_STATE_TEST_TABLE);
        $this->createNumericStateTable(self::NUMERIC_STATE_TEST_TABLE);

        $this->createMappedStateIdTable(self::STATE_ID_TEST_TABLE);

    }

    protected function createNumericStateTable(string $tableName):void
    {
        $this->createOrReplaceStrategy($tableName);
//        $this->app->get('db')->connection()->getSchemaBuilder()->create('states', function (Blueprint $table) {
//        DB::statement("
        $this->app->get('db')->connection()->/*getSchemaBuilder()->*/statement("
CREATE TABLE $tableName (
    id INTEGER PRIMARY KEY,
    state INTEGER CHECK (state > 0),
    message linestring,
    created_at timestamp,
    updated_at timestamp,
        check (cast(cast(state AS INTEGER) AS TEXT) = state)
)
    ;");

    }

    public function createMappedStateIdTable(string $tableName): void
    {
        $this->createOrReplaceStrategy($tableName);


        $this->app->get('db')->connection()->/*getSchemaBuilder()->*/statement("
CREATE TABLE $tableName (
    id INTEGER PRIMARY KEY,
    state_id INTEGER CHECK (state_id > 0),
    message linestring,
    created_at timestamp,
    updated_at timestamp,
        check (cast(cast(state_id AS INTEGER) AS TEXT) = state_id)
)
    ;");

//        $this->app->get('db')->connection()->getSchemaBuilder()->create($tableName, function (Blueprint $table) {
//            $table->increments('id');
//            $table->integer('state_id')->nullable();
//            $table->string('message')->nullable();
//            $table->timestamps();
//        });
    }
    public function createDefaultStateTable(string $tableName): void
    {
        $this->createOrReplaceStrategy($tableName);

        $this->app->get('db')->connection()->getSchemaBuilder()->create($tableName, function (Blueprint $table) {
            $table->increments('id');
            $table->string('state')->nullable();
            $table->string('message')->nullable();
            $table->timestamps();
        });
    }

    protected function getEnvironmentSetUp($app):void
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
//            'database' => ('C:\dev\Gits\gitlibs\laravel-model-states\identifier.sqlite'),
            'prefix' => '',
        ]);
    }

    protected function createTestStateTable(string $tableName):void
    {
        $this->createOrReplaceStrategy($tableName);

        $this->app->get('db')->connection()->getSchemaBuilder()->create($tableName, function (Blueprint $table) {
            $table->increments('id');
            $table->string('state')->nullable();
            $table->string('message')->nullable();
            $table->timestamps();
        });
    }

    protected function createOrReplaceStrategy(string $tableName):void
    {
//        if (Schema::hasTable($tableName)) return;
        Schema::dropIfExists($tableName);
    }


}
