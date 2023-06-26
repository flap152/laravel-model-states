<?php

namespace Spatie\ModelStates\Tests;

use Illuminate\Database\Schema\Blueprint;
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

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function setUpDatabase()
    {
        $this->app->get('db')->connection()->getSchemaBuilder()->create('test_models', function (Blueprint $table) {
            $table->increments('id');
            $table->string('state')->nullable();
            $table->string('message')->nullable();
            $table->timestamps();
        });
    }
}
