<?php

namespace Bjuppa\EloquentStateMachine\Eloquent;

use Bjuppa\EloquentStateMachine\StateEvent;
use Bjuppa\EloquentStateMachine\Exceptions\UnexpectedStateException;
use Bjuppa\EloquentStateMachine\Exceptions\UnhandledEventException;
use Bjuppa\EloquentStateMachine\ModelCreatedStateEvent;
use Bjuppa\EloquentStateMachine\RootState;
use Bjuppa\EloquentStateMachine\SimpleState;
use Bjuppa\EloquentStateMachine\Support\State;
use DomainException;
use InvalidArgumentException;
use LogicException;
use Throwable;

trait HasState
{
    use CanLockPessimistically;
    use SavesInTransaction;

    /**
     * Classname of the root state for the state machine of this model.
     * Used to initialize the state machine upon model creation.
     *
     * Always set this in your model class.
     *
     * Your root state class should extend
     * @see \Bjuppa\EloquentStateMachine\RootState
     */
    protected string $rootStateClass;

    /**
     * Logic determining the state this model is currently in.
     *
     * Always implement this in your model class.
     *
     * Current state may be stored directly in a database column, but you are free to use
     * any logic involving the attributes and relationships of the current model.
     *
     * After determining the current state, call $this->makeState()
     * passing the desired classname and return it.
     *
     * @throws \Throwable
     */
    abstract public function getState(): SimpleState;

    /**
     * Instantiate an event for the initial transition when models are created.
     *
     * Create your own event class extending ModelCreatedStateEvent and override this method if you need a custom
     * payload when initializing the state machine for freshly created model instances.
     *
     * @see \Bjuppa\EloquentStateMachine\ModelCreatedStateEvent
     */
    protected function initialTransitionEvent(): ModelCreatedStateEvent
    {
        return new ModelCreatedStateEvent($this);
    }

    /**
     * Dispatch an event to the state machine, and silence any UnhandledEventException.
     *
     * This is your primary interaction point with the model's state machine.
     *
     * Instantiate an event class representing whatever happened in the outside world
     * and pass it to this method for processing by the state machine.
     *
     * This model will be refreshed from storage and then manipulated by the state machine within a transaction.
     *
     * @return SimpleState|null The committed and verified state of this model after any successful transition,
     * or null if the event had no handler and thus the model was left unmodified.
     *
     * @throws \Throwable Except \Bjuppa\EloquentStateMachine\Exceptions\UnhandledEventException
     */
    public function dispatchToState(StateEvent $event): ?SimpleState
    {
        try {
            return $this->dispatchToStateOrFail($event);
        } catch (UnhandledEventException $e) {
            return null;
        }
    }

    /**
     * Dispatch an event to the state machine.
     *
     * Use this method directly if you don't want to silence any exceptions.
     *
     * @return SimpleState The committed and verified state of this model after any transitions.
     *
     * @throws \Throwable If any part of the state machine throws an exception when handling the event,
     * the transaction will be aborted, the model will be reset to the state it was before,
     * and the exception will be re-thrown for you to handle.
     */
    public function dispatchToStateOrFail(StateEvent $event): SimpleState
    {
        if ($this->isDirty()) {
            throw new LogicException(get_class($this) . ' [' . $this->getKey() . '] must not be dirty when dispatching event to state');
        }

        try {
            return $this->transactionWithRefreshForUpdate(function () use ($event) {
                return tap(
                    $this->getState()->dispatch($event),
                    function (State $destination) use ($event) {
                        $this->assertStateAfterEvent($destination, $event);
                        $event->processSideEffects();
                    }
                );
            });
        } catch (Throwable $e) {
            $this->refresh();
            throw $e;
        }
    }

    /**
     * Generate a state object bound to this model.
     */
    protected function makeState(string $classname): State
    {
        if (!is_a($classname, State::class, true)) {
            throw new InvalidArgumentException(
                $classname . ' is not a ' . State::class
            );
        }
        return new $classname($this);
    }

    /**
     * -------------------------------------------------------------------------
     * Methods below are for internal use.
     * -------------------------------------------------------------------------
     */

    /**
     * Get the root state object for this model.
     *
     * @throws DomainException
     */
    protected function rootState(): RootState
    {
        if (!isset($this->rootStateClass)) {
            throw new DomainException(get_class($this) . '::$rootStateClass must be specified');
        }
        if (!is_a($this->rootStateClass, RootState::class, true)) {
            throw new DomainException(
                get_class($this) . '::$rootStateClass ' . $this->rootStateClass . ' is not a ' . RootState::class
            );
        }
        return $this->makeState($this->rootStateClass);
    }

    /**
     * Transition the state machine into its default state.
     *
     * @throws \Throwable
     */
    protected function performInitialTransition(): void
    {
        $event = $this->initialTransitionEvent();
        $destination = $this->rootState()->defaultEntry($event);
        $this->assertStateAfterEvent($destination, $event);
        $event->processSideEffects();
    }

    /**
     * Ensure the current state matches the given state.
     * Called after processing an event.
     *
     * @throws UnexpectedStateException
     */
    public function assertStateAfterEvent(State $state, StateEvent $event): void
    {
        $this->refresh();
        if (!$state->is($this->getState())) {
            throw new UnexpectedStateException(
                $state,
                $this->getState(),
                $event
            );
        }
    }

    /**
     * Boot the trait.
     */
    protected static function bootHasState()
    {
        static::created(function ($model) {
            $model->performInitialTransition();
        });
    }
}
