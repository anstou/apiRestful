<?php

namespace App\Server;

use ApiCore\Library\Command\Command;
use ApiCore\Library\Http\Route\Route;
use ApiCore\Library\InterfaceWarehouse\Facade;


class Bootstrap
{

    /**
     * @return void
     * @throws \Exception
     */
    public static function run(): void
    {
        Facade::loadFacade();
        Route::loadRoutes(true);
        Command::Init([path('app' . DIRECTORY_SEPARATOR . 'Commands')]);
    }

}