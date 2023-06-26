<?php

use Illuminate\Support\Facades\DB;
use Spatie\ModelStates\Tests\Dummy\ModelStates\NumericStates\StateL_11;
use Spatie\ModelStates\Tests\Dummy\TestModelWithStateIdField;


test('non-sequential numeric explicit state mapping works on create', function () {
    DB::table((new TestModelWithStateIdField())->getTable())->insert([
        'id' => 1,
//        'state' => 11,
        'state_id' => 11,
    ]);

    $model = TestModelWithStateIdField::find(1);

    expect($model->state_id)->toEqual(StateL_11::getStoredValue());
    expect($model->state)->toBeInstanceOf(StateL_11::class);
});

