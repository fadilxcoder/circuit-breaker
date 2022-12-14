<?php

namespace App\Services\EasyBreaker;

use Closure;

class Breaker {

    public string $exception;
    public Closure $closure;

    public function when(string $exception) :Breaker
    {
        $this->exception = $exception;

        return $this;
    }

    public function do(Closure $closure) :Breaker
    {
        $this->closure = $closure;

        return $this;
    }
}
