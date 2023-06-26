<?php

namespace Spatie\ModelStates\Tests\Dummy\ModelStates;

use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;
use Spatie\ModelStates\Tests\Dummy\ModelStates\NumericStates\StateL_11;
use Spatie\ModelStates\Tests\Dummy\ModelStates\NumericStates\StateM_155_default;
use Spatie\ModelStates\Tests\Dummy\ModelStates\NumericStates\StateN;

//use Spatie\ModelStates\Tests\Dummy\ModelStates\AnotherDirectory\StateG;
//use Spatie\ModelStates\Tests\Dummy\ModelStates\AnotherDirectory\StateH;

///*abstract*/ class ModelStateMappedNumeric extends State
abstract class ModelStateMappedNumeric extends State
{
    protected static bool $isMappingExplicit = true;
    protected static bool $isNumericField = true;

    public static function config(): StateConfig    {
        return parent::config()
//            ->mapped()
//            ->doNotScan()
            ->registerState(StateL_11::class, '11')
            ->registerState(StateM_155_default::class, '22')
            ->registerState(StateM_155_default::class)
//            ->registerState(StateN::class, '33')
            ->registerState(StateN::class)
            ->registerMappedStates(['77' => StateL_11::class, '88' => StateN::class])
            ->default(StateM_155_default::class)
            ;
    }

}
