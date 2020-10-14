<?php

namespace Bjuppa\EloquentStateMachine\Support;

use Bjuppa\EloquentStateMachine\Event;
use Bjuppa\EloquentStateMachine\SimpleState;
use DomainException;
use Illuminate\Database\Eloquent\Model;

abstract class SubState extends State
{
    public static string $superStateClass;

    protected State $superState;

    public function __construct(Model $model)
    {
        parent::__construct($model);
        $this->superState = new static::$superStateClass($model);
        if (!$this->superState instanceof SuperState) {
            throw new DomainException(
                get_class() . '::$superStateClass ' . static::$superStateClass . ' must implement' . SuperState::class
            );
        }
    }

    public abstract function exit(Event $event): void;

    protected function dispatchInternal(Event $event): bool
    {
        return parent::dispatchInternal($event) ?: $this->superState->dispatchInternal($event);
    }


    protected function dispatchLocal(Event $event): SimpleState
    {
        return $this->handle($event) ?: $this->dispatchExternal($event);
    }

    private function dispatchExternal(Event $event): SimpleState
    {
        $this->exit($event);
        return $this->superState->dispatchLocal($event);
    }
}
