<?php

use Illuminate\Support\Facades\DB;
use Spatie\ModelStates\StateConfig;
use Spatie\ModelStates\Tests\Dummy\ModelStates\AnotherDirectory\StateF;
use Spatie\ModelStates\Tests\Dummy\ModelStates\NumericStates\StateL_11;
use Spatie\ModelStates\Tests\Dummy\ModelStates\NumericStates\StateM_155_default;
use Spatie\ModelStates\Tests\Dummy\ModelStates\NumericStates\StateN;
use Spatie\ModelStates\Tests\Dummy\ModelStates\StateA;
use Spatie\ModelStates\Tests\Dummy\TestModel;
use Spatie\ModelStates\Tests\Dummy\TestModelWithMappedNumericStateIdField;

it('state without alias is serialized on create', function () {
    $model = \Spatie\ModelStates\Tests\Dummy\TestModel::create([
        'state' => StateA::class,
    ]);

    expect($model->state)->toBeInstanceOf(StateA::class);

    $this->assertDatabaseHas($model->getTable(), [
//        'state_id' => StateA::getStoredValue(),
        'state' => StateA::getStoredValue(),
    ]);
});

it('custom registered mapped state without alias is serialized on create', function () {
    $model = TestModel::create([
//        'state' => StateF_::class,
        'state' => StateF::class,
    ]);

    expect($model->state)->toBeInstanceOf(StateF::class);

    $this->assertDatabaseHas($model->getTable(), [
        'state' => StateF::getMorphClass(),
//        'state_id' => StateF::getMorphClass(),
    ]);
});

it('mapped state with alias is serialized on create when using class name', function () {
    $model = \Spatie\ModelStates\Tests\Dummy\TestModelWithMappedNumericStateIdField::create([
        'state' => StateN::class,
    ]);

    expect($model->state)->toBeInstanceOf(StateN::class);

    $this->assertDatabaseHas($model->getTable(), [
        'state_id' => StateN::getMorphClass(),
    ]);
});

it('custom registered mapped state with alias is serialized on create when using class name', function () {
    $model = TestModelWithMappedNumericStateIdField::create([
        'state' => StateN::class,
    ]);

    expect($model->state)->toBeInstanceOf(StateN::class);

    $this->assertDatabaseHas($model->getTable(), [
//        'state' => StateG_nameG::getMorphClass(),
        'state_id' => StateN::getMorphClass(),
    ]);
});

it('mapped state with alias is serialized on create when using alias', function () {
    $model = TestModelWithMappedNumericStateIdField::create([
//        'state' => StateC_C::getMorphClass(),
        'state_id' => StateN::getMorphClass(),
    ]);

    expect($model->state)->toBeInstanceOf(StateN::class);

    $this->assertDatabaseHas($model->getTable(), [
//        'state' => StateC_C::getMorphClass(),
        'state_id' => StateN::getMorphClass(),
    ]);
});

it('custom registered mapped state with alias is serialized on create when using alias', function () {
    $model = TestModelWithMappedNumericStateIdField::create([
//        'state' => StateG_nameG::getMorphClass(),
        'state' => StateN::getMorphClass(),
    ]);

    expect($model->state)->toBeInstanceOf(StateN::class);

    $this->assertDatabaseHas($model->getTable(), [
//        'state' => StateG_nameG::getMorphClass(),
        'state_id' => StateN::getMorphClass(),
    ]);
});

it('mapped state is immediately unserialized on property set', function () {
    $model = new TestModelWithMappedNumericStateIdField();

    $model->state = StateN::class;

    expect($model->state)->toBeInstanceOf(StateN::class);
});

it('mapped state is immediately unserialized on model construction', function () {
    $model = new TestModelWithMappedNumericStateIdField([
        'state' => StateN::class,
    ]);

    expect($model->state)->toBeInstanceOf(StateN::class);
});

it('mapped state is unserialized on fetch', function () {
    DB::table((new TestModelWithMappedNumericStateIdField())->getTable())->insert([
        'id' => 1,
//        'state' => StateA_::getMorphClass(),
        'state_id' => StateN::getMorphClass(),
    ]);

    $model = TestModelWithMappedNumericStateIdField::find(1);

    expect($model->state)->toBeInstanceOf(StateN::class);
});

it('default  is set when none provided', function () {
//    $model = (new class() extends TestModelWithExplicitNumericMapping {
    $model = (new class() extends TestModelWithMappedNumericStateIdField {
        public static function config(): StateConfig
        {
            return parent::config()
            ->addState('state', \Spatie\ModelStates\Tests\Dummy\ModelStates\ModelStateMappedNumeric::class)
            ->default(StateN::class);
        }
    })->create();

    expect($model->state)->toBeInstanceOf(StateN::class);

    $this->assertDatabaseHas($model->getTable(), [
//        'state' => StateA_::getMorphClass(),
        'state' => StateN::getMorphClass(),
    ]);
})->skip('Pick a default state at random really?');

it('field is always populated when set', function () {
    $model = new TestModelWithMappedNumericStateIdField();

    expect($model->state)->toBeInstanceOf(StateM_155_default::class);

    $model->state = new StateN($model);

    expect($model->state)->toBeInstanceOf(StateN::class);

//    expect($model->state->getField())->toEqual('state');
    expect($model->state->getField())->toEqual('state_id'); // is this ok?
});
