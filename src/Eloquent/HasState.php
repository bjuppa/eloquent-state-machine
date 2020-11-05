<?php

namespace Bjuppa\EloquentStateMachine\Eloquent;

use Bjuppa\EloquentStateMachine\StateEvent;
use Bjuppa\EloquentStateMachine\Exceptions\UnexpectedStateException;
use Bjuppa\EloquentStateMachine\ModelCreatedStateEvent;
use Bjuppa\EloquentStateMachine\RootState;
use Bjuppa\EloquentStateMachine\SimpleState;
use Bjuppa\EloquentStateMachine\Support\State;
use DomainException;
use InvalidArgumentException;
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
    //TODO: remove this assertStateAfterEvent
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
