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
     *
     * Always set this in your substate class.
     *
     * Can be either
     * @see \Bjuppa\EloquentStateMachine\RootState
     * or
     * @see \Bjuppa\EloquentStateMachine\CompositeState
     */
    public static string $superStateClass;

    /**
     * Parent superstate instance.
     */
    protected State $superState;

    public function __construct(Model $model)
    {
        parent::__construct($model);

        if (!isset(static::$superStateClass)) {
            throw new DomainException(get_class($this) . '::$superStateClass must be specified');
        }

        if (!is_a(static::$superStateClass, State::class, true)) {
            throw new DomainException(
                get_class($this) . '::$superStateClass (' . static::$superStateClass . ') must be a ' . State::class
            );
        }

        if (is_a(static::$superStateClass, SimpleState::class, true)) {
            throw new DomainException(
                get_class($this) . '::$superStateClass (' . static::$superStateClass . ') must not be a ' . SimpleState::class
            );
        }

        $this->superState = $this->make(static::$superStateClass);
    }

    /**
     * Actions to process when entering this state from the outside.
     *
     * Manipulate $this->model in here.
     * Put any side effects into the event object for processing after the transition is completed.
     *
     * @throws \Throwable
     */
    public abstract function entry(StateEvent $event): void;

    /**
     * Actions to process when exiting this state.
     *
     * Manipulate $this->model in here.
     * Put any side effects into the event object for processing after the transition is completed.
     *
     * @throws \Throwable
     */
    public abstract function exit(StateEvent $event): void;

    /**
     * Do a recursive transition, exiting the current state and entering it again.
     *
     * Also called self-transition.
     */
    protected function transitionToSelf(StateEvent $event): SimpleState
    {
        return $this->transitionToState($event, get_class($this), static::$superStateClass);
    }

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
