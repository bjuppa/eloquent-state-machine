<?php

namespace Bjuppa\EloquentStateMachine\Support;

use Bjuppa\EloquentStateMachine\StateEvent;
use Bjuppa\EloquentStateMachine\SimpleState;
use DomainException;
use Illuminate\Database\Eloquent\Model;

abstract class SubState extends State
{
    /**
     * Classname of the parent superstate this substate belongs to.
     */
    public static string $superStateClass;

    protected State $superState;

    public function __construct(Model $model)
    {
        parent::__construct($model);

        if (!static::$superStateClass) {
            throw new DomainException(get_class($this) . '::$superStateClass must be specified');
        }

        if (!is_a($this->superState, State::class)) {
            throw new DomainException(
                get_class($this) . '::$superStateClass (' . static::$superStateClass . ') must be a ' . State::class
            );
        }

        if (is_a($this->superState, SimpleState::class)) {
            throw new DomainException(
                get_class($this) . '::$superStateClass (' . static::$superStateClass . ') must not be a ' . SimpleState::class
            );
        }

        $this->superState = $this->make(static::$superStateClass);
    }

    /**
     * Actions to process when exiting this state.
     *
     * Manipulate $this->model in here.
     * Put any side effects into the event object for processing after the transition is completed.
     *
     * @throws \Throwable
     */
    public abstract function exit(StateEvent $event): void;

    protected function dispatchInternal(StateEvent $event): bool
    {
        return parent::dispatchInternal($event) ?: $this->superState->dispatchInternal($event);
    }

    protected function dispatchLocal(StateEvent $event): SimpleState
    {
        return $this->handle($event) ?: $this->dispatchExternal($event);
    }

    private function dispatchExternal(StateEvent $event): SimpleState
    {
        $this->exit($event);
        return $this->superState->dispatchLocal($event);
    }

    public function branch(): array
    {
        return array_merge(parent::branch(), $this->superState->branch());
    }
}
