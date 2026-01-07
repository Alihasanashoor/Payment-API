<?php 

declare(strict_types=1);

namespace App\Core;

/**
 * Application Kernel
 *
 * The Kernel is responsible for executing global middleware
 * before the request reaches the controller.
 *
 * Think of it as the "security & bootstrapping gate" of the app.
*/

final class Kernel{
    
    /**
     * List of registered middleware instances.
     *
     * These middleware will be executed in the order
     * they are added.
    */
    private static array $middlewares =[];


    /**
     * Register a middleware to the kernel.
     *
     * This allows global middleware such as:
     * - Authentication
     * - Rate limiting
     * - Logging
     *
     * @param Middleware $middleware
    */

    public static function add(Middleware $middleware):void
    {
     self::$middlewares[] = $middleware;
    }
    
    /**
     * Execute all registered middleware.
     *
     * Each middleware's handle() method is called
     * before the request reaches any controller.
     *
     * If a middleware denies the request (e.g. auth failure),
     * execution will stop immediately.
    */

    public static function run(): void{
        foreach(self::$middlewares as $middleware){
            $middleware->handle();
        }
    }

}
?>