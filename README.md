# State machines for Laravel Eloquent models

This package allows you to treat a Laravel Eloquent model and its relationships as
[extended state](https://en.wikipedia.org/wiki/UML_state_machine#Extended_states)
in a [hierarchical state machine](https://en.wikipedia.org/wiki/UML_state_machine#Hierarchically_nested_states).

The philosophy that sets this package apart from most other state machine implementations is that
behavior is declared **in code** in classes representing *states* and *events*,
and the *model* itself determines the current state.
There is no overall configuration of the state machine's *graph*.
The current state handles incoming events and *transitions* the model into another state.

- Every state and event is represented by a PHP class, extending a relevant base class.
- A state declares the *superstate* (*composite* or *root*) it belongs too.
- Events are dispatched to the current state of the model.
- Event handling happens within database transactions with pessimistic row-locks.
- The current state evaluates an incoming event (*guards*) and may initiate a *transition* to another (named) state.
- Events not explicitly handled by the current state will bubble up the state branch.
- States have *entry* and *exit* actions and events have *actions* that manipulate the model during a transition.
- Side effects can be deferred for processing to after the transition is completed.
- Any anomalies during transitions throws exceptions, triggering transaction rollback.

## Project status

I, Björn Nilsved, created this package back in 2020 for a Laravel app that has been in production since 2021.
The development process of the state machine functionality was rather exploratory and focused on the needs of that specific app.
As the state machine API, base classes, etc were very much in flux, I wrote specific tests in the app itself, and held off writing generic tests for the package until the structure had stabilised.
Of course, once the API was stable and the app was deployed I never got around to writing those tests or documentation for the package as planned...

For my own needs this situation is fine, I'm confident in the test suite of my app.
Should others show interest in this package I'd be willing to put some effort in to set up proper testing and documentation for release of a stable `1.0` version.
Please [get in touch](https://github.com/bjuppa/eloquent-state-machine/issues/5) if this is something you'd like to see happen!

## Requirements

Row-level locking is only supported in MySQL / MariaDB and PostgreSQL.

## Installation

You can install the package via composer:

```bash
composer require bjuppa/eloquent-state-machine
```

## Usage

Start by drawing a *statechart* for your state machine, without it you will probably miss some state, transition, event
or action.

**Everything** related to a specific state in the chart is coded directly into distinct methods of that state's
PHP class.

### Security

If you discover any security related issues, please email nilsved@gmail.com instead of using the issue tracker.

## Credits

- [Björn Nilsved](https://github.com/bjuppa)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
