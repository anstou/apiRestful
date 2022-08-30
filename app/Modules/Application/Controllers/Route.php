<?php

namespace App\Modules\Application\Controllers;

use ApiCore\App;
use ApiCore\Library\ApiRestful\ApiRestful;

class Route extends App
{
    /**
     * 所有路由
     *
     * @return ApiRestful
     */
    protected static function RoutesAction(): ApiRestful
    {
        $routes = \ApiCore\Library\Http\Route\Route::getRoutes();
        return ApiRestful::init(data: ['routes' => $routes]);
    }
}