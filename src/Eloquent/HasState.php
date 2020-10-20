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
use LogicException;
use Throwable;

trait HasState
{
    use CanLockPessimistically;

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
     * Dispatch an event to the state machine.
     * This is your primary interaction point with the model's state machine.
     *
     * Instantiate an event class representing whatever happened in the outside world
     * and pass it to this method for processing by the state machine.
     *
     * This model will be refreshed from storage and then manipulated by the state machine within a transaction.
     *
     * @return SimpleState The committed and verified state the model is in after any transitions.
     *
     * @throws \Throwable If any part of the state machine throws an exception when handling the event,
     * the transaction will be aborted, the model will be reset to the state it was before,
     * and the exception will be re-thrown for you to handle.
     */
    public function dispatchToState(StateEvent $event): SimpleState
    {
        if ($this->isDirty()) {
            throw new LogicException('Model must not be dirty when dispatching event to state');
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
     * Perform the initial transition to the state machine's default state.
     *
     * @throws \Throwable
     */
    protected function initialTransition(): void
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
     * Always save the model to the database using transaction.
     * @see \Illuminate\Database\Eloquent\Model::save
     * @see \Illuminate\Database\Eloquent\Model::saveOrFail
     *
     * @param  array  $options
     * @return bool
     *
     * @throws \Throwable
     */
    public function save(array $options = [])
    {
        return $this->getConnection()->transaction(function () use ($options) {
            return parent::save($options);
        });
    }

    /**
     * Avoid double transactions.
     * @see \Illuminate\Database\Eloquent\Model::saveOrFail
     *
     * @param  array  $options
     * @return bool
     *
     * @throws \Throwable
     */
    public function saveOrFail(array $options = [])
    {
        return $this->save();
    }

    /**
     * Boot the trait.
     */
    protected static function bootHasState()
    {
        static::created(function ($model) {
            $model->initialTransition();
        });
    }

    /**
     * @see \Illuminate\Database\Eloquent\Model::getConnection
     *
     * @return \Illuminate\Database\Connection
     */
    abstract public function getConnection();
}
