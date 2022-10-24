# Notes 

- https://github.com/DeGraciaMathieu/php-easy-breaker (PHP implementation of circuit breaker pattern)
- Integrate codebase in project itself **without** using composer

```php

require __DIR__.'/vendor/autoload.php';

use Exception;
use DeGraciaMathieu\EasyBreaker\Breaker;
use DeGraciaMathieu\EasyBreaker\CircuitBreaker;

$firstBreaker = (new Breaker)
    ->when(Exception::class)
    ->do(function(Exception $e){
        return "it's broken.";
    });

$secondBreaker = (new Breaker)
    ->when(Exception::class)
    ->do(function(Exception $e){
        return "really broken.";
    });

$thirdBreaker = (new Breaker)
    ->when(AnotherException::class)
    ->do(function(AnotherException $e){
        return "boom.";
    });

$results = (new CircuitBreaker())
    ->addBreaker($firstBreaker)
    ->addBreaker($secondBreaker)
    ->addBreaker($thirdBreaker)
    ->closure(function(){
        throw new Exception();
    });

var_dump($results);

// array(2) {
//   [0]=>
//   string(12) "it's broken."
//   [1]=>
//   string(18) "really broken."
// }

```

**Actual Code**

```php

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

```

```php

class UsersRepository extends Repository
{
    public function getUsers()
    {
        // throw new \Exception(); # <-- Raise exception to activate circuit breaker

        $query = '

```