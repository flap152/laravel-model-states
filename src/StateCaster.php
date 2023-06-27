<?php

namespace Spatie\ModelStates;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Collection;
use Spatie\ModelStates\Exceptions\UnknownState;

class StateCaster implements CastsAttributes
{
    /** @var string|\Spatie\ModelStates\State */
    private string $baseStateClass;
//    private bool $isNumericField = false;
    protected static bool $isNumericField = false;

    public function __construct(string $baseStateClass)
    {
        $this->baseStateClass = $baseStateClass;
    }

    public function get($model, string $key, $value, array $attributes)
    {
        $myKey = ($model->map)[$key] ?? $key;
        $value2 = $attributes[$myKey] ?? $value;
        $value = $value2 ?? $value;
        if ($value === null) {
            return null;
        }

        $mapping = $this->getStateMapping();

        $stateClassName = $mapping[$value];

        /** @var \Spatie\ModelStates\State $state */
        $state = new $stateClassName($model);

//        $state->setField($key);
        $state->setField($myKey);

        return $state;
//        return [$key => $state];
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $key
     * @param \Spatie\ModelStates\State|string $value
     * @param array $attributes
     *
     * @return string
     */
    public function set($model, string $key, $value, array $attributes)//: ?string
    {
        if ($value === null) {
            return null;
        }

        $myKey = ($model->map)[$key] ?? $key;
        if (! is_subclass_of($value, $this->baseStateClass)) {
            $mapping = $this->getStateMapping();

            if (! isset($mapping[$value])) {
                throw UnknownState::make(
                    $value,
                    $this->baseStateClass,
                    get_class($model),
                    $key
                );
            }

            $value = $mapping[$value];
        }

        if ($value instanceof $this->baseStateClass) {
//            $value->setField($key);
            $value->setField($myKey);
        }

//        return $value::getMorphClass();
//        return [$key => $value::getMorphClass()];
        if ($key !== $myKey) { // there's a field mapping
            return [
//            $key => $value::getStoredValue(),
                $myKey => $value::getStoredValue(),
            ];
        }
//        return $this->baseStateClass::makeStringNumericIfNeeded($value::getMorphClass());
        return $value::getStoredValue();
    }

    private function getStateMapping(): Collection
    {
        return $this->baseStateClass::getStateMapping();
    }
}
