<?php

namespace Bjuppa\EloquentStateMachine\Exceptions;

use RuntimeException;

class UnexpectedStateException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct("Model is not in expected state.");
    }
}
