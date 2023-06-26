<?php

namespace Spatie\ModelStates\Tests\Dummy;

use Illuminate\Database\Eloquent\Model;
use Spatie\ModelStates\HasStates;
use Spatie\ModelStates\Tests\Dummy\ModelStates\ModelState;
use Spatie\ModelStates\Tests\TestCase;

/**
 * @method static static create(array $extra = [])
 * @method static|\Illuminate\Database\Eloquent\Builder whereNotState(string $fieldNames, $states)
 * @method static|\Illuminate\Database\Eloquent\Builder whereState(string $fieldNames, $states)
 * @method static static query()
 * @method static self find(int $id)
 * @property ModelState|null state
 * @property string|null message
 * @property int id
 */
class TestModel extends Model
{
    use HasStates;
    protected $guarded = [];

    protected $casts = [
        'state' => ModelState::class,
    ];

    protected $dispatchesEvents = [
        'updating' => TestModelUpdatingEvent::class,
    ];

    public function getTable()
    {
        return TestCase::DEFAULT_STATE_TEST_TABLE;
    }
}
