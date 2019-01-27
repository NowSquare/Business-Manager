<?php

namespace Orchestra\Support;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Validation\Factory;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;

abstract class Validator
{
    use Concerns\Validation;

    /**
     * List of rules.
     *
     * @var array
     */
    protected $rules = [];

    /**
     * List of events.
     *
     * @var array
     */
    protected $events = [];

    /**
     * List of phrases.
     *
     * @var array
     */
    protected $phrases = [];

    /**
     * Create a new Validator instance.
     *
     * @param  \Illuminate\Contracts\Validation\Factory  $factory
     * @param  \Illuminate\Contracts\Events\Dispatcher  $dispatcher
     */
    public function __construct(Factory $factory, Dispatcher $dispatcher)
    {
        $this->validationFactory = $factory;
        $this->validationDispatcher = $dispatcher;
    }

    /**
     * Create a scope scenario.
     *
     * @param  string  $scenario
     * @param  array   $parameters
     *
     * @return $this
     */
    public function on(string $scenario, array $parameters = [])
    {
        return $this->onValidationScenario($scenario, $parameters);
    }

    /**
     * Add bindings.
     *
     * @param  array  $bindings
     *
     * @return $this
     */
    public function bind(array $bindings)
    {
        return $this->bindToValidation($bindings);
    }

    /**
     * Execute validation service.
     *
     * @param  array  $input
     * @param  string|array  $events
     * @param  array   $phrases
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function with(array $input, $events = [], array $phrases = []): ValidatorContract
    {
        return $this->runValidation($input, $events, $phrases);
    }

    /**
     * Get validation events.
     *
     * @return array
     */
    public function getValidationEvents(): array
    {
        return $this->events;
    }

    /**
     * Get validation phrases.
     *
     * @return array
     */
    public function getValidationPhrases(): array
    {
        return $this->phrases;
    }

    /**
     * Get validation rules.
     *
     * @return array
     */
    public function getValidationRules(): array
    {
        return $this->rules;
    }
}
