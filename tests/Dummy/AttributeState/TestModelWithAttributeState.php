<?php

namespace Spatie\ModelStates\Tests\Dummy\AttributeState;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\ModelStates\HasStates;
use Spatie\ModelStates\Tests\TestCase;

/**
 * @method static static create(array $extra = [])
 * @method static|Builder whereNotState(string $fieldNames, $states)
 * @method static|Builder whereState(string $fieldNames, $states)
 * @method static static query()
 * @method static self find(int $id)
 * @property AttributeState|null state
 * @property string|null message
 * @property int id
 */
class TestModelWithAttributeState extends Model
{
    use HasStates;
    protected $guarded = [];

    protected $casts = [
        TestCase::STATE_FIELD_NAME => AttributeState::class,
    ];

    public function getTable()
    {
        return TestCase::DEFAULT_STATE_TEST_TABLE ;
    }
}
