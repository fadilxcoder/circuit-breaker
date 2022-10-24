<?php

namespace App\Services\EasyBreaker;

use Exception;
use Illuminate\Support\Collection;
use App\Services\EasyBreaker\Breaker;
use App\Services\EasyBreaker\BreakerAggregator;

class BreakerAggregator
{
    protected Collection $collection;

    public function __construct()
    {
        $this->collection = new Collection;
    }

    public function add(Breaker $breaker) :BreakerAggregator
    {
        $this->collection->push($breaker);

        return $this;
    }    

    public function retrieve(Exception $exception) :array
    {
        return $this->collection->filter(function($breaker) use($exception) {
            return $breaker->exception === get_class($exception);
        })->values()->all();
    }       
}