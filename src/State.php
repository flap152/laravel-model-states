<?php

namespace Spatie\ModelStates;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Support\Collection;
use JsonSerializable;
use ReflectionClass;
use Spatie\ModelStates\Attributes\AttributeLoader;
use Spatie\ModelStates\Events\StateChanged;
use Spatie\ModelStates\Exceptions\CouldNotPerformTransition;
use Spatie\ModelStates\Exceptions\InvalidConfig;

abstract class State implements Castable, JsonSerializable
{
    private $model;

    private StateConfig $stateConfig;

    private string $field;

    private static array $stateMapping = [];


//    private static bool $isNumericField = true;
    protected static bool $isNumericField = false;

    /**
     * @param  \Illuminate\Database\Eloquent\Model  $model
     */
    public function __construct($model)
    {
        $this->model = $model;
        $this->stateConfig = static::config();
    }

    public static function reset()
    {
        static :: $stateMapping = [];
    }

    public static function config(): StateConfig
    {
        $reflection = new ReflectionClass(static::class);

        $baseClass = $reflection->name;

        while ($reflection && ! $reflection->isAbstract()) {
            $reflection = $reflection->getParentClass();

            $baseClass = $reflection->name;
        }

        $stateConfig = new StateConfig($baseClass);

        if (version_compare(PHP_VERSION, '8.0', '>=')) {
            $stateConfig = (new AttributeLoader($baseClass))->load($stateConfig);
        }

        return $stateConfig;
    }

    public static function castUsing(array $arguments)
    {
        return new StateCaster(static::class);
    }

    public static function getMorphClass(): string
    {
        //FFLLAAPP
//        return static::$name ?? static::class;
        //FFLLAAPP
        $name = static::$name ?? null;
//        $name = $name ?? static::getStateMapping()->first(fn($key, $item) => $item == $name);
        if (is_numeric($name)) return $name;
        return $name ?? static::class;
    }



    public static function getStoredValue(): float|int|string
    {
        $value = static::getMorphClass();
        if ($value === static::class && ! empty( self::$stateMapping)){
            $value = self::getMappedValue(static::class);
        }
        if (static::$isNumericField) {
            return static::makeStringNumeric($value);
        }
        return $value;
    }
    public static function makeStringNumeric($value)
    {
        if (is_numeric($value)) return $value;
        return crc32($value);
    }

    public static function getMappedValue($class)
    {
//        $coll = self::getStateMapping();
        $coll = collect( static::config()->mappedStates);
        $f = $coll->filter(fn( $item) => $item == $class);
        $g = /*collect*/($f?->keys())?->sort()->first() ?? $class;
        return $g;
    }

    public static function getStateMapping(): Collection
    {
        if (! isset(self::$stateMapping[static::class])) {
            self::$stateMapping[static::class] = static::resolveStateMapping();
        }

        return collect(self::$stateMapping[static::class]);
    }

    public static function resolveStateClass($state): ?string
    {
        if ($state === null) {
            return null;
        }

        if ($state instanceof State) {
            return get_class($state);
        }

        foreach (static::getStateMapping() as $key => $stateClass) {
            if (! class_exists($stateClass)) {
                continue;
            }

            // Loose comparison is needed here in order to support non-string values,
            // Laravel casts their database value automatically to strings if we didn't specify the fields in `$casts`.
            if ($key == $state) {
                return $stateClass;
            }


            $name = $stateClass::getMorphClass();

            if ($name == $state) {
                return $stateClass;
            }
        }

        return $state;
    }

    /**
     * @param  string  $name
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return  State
     */
    public static function make(string $name, $model): State
    {
        $stateClass = static::resolveStateClass($name);

        if (! is_subclass_of($stateClass, static::class)) {
            throw InvalidConfig::doesNotExtendBaseClass($name, static::class);
        }

        return new $stateClass($model);
    }

    /**
     * @return array<State>
     */
    public static function resolveStatesFromFolder(StateConfig $stateConfig): array
    {
        $resolvedStates = [];
//        $stateConfig = static::config();

        $reflection = new ReflectionClass(static::class);

        ['dirname' => $directory] = pathinfo($reflection->getFileName());

        $files = scandir($directory);

        $namespace = $reflection->getNamespaceName();

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            ['filename' => $className] = pathinfo($file);

            /** @var \Spatie\ModelStates\State|mixed $stateClass */
            $stateClass = $namespace . '\\' . $className;

            if (!is_subclass_of($stateClass, $stateConfig->baseStateClass)) {
                continue;
            }

            $resolvedStates[$stateClass::getMorphClass()] = $stateClass;
        }
        return $resolvedStates;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getModel()
    {
        return $this->model;
    }

    public function getField(): string
    {
        return $this->field;
    }

    /**
     * @return \Illuminate\Support\Collection|string[]|static[] A list of class names.
     */
    public static function all(): Collection
    {
        //resolveStateMapping is private and may not do what we want. Use the getter instead
//        return collect(self::resolveStateMapping());
        return collect(self::getStateMapping());
    }

