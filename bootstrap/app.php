<?php

use Illuminate\Foundation\Application;
use App\Http\Middleware\LocaleMiddleware;
use App\Http\Middleware\PreventBackHistory;
use App\Http\Middleware\RedirectIfPermissionDenied;
use App\Http\Middleware\DepartmentPermissionMiddleware;  
use App\Http\Middleware\DepartmentRoleMiddleware; // Add this import
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
  ->withRouting(
    web: __DIR__ . '/../routes/web.php',
    commands: __DIR__ . '/../routes/console.php',
    health: '/up',
  )
  ->withMiddleware(function (Middleware $middleware) {
    $middleware->web(LocaleMiddleware::class);
    $middleware->web(PreventBackHistory::class);
  })
  ->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
        'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
        'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class
    ]);
   
    // Register DepartmentRoleMiddleware here
  })
  ->withExceptions(function (Exceptions $exceptions) {
    //
  })->create();
