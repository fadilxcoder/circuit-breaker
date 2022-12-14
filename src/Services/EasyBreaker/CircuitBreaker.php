<?php

namespace App\Services\EasyBreaker;

use Closure;
use Exception;
use App\Services\EasyBreaker\Breaker;
use App\Services\EasyBreaker\BreakerAggregator;

class CircuitBreaker {

    protected BreakerAggregator $breakerAggregator;

    public function __construct()
    {
        $this->breakerAggregator = new BreakerAggregator();
    }

    public function addBreaker(Breaker $breaker) :CircuitBreaker
    {
        $this->breakerAggregator->add($breaker);

        return $this;
    }

    public function closure(Closure $closure) :?array
    {   
        try {
            return $closure();
        } catch (Exception $e) {
            return $this->saveMyBacon($e);
        }
    }

    protected function saveMyBacon(Exception $exception) :array
    {
        $breakers = $this->breakerAggregator->retrieve($exception);

        return array_map(function($breaker) use($exception) {
            return $breaker->closure->__invoke($exception);
        }, $breakers);
    }
}