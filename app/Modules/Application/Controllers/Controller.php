<?php

namespace App\Modules\Application\Controllers;

use ApiCore\App;
use ApiCore\Library\ApiRestful\ApiRestful;
use ApiCore\Library\Command\Command;
use ApiCore\Library\Module\Module as ModuleBase;

class Controller extends App
{

    /**
     * 创建控制器
     *
     * @param string $module_name 模块名
     * @param string $controller_name 控制器名
     * @return ApiRestful
     * @throws \Exception
     */
    protected function CreateAction(string $module_name, string $controller_name): ApiRestful
    {
        if (!Command::dispatch(\ApiCore\Library\Command\Commands\Make\Controller::class, compact('module_name', 'controller_name'))) {
            return new ApiRestful(1, '创建失败');
        }

        return ApiRestful::init();
    }

    /**
     * 删除控制器
     *
     * @param string $module_name 模块名
     * @param string $controller_name 控制器名
     * @return ApiRestful
     */
    protected static function DeleteAction(string $module_name, string $controller_name): ApiRestful
    {
        if (!ModuleBase::hasModule($module_name)) {
            return new ApiRestful(1, '模块不存在');
        }
        $controllerDirname = ModuleBase::getModulePath($module_name . DIRECTORY_SEPARATOR . 'Controllers' . DIRECTORY_SEPARATOR . $controller_name . '.php');
        $filterDirname = ModuleBase::getModulePath($module_name . DIRECTORY_SEPARATOR . 'Filter' . DIRECTORY_SEPARATOR . $controller_name . '.php');
        if (file_exists($controllerDirname)) {
            return new ApiRestful(1, '控制器已存在');
        }

        unlink($controllerDirname);
        unlink($filterDirname);
        return ApiRestful::init();
    }

}