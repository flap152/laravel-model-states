<?php

use Illuminate\Support\Facades\Event;
use Spatie\ModelStates\Tests\Dummy\ModelStates\ModelState;
use Spatie\ModelStates\Tests\Dummy\ModelStates\StateA;
use Spatie\ModelStates\Tests\Dummy\ModelStates\StateB;
use Spatie\ModelStates\Tests\Dummy\ModelStates\StateC;
use Spatie\ModelStates\Tests\Dummy\ModelStates\StateD;
use Spatie\ModelStates\Tests\Dummy\ModelStates\StateE;
use Spatie\ModelStates\Tests\Dummy\ModelStates\AnotherDirectory\StateF;
use Spatie\ModelStates\Tests\Dummy\ModelStates\AnotherDirectory\StateG;
use Spatie\ModelStates\Tests\Dummy\ModelStates\AnotherDirectory\StateH;
use Spatie\ModelStates\Tests\Dummy\OtherModelStates\OtherModelState;
use Spatie\ModelStates\Tests\Dummy\OtherModelStates\StateX;
use Spatie\ModelStates\Tests\Dummy\OtherModelStates\StateY;
use Spatie\ModelStates\Tests\Dummy\TestModel;
use Spatie\ModelStates\Tests\Dummy\TestModelUpdatingEvent;
use Spatie\ModelStates\Tests\Dummy\TestModelWithCustomTransition;
use Spatie\ModelStates\Tests\Dummy\TestModelWithDefault;

it('resolve state class', function () {
    expect(ModelState::resolveStateClass(StateA::class))->toEqual(StateA::class);
    expect(ModelState::resolveStateClass(StateC::class))->toEqual(StateC::class);
    expect(ModelState::resolveStateClass(StateC::getMorphClass()))->toEqual(StateC::class);
    expect(ModelState::resolveStateClass(StateC::$name))->toEqual(StateC::class);
    expect(ModelState::resolveStateClass(StateD::class))->toEqual(StateD::class);
    expect(ModelState::resolveStateClass(StateD::getMorphClass()))->toEqual(StateD::class);
    expect(ModelState::resolveStateClass(StateD::$name))->toEqual(StateD::class);
    expect(ModelState::resolveStateClass(StateE::class))->toEqual(StateE::class);
    expect(ModelState::resolveStateClass(StateE::getMorphClass()))->toEqual(StateE::class);
    expect(ModelState::resolveStateClass(StateF::getMorphClass()))->toEqual(StateF::class);
    expect(ModelState::resolveStateClass(StateG::getMorphClass()))->toEqual(StateG::class);
    expect(ModelState::resolveStateClass(StateG::getMorphClass()))->toEqual(StateG::class);
    expect(ModelState::resolveStateClass(StateH::getMorphClass()))->toEqual(StateH::class);
    expect(ModelState::resolveStateClass(StateH::getMorphClass()))->toEqual(StateH::class);
});

test('resolve state class without scanned folder', function () {

    config()->set('model-states.folder_scan', false);

    expect(ModelState::resolveStateClass(StateA::class))->toEqual(StateA::class);
    expect(ModelState::resolveStateClass(StateC::class))->toEqual(StateC::class);
    expect(ModelState::resolveStateClass(StateC::getMorphClass()))->toEqual(StateC::class);
    expect(ModelState::resolveStateClass(StateC::$name))->toEqual(StateC::class);
    expect(ModelState::resolveStateClass(StateD::class))->toEqual(StateD::class);
    expect(ModelState::resolveStateClass(StateD::getMorphClass()))->toEqual(StateD::class);
    expect(ModelState::resolveStateClass(StateD::$name))->toEqual(StateD::class);
    expect(ModelState::resolveStateClass(StateE::class))->toEqual(StateE::class);
    expect(ModelState::resolveStateClass(StateE::getMorphClass()))->toEqual(StateE::class);
    expect(ModelState::resolveStateClass(StateF::getMorphClass()))->toEqual(StateF::class);
    expect(ModelState::resolveStateClass(StateG::getMorphClass()))->toEqual(StateG::class);
    expect(ModelState::resolveStateClass(StateG::getMorphClass()))->toEqual(StateG::class);
    expect(ModelState::resolveStateClass(StateH::getMorphClass()))->toEqual(StateH::class);
    expect(ModelState::resolveStateClass(StateH::getMorphClass()))->toEqual(StateH::class);

    ModelState::reset();

});

test('a state that is not transitionned to or from will be missing from registration if folder is not scanned', function () {


    ModelState::reset();
    config()->set('model-states.folder_scan', false);
    $notScannedStates = ModelState::getStateMapping();

    ModelState::reset();
    config()->set('model-states.folder_scan', true);
    $scannedStates = ModelState::getStateMapping();


    expect($notScannedStates)->not->toEqual($scannedStates);

    expect($notScannedStates)->not->toContain(StateE::class);
    expect($scannedStates)->toContain(StateE::class);

});

test('a state class cannot resolve the mapped state from different baseclass', function () {

    expect(OtherModelState::resolveStateClass(StateC::getMorphClass()))->not()->toEqual(StateC::class);
    expect(OtherModelState::resolveStateClass(StateC::$name))->not()->toEqual(StateC::class);
    expect(OtherModelState::resolveStateClass(StateD::getMorphClass()))->not()->toEqual(StateD::class);
    expect(OtherModelState::resolveStateClass(StateD::$name))->not()->toEqual(StateD::class);
    expect(OtherModelState::resolveStateClass(StateE::getMorphClass()))->not()->toEqual(StateE::class);
    expect(OtherModelState::resolveStateClass(StateG::getMorphClass()))->not()->toEqual(StateG::class);
    expect(OtherModelState::resolveStateClass(StateG::$name))->not()->toEqual(StateG::class);
    expect(OtherModelState::resolveStateClass(StateH::getMorphClass()))->not()->toEqual(StateH::class);
    expect(OtherModelState::resolveStateClass(StateH::$name))->not()->toEqual(StateH::class);
});

it('transitionable states', function () {
    $modelA = TestModel::make(['state' => StateA::class]);

    expect(
        [
            StateB::getMorphClass(),
            StateC::getMorphClass(),
            StateD::getMorphClass(),
            StateF::getMorphClass(),
        ]
    )->toEqual($modelA->state->transitionableStates());

    $modelB = TestModelWithDefault::create([
        'state' => StateC::class,
    ]);

    expect($modelB->state->transitionableStates())->toEqual([]);
});

it('transitionable states with custom transition', function () {
    $model = TestModelWithCustomTransition::create(['state' => StateX::class]);
    expect($model->state->transitionableStates())->toBe([StateY::class]);
});

it('equals', function () {
    $modelA = TestModelWithDefault::create();

    $modelB = TestModelWithDefault::create();

    expect($modelA->state->equals($modelB->state))->toBeTrue();

    $modelA = TestModelWithDefault::create();

    $modelB = TestModelWithDefault::create([
        'state' => StateC::class,
    ]);

    expect($modelA->state->equals($modelB->state))->toBeFalse();

    expect($modelA->state->equals(StateA::class))->toBeTrue();
});

it('can transition to', function () {
    $state = new StateA(new TestModel());
    $state->setField('state');

    expect($state->canTransitionTo(StateB::class))->toBeTrue();
    expect($state->canTransitionTo(StateC::class))->toBeTrue();
    expect($state->canTransitionTo(StateF::class))->toBeTrue();

    $state = new StateB(new TestModel());
    $state->setField('state');

    expect($state->canTransitionTo(StateB::class))->toBeFalse();
    expect($state->canTransitionTo(StateA::class))->toBeFalse();
});

it('get states', function () {
    $states = TestModelWithDefault::getStates();

    expect(
        [
            'state' => [
                StateA::getMorphClass(),
                StateB::getMorphClass(),
                StateC::getMorphClass(),
                StateD::getMorphClass(),
                StateE::getMorphClass(),
                StateF::getMorphClass(),
                StateG::getMorphClass(),
                StateH::getMorphClass(),
            ],
        ],
    )->toEqual($states->toArray());
});

it('get states for', function () {
    $states = TestModelWithDefault::getStatesFor('state');

    expect(
        [
            StateA::getMorphClass(),
            StateB::getMorphClass(),
            StateC::getMorphClass(),
            StateD::getMorphClass(),
            StateE::getMorphClass(),
            StateF::getMorphClass(),
            StateG::getMorphClass(),
            StateH::getMorphClass(),
        ],
    )->toEqual($states->toArray());

    expect($states->toArray())->toEqual([
//        'state' => [
        0 => 'Spatie\ModelStates\Tests\Dummy\ModelStates\StateA',
        1 => 'Spatie\ModelStates\Tests\Dummy\ModelStates\StateB',
        2 => 'C',
        3 => 4,
        4 => 5,
        5 => 'Spatie\ModelStates\Tests\Dummy\ModelStates\AnotherDirectory\StateF',
        6 => 'G',
        7 => 8,
//    ],
    ]);
});

it('get default states', function () {
    $states = TestModelWithDefault::getDefaultStates();

    expect(
        [
            'state' => StateA::getMorphClass(),
        ],
    )->toEqual($states->toArray());
});

it('get default states for', function () {
    $defaultState = TestModelWithDefault::getDefaultStateFor('state');

    expect($defaultState)->toEqual(StateA::getMorphClass());
});

it('make', function () {
    $stateA = ModelState::make(StateA::class, new TestModel());

    expect($stateA)->toBeInstanceOf(StateA::class);

    $stateC = ModelState::make('C', new TestModel());

    expect($stateC)->toBeInstanceOf(StateC::class);

    $stateD = ModelState::make(4, new TestModel());

    expect($stateD)->toBeInstanceOf(StateD::class);
});

it('all', function () {
    expect(
        [
            StateA::getMorphClass() => StateA::class,
            StateB::getMorphClass() => StateB::class,
            StateC::getMorphClass() => StateC::class,
            StateD::getMorphClass() => StateD::class,
            StateE::getMorphClass() => StateE::class,
            StateF::getMorphClass() => StateF::class,
            StateG::getMorphClass() => StateG::class,
            StateH::getMorphClass() => StateH::class,
        ]
    )->toEqual(ModelState::all()->toArray());
});

it('default is set when constructing a new model', function () {
    $model = new TestModel();

    expect($model->state->equals(StateA::class))->toBeTrue();
});

it('default is set when creating a new model', function () {
    $model = TestModel::create();

    expect($model->state->equals(StateA::class))->toBeTrue();
});

it('get model', function () {
    $stateA = ModelState::make(StateA::class, new TestModel());

    expect($stateA->getModel())->toBeInstanceOf(TestModel::class);
});

it('get field', function () {
    $model = TestModel::create();

    expect($model->state->getField())->toEqual('state');
});

it('can override default transition', function () {
    Event::fake();

    config()->set(
        'model-states.default_transition',
        \Spatie\ModelStates\Tests\Dummy\Transitions\CustomDefaultTransition::class
    );

    TestModel::create()->state->transitionTo(StateB::class);

    Event::assertNotDispatched(TestModelUpdatingEvent::class);
});
