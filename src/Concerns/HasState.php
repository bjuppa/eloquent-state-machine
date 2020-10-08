<?php

namespace Bjuppa\EloquentStateMachine\Concerns;

trait HasState
{
    public function dispatchToState($event)
    {
        try {
            //TODO: Wrap in transactionWithRefresh
            $newState = $this->getState()->dispatch($event);
            $this->refresh();
            if (get_class($this->getState()) != get_class($newState)) {
                //TODO: throw UnexpectedStateException
            }
            return $newState;
        } catch (\Exception $e) {
            $this->refresh();
            throw $e;
        }
    }

    protected function makeState($classname)
    {
        return new $classname($this);
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
     * Avoid double transactions
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
}
