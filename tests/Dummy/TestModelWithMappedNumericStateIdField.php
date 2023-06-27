<?php

namespace Spatie\ModelStates\Tests\Dummy;

use Illuminate\Database\Eloquent\Model;
use Spatie\ModelStates\HasStates;
use Spatie\ModelStates\Tests\Dummy\ModelStates\ModelStateMappedNumeric;
use Spatie\ModelStates\Tests\TestCase;

class TestModelWithMappedNumericStateIdField extends Model
{
    use HasStates;
    protected $guarded = [];

    protected $casts = [
        'state' => ModelStateMappedNumeric::class,
    ];


    public $map = ['state' => 'state_id'];

    public function getTable()
    {
//        return TestCase::DEFAULT_STATE_TEST_TABLE;
        return TestCase::STATE_ID_TEST_TABLE;
    }
}
