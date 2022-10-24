<?php

namespace App\Controller;

use Exception;
use App\Core\Controller;
use App\Services\Cache\Redis;
use App\Exceptions\UsersNotFound;
use App\Repository\UsersRepository;
use App\Services\EasyBreaker\Breaker;
use App\Services\EasyBreaker\CircuitBreaker;
use Symfony\Component\HttpFoundation\Request;

class ContentController extends Controller
{
    public function show(Request $request, UsersRepository $usersRepository)
    {
        $users = null;
        $flag = false;
        $key = ['hfx', 'circuit-breaker', 'users'];

        try {
            $users = $usersRepository->getUsers();
            Redis::set($key, $users);
        } catch (Exception $e) {
            $flag = true;
        }
        
        if ($flag) {
            $cacheUserCircuitBreaker = (new Breaker)
                ->when(UsersNotFound::class)
                ->do(function() use ($key) {
                    return Redis::get($key);
                });

            $results = (new CircuitBreaker())
                ->addBreaker($cacheUserCircuitBreaker)
                ->closure(function(){
                    throw new UsersNotFound();
                });
            dump('Exception raised to activate(flag) circuit breaker !');
            $users = $results[0];
        }
        
        return $this->render('content/index.html.twig', [
            'users' => $users
        ]);
    }
}