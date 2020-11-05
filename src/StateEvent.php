<?php

namespace Bjuppa\EloquentStateMachine;

use Bjuppa\EloquentStateMachine\Support\Event;
use Bjuppa\EloquentStateMachine\StateMachineModel as Model;

/**
 * Extend this class to describe an event that the state machine should handle.
 */
abstract class StateEvent extends Event
{
    /**
     * Subclass' constructor may receive and store any additional payload data needed during event handling.
     */
    public function __construct(Model $model)
    {
        parent::__construct($model);
    }

    /**
     * Manipulates $this->model during transition.
     *
     * Actions will be executed when the transition is in the common superstate.
     *
     * Side-effects not directly manipulating the model, like queuing notifications,
     * should be put off to after the transition using
     * $this->deferSideEffect(function() { ... })
     *
     * @throws \Throwable
     */
    protected function actions(): void
    {
        //
    }

    /**
     * Dispatch this event to the state machine, and silence any UnhandledEventException.
     *
     * @see static::dispatchOrFail to not silence any exceptions.
     *
     * This is your primary interaction point with the model's state machine.
     *
     * The model will be refreshed from storage and then manipulated by the state machine within a transaction.
     *
     * @return SimpleState|null The committed and verified state of the model after any successful transition,
     * or null if the event had no handler and thus the model was left unmodified.
     *
     * @throws \Throwable Except \Bjuppa\EloquentStateMachine\Exceptions\UnhandledEventException
     */
    public static function dispatch(...$constructorArguments): ?SimpleState
    {
        return static::make(...$constructorArguments)->dispatchToState();
    }

    /**
     * Dispatch this event to the state machine.
     *
     * @see static::dispatch if you want to silence any UnhandledEventException.
     *
     * @return SimpleState The committed and verified state of the model after any transitions.
     *
     * @throws \Throwable If any part of the state machine throws an exception when handling the event,
     * the transaction will be aborted, the model will be reset to the state it was before,
     * and the exception will be re-thrown for you to handle.
     */
    public static function dispatchOrFail(...$constructorArguments): SimpleState
    {
        return static::make(...$constructorArguments)->dispatchToStateOrFail();
    }
}
