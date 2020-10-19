<?php

namespace Bjuppa\EloquentStateMachine\Eloquent;

use Bjuppa\EloquentStateMachine\StateEvent;
use Bjuppa\EloquentStateMachine\Exceptions\UnexpectedStateException;
use Bjuppa\EloquentStateMachine\ModelCreatedStateEvent;
use Bjuppa\EloquentStateMachine\RootState;
use Bjuppa\EloquentStateMachine\SimpleState;
use Bjuppa\EloquentStateMachine\Support\State;
use DomainException;
use Illuminate\Database\Connection;
use InvalidArgumentException;
use LogicException;
use Throwable;

trait HasState
{
    use CanLockPessimistically;

    /**
     * Classname of the root state for the state machine of this model.
     */
    protected string $rootStateClass;

    /**
     * Conditions determining the state this model is currently in.
     *
     * Call $this->makeState() passing the classname of the current state and return it.
     *
     * @throws \Throwable
     */
    abstract public function getState(): SimpleState;

    /**
     * Override this to swap out the default event passed to the root state when models are created.
     *
     * @see Bjuppa\EloquentStateMachine\ModelCreatedStateEvent
     */
    protected function initialTransitionEvent(): ModelCreatedStateEvent
    {
        return new ModelCreatedStateEvent($this);
    }

    /**
     * This is the primary interaction point with the state machine.
     *
     * Instantiate an event class representing whatever happened in the outside world
     * and pass it in for processing by the state machine.
     *
     * This model will be refreshed from storage and manipulated by the state machine within a transaction.
     *
     * @return SimpleState The committed state the model is in after any transitions.
     *
     * @throws \Throwable
     */
    public function dispatchToState(StateEvent $event): SimpleState
    {
        if ($this->isDirty()) {
            throw new LogicException('Model should not be dirty when dispatching event to state');
        }
        try {
            return $this->transactionWithRefreshForUpdate(function () use ($event) {
                return tap(
                    $this->getState()->dispatch($event),
                    // Validate the new state and process side effects before committing the transaction
                    function (State $destination) use ($event) {
                        $this->assertStateAfterEvent($destination, $event);
                        $event->processSideEffects();
                    }
                );
            });
        } catch (Throwable $e) {
            // If transaction was aborted, make sure this model matches the database
            $this->refresh();
            throw $e;
        }
    }

    protected function makeState(string $classname): State
    {
        if (!is_a($classname, State::class)) {
            throw new InvalidArgumentException(
                $classname . ' is not a ' . State::class
            );
        }
        return new $classname($this);
    }

    protected function rootState(): RootState
    {
        if (!is_a($this->rootStateClass, RootState::class)) {
            throw new DomainException(
                get_class($this) . '::rootStateClass ' . $this->rootStateClass . ' is not a ' . RootState::class
            );
        }
        return $this->makeState($this->rootStateClass);
    }

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

    protected static function bootHasState()
    {
        static::created(function ($model) {
            $model->initialTransition();
        });
    }

    abstract public function getConnection(): Connection;
}
