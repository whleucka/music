<?php

namespace Echo\Traits\State;

trait SessionProperties
{
    public function __construct()
    {
        $state = $this->getState();
        if (is_null($state)) {
            session()->set($this->state_name, $this->default);
        }
    }

    public function getState(): ?array
    {
        return session()->get($this->state_name);
    }

    public function setState(array $state)
    {
        session()->set($this->state_name, $state);
    }

    public function __set($name, $value): void
    {
        $state = $this->getState();
        $state[$name] = $value;
        $this->setState($state);
    }

    public function __get($name): mixed
    {
        $state = $this->getState();
        return $state[$name];
    }
}