    public function setField(string $field): self
    {
        $this->field = $field;

        return $this;
    }

    /**
     * @param  string|State  $newState
     * @param  mixed  ...$transitionArgs
     * @return  \Illuminate\Database\Eloquent\Model
     */
    public function transitionTo($newState, ...$transitionArgs)
    {
        $newState = $this->resolveStateObject($newState);

        $from = static::getMorphClass();

        $to = $newState::getMorphClass();

        if (! $this->stateConfig->isTransitionAllowed($from, $to)) {
            throw CouldNotPerformTransition::notFound($from, $to, $this->model);
        }

        $transition = $this->resolveTransitionClass(
            $from,
            $to,
            $newState,
            ...$transitionArgs
        );

        return $this->transition($transition);
    }

    /**
     * @param  Transition  $transition
     * @return  \Illuminate\Database\Eloquent\Model
     */
    public function transition(Transition $transition)
    {
        if (method_exists($transition, 'canTransition')) {
            if (! $transition->canTransition()) {
                throw CouldNotPerformTransition::notAllowed($this->model, $transition);
            }
        }

        $model = app()->call([$transition, 'handle']);
        $model->{$this->field}->setField($this->field);

        event(new StateChanged(
            $this,
            $model->{$this->field},
            $transition,
            $this->model,
        ));

        return $model;
    }

    public function transitionableStates(...$transitionArgs): array
    {
        return collect($this->stateConfig->transitionableStates(static::getMorphClass()))->reject(function ($state) use ($transitionArgs) {
            return ! $this->canTransitionTo($state, ...$transitionArgs);
        })->toArray();
    }

    public function canTransitionTo($newState, ...$transitionArgs): bool
    {
        $newState = $this->resolveStateObject($newState);

        $from = static::getMorphClass();

        $to = $newState::getMorphClass();

        if (! $this->stateConfig->isTransitionAllowed($from, $to)) {
            return false;
        }

        $transition = $this->resolveTransitionClass(
            $from,
            $to,
            $newState,
            ...$transitionArgs
        );

        if (method_exists($transition, 'canTransition')) {
            return $transition->canTransition();
        }

        return true;
    }

    public function getValue(): string
    {
        return static::getMorphClass();
    }

    public function equals(...$otherStates): bool
    {
        foreach ($otherStates as $otherState) {
            $otherState = $this->resolveStateObject($otherState);

            if ($this->stateConfig->baseStateClass === $otherState->stateConfig->baseStateClass
                && $this->getValue() === $otherState->getValue()) {
                return true;
            }
        }

        return false;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->getValue();
    }

    public function __toString(): string
    {
        return $this->getValue();
    }

    private function resolveStateObject($state): self
    {
        if (is_object($state) && is_subclass_of($state, $this->stateConfig->baseStateClass)) {
            return $state;
        }

        $stateClassName = $this->stateConfig->baseStateClass::resolveStateClass($state);

        return new $stateClassName($this->model, $this->stateConfig);
    }

    private function resolveTransitionClass(
        string $from,
        string $to,
        State $newState,
        ...$transitionArgs
    ): Transition {
        $transitionClass = $this->stateConfig->resolveTransitionClass($from, $to);

        if ($transitionClass === null) {
            $defaultTransition = config('model-states.default_transition', DefaultTransition::class);

            $transition = new $defaultTransition(
                $this->model,
                $this->field,
                $newState
            );
        } else {
            $transition = new $transitionClass($this->model, ...$transitionArgs);
        }

        return $transition;
    }

    private static function resolveStateMapping(): array
    {
        // MOVED to scan folder method
//        $reflection = new ReflectionClass(static::class);
//
//        ['dirname' => $directory] = pathinfo($reflection->getFileName());
//
//        $files = scandir($directory);
//
//        $namespace = $reflection->getNamespaceName();

        $resolvedStates = [];


        $stateConfig = static::config();
        $mappedStates = $stateConfig->mappedStates;



/**        foreach ($files as $file) {
//            if ($file === '.' || $file === '..') {
//                continue;
//            }
//
//            ['filename' => $className] = pathinfo($file);
//
//            /** @var \Spatie\ModelStates\State|mixed $stateClass */
//            $stateClass = $namespace . '\\' . $className;
//

//        if ($stateConfig->canScanFolders() ) {
        $scannedStates = [];
        if ($stateConfig->canScanFolders() ) {
            $scannedStates = self::resolveStatesFromFolder($stateConfig);
        }

        /** @var \Spatie\ModelStates\State|mixed $stateClass */
        foreach ($scannedStates as $stateClass) {
            $mappedStates[$stateClass::getMorphClass()] = $stateClass;
        }
        foreach ($stateConfig->registeredStates as $stateClass) {
            $mappedStates[$stateClass::getMorphClass()] = $stateClass;
        }

        return $mappedStates;
    }
}
