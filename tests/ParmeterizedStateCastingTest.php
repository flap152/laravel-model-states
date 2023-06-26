<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Spatie\ModelStates\StateConfig;
use Spatie\ModelStates\Tests\Dummy\ModelStates\AnotherDirectory\StateF;
use Spatie\ModelStates\Tests\Dummy\ModelStates\NumericStates\StateL_11;
use Spatie\ModelStates\Tests\Dummy\ModelStates\NumericStates\StateM_155_default;
use Spatie\ModelStates\Tests\Dummy\ModelStates\NumericStates\StateN;
use Spatie\ModelStates\Tests\Dummy\ModelStates\StateA;
use Spatie\ModelStates\Tests\Dummy\TestModel;
use Spatie\ModelStates\Tests\Dummy\TestModelWithStateIdField;


it('state without alias is serialized on create', function () {
    $model = TestModel::create([
        'state' => StateA::class,
    ]);

    expect($model->state)->toBeInstanceOf(StateA::class);

    $this->assertDatabaseHas($model->getTable(), [
//        'state' => StateA::getMorphClass(),
        'state' => StateA::getMorphClass(),
    ]);
});

it('custom registered mapped state without alias is serialized on create', function () {
    $model = TestModel::create([
//        'state' => StateF_::class,
        'state' => StateF::class,
    ]);

    expect($model->state)->toBeInstanceOf(StateF::class);

    $this->assertDatabaseHas($model->getTable(), [
//        'state' => StateF_::getMorphClass(),
        'state' => StateF::getMorphClass(),
    ]);
});

it('mapped state with alias is serialized on create when using class name', function () {
    $model = \Spatie\ModelStates\Tests\Dummy\TestModelWithStateIdField::create([
//        'state' => StateC_C::class,
//        'state' => StateL_11::getStoredValue(),
        'state' => StateL_11::class,
    ]);

    expect($model->state)->toBeInstanceOf(StateL_11::class);

    $this->assertDatabaseHas($model->getTable(), [
//        'state' => StateC_C::getMorphClass(),
        'state_id' => StateL_11::getStoredValue(),
    ]);
});

it('custom registered mapped state with alias is serialized on create when using class name', function () {
    $model = TestModelWithStateIdField::create([
        'state' => StateM_155_default::class,
    ]);

    expect($model->state)->toBeInstanceOf(StateM_155_default::class);

    $this->assertDatabaseHas($model->getTable(), [
//        'state' => StateG_nameG::getMorphClass(),
        'state_id' => StateM_155_default::getStoredValue(),
    ]);
});

it('mapped state with alias is serialized on create when using alias', function () {
    $model = TestModelWithStateIdField::create([
//        'state' => StateC_C::getMorphClass(),
        'state' => StateN::getMorphClass(),
    ]);

    expect($model->state)->toBeInstanceOf(StateN::class);

    $this->assertDatabaseHas($model->getTable(), [
//        'state' => StateC_C::getMorphClass(),
        'state_id' => StateN::getStoredValue(),
    ]);
});

it('custom registered mapped state with alias is serialized on create when using alias', function () {
    $model = TestModelWithStateIdField::create([
//        'state' => StateG_nameG::getMorphClass(),
        'state' => StateN::getMorphClass(),
    ]);

    expect($model->state)->toBeInstanceOf(StateN::class);

    $this->assertDatabaseHas($model->getTable(), [
//        'state' => StateG_nameG::getMorphClass(),
        'state_id' => StateN::getStoredValue(),
    ]);
});

it('mapped state is immediately unserialized on property set', function () {
    $model = new TestModelWithStateIdField();

//    $model->state = StateA_::class;
    $model->state = StateN::class;

    expect($model->state)->toBeInstanceOf(StateN::class);
});

it('mapped state is immediately unserialized on model construction', function () {
    $model = new TestModelWithStateIdField([
//        'state' => StateN::class,
        'state' => StateN::getStoredValue(),
    ]);

    expect($model->state)->toBeInstanceOf(StateN::class);
});

it('mapped state is unserialized on fetch', function () {
    DB::table((new TestModelWithStateIdField())->getTable())->insert([
        'id' => 1,
//        'state' => StateA_::getMorphClass(),
        'state_id' => StateN::getStoredValue(),
    ]);

    $model = TestModelWithStateIdField::find(1);

    expect($model->state)->toBeInstanceOf(StateN::class);
});

it('default  is set when none provided', function () {
//    $model = (new class() extends TestModelWithExplicitNumericMapping {
//        //....
////    $model = (new class() extends Model {
////    $model = (new class() extends TestModel {
//        use \Spatie\ModelStates\HasStates;
//        public function registerStates(): void
//        {
//            $this
////                ->addState('state', ModelState::class)
//                ->addState('state', ModelStateMappedNumeric::class)
//                ->default(StateN::class);
//        }
////        public static function config(): StateConfig
////        {
////            return parent::config()
////            ->addState('state', ModelState::class)
////            ->default(StateA::class);
////        }
//        //....
////        public static function config(): StateConfig
////        {
////            return parent::config()
////            ->addState('state', ModelStateMappedNumeric::class)
////            ->default(StateL_11::class);
////        }
//    })->create();

    $model = (new \Spatie\ModelStates\Tests\Dummy\TestModelWithCustomTransition())->create();

    ray($model->state);
    expect($model->state)->toBeInstanceOf(StateL_11::class);

    $this->assertDatabaseHas($model->getTable(), [
//        'state' => StateA_::getMorphClass(),
        'state' => StateL_11::getStoredValue(),
    ]);
})->skip('Pick a default state at random really?');


it('field is always populated when set', function () {
    $model = new TestModelWithStateIdField();

    expect($model->state)->toBeInstanceOf(StateM_155_default::class);

    $model->state = new StateN($model);

    expect($model->state)->toBeInstanceOf(StateN::class);

    expect($model->state->getField())->toEqual('state_id');
});
