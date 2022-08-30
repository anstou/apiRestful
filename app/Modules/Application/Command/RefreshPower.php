<?php

namespace App\Modules\Application\Command;

use ApiCore\Library\Http\Route\Route;
use App\Modules\Application\Models\PowerControllers;
use App\Modules\Application\Models\PowerMethods;
use App\Modules\Application\Models\PowerModules;

/**
 * 刷新权限表组
 */
class RefreshPower
{


    public function run(): void
    {
        try {
            $power = [];

            $routes = Route::getRoutes();
            foreach ($routes as $route) {
                if (!array_key_exists($route->module, $power)) {
                    $power[$route->module] = [];
                }
                if (!array_key_exists($route->controller, $power[$route->module])) {
                    $power[$route->module][$route->controller] = [];
                }
                $power[$route->module][$route->controller][] = $route->controller_method;
            }

            $moduleModel = new PowerModules();
            $controllerModel = new PowerControllers();
            $methodModel = new PowerMethods();
            foreach ($power as $module => $controllerUnits) {

                $moduleId = $moduleModel->addModule($module);
                if (is_numeric($moduleId)) {

                    //循环模块下所有控制器单元
                    foreach ($controllerUnits as $controller => $methods) {

                        $controllerId = $controllerModel->addController($controller, $moduleId);
                        if (is_numeric($controllerId)) {

                            //循环控制器下所有方法
                            foreach ($methods as $method) {

                                $methodModel->addMethod($method, $controllerId);

                            }


                        } else echo "模块:{$module}下的控制器:{$controller}添加失败\r\n";
                    }

                } else echo "模块:{$module}添加失败\r\n";
            }

        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }


}