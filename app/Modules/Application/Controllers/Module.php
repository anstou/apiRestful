<?php

namespace App\Modules\Application\Controllers;

use ApiCore\App;
use ApiCore\Library\ApiRestful\ApiRestful;
use ApiCore\Library\Module\Module as ModuleBase;

/**
 * 模块管理
 */
class Module extends App
{
    /**
     * 列出项目内所有模块
     *
     * @return ApiRestful
     */
    protected static function ModulesAction(): ApiRestful
    {
        $data = [];
        $modules = ModuleBase::getAllModules();
        foreach ($modules as $module) {
            $data['modules'][$module] = [
                'isRegister' => ModuleBase::hasRegisterModule($module),
                'isOpen' => ModuleBase::AuthModule($module)
            ];
        }
        return ApiRestful::init(data: $data);
    }

    /**
     * 创建模块
     *
     * @param string $module_name
     * @return ApiRestful
     */
    protected static function CreateAction(string $module_name): ApiRestful
    {
        if (ModuleBase::hasModule($module_name)) {

            return ApiRestful::init(message: '模块已存在,请检查Modules目录');
        }

        $dirArr = [
            '控制器类' => 'Controllers',
            '数据库操作类' => 'Models',
            '配置文件' => 'Config',
            '控制器方法过滤类' => 'Filters',
            '模块类库' => 'Library',
            '资源库' => 'resource',
            '命令行' => 'Commands',
            '中间件' => 'Middlewares',
        ];
        $pathArr = [path("app/Modules/$module_name")];
        array_map(function ($name) use ($module_name, &$pathArr) {
            $pathArr[] = path("app/Modules/$module_name/$name");
        }, $dirArr);

        $oldUmask = umask();
        /*
            0 – 读, 写, 可执行 (rwx)
            1 – 读和写 (rw-)
            2 – 读和可执行 (r-x)
            3 – 只读 (r--)
            4 – 写和可执行 (-wx)
            5 – 只写 (-w-)
            6 – 仅可执行 (--x)
            7 – 没有权限 (---)
        */
        umask(0002);//0-rwx-rwx-rx
        //Linux的umask默认值是0022，
        //所以php 的 mkdir 函数只能建立出0755[0-rwx-rx-rx]权限的文件夹出来，
        //修改umask0002则可以建立0775。
        $r = 0;
        array_map(function ($dirName) use (&$r) {
            $r += mkdir($dirName, 0775, true) ? 1 : 0;
        }, $pathArr);

        //创建模块的初始化类
        //项目导入这个模块的时候要调用一次
        $libraryPathName = path("app/Modules/$module_name/Library");
        if (is_dir($libraryPathName)) {
            $controllerTemplatePath = module_path('Application' . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'init.template');
            if ($strCode = file_get_contents($controllerTemplatePath)) {
                $initCode = str_replace(['{module_name}'], [$module_name], $strCode);
                filePutContents($libraryPathName . DIRECTORY_SEPARATOR . 'Init.php', $initCode);
            }
        }

        umask($oldUmask);

        if ($r === count($pathArr)) {
            return ApiRestful::init(message: "{$module_name}模块创建成功");
        } else {
            return ApiRestful::init(1, "{$module_name}模块创建异常");
        }

    }

    /**
     * 删除模块
     * !!危险操作!!
     *
     * @param string $module_name 要删除的模块名
     * @return ApiRestful
     */
    protected static function DeleteAction(string $module_name): ApiRestful
    {
        if (ModuleBase::hasRegisterModule($module_name)) {
            return ApiRestful::init(1, '模块已注册,需要执行卸载操作才能删除模块');
        }
        if (!ModuleBase::hasModule($module_name)) {
            return ApiRestful::init(1, '要删除的模块不存在');
        }
        $modulePath = path("app/Modules/{$module_name}");
        if (is_writable($modulePath)) {
            if (deleteDir($modulePath)) {
                return ApiRestful::init(message: '模块删除成功');
            } else {
                return ApiRestful::init(1, '模块删除失败');
            }
        } else {
            $p = substr(sprintf('%o', fileperms($modulePath)), -4);
            return ApiRestful::init(1, '没有该模块的目录操作权限,请确定权限.当前权限为:' . $p);
        }

    }

    /**
     * 注册模块
     * 将模块注册到modules.json文件中,但是默认不会开启访问
     *
     * @param string $module_name
     * @param bool $open
     * @return ApiRestful
     */
    protected static function RegisterAction(string $module_name, bool $open = false): ApiRestful
    {
        $moduleJsonPathname = config_path('modules.json');
        if (is_writable($moduleJsonPathname)) {
            $json = file_get_contents($moduleJsonPathname);
            if ($json === false) {
                return ApiRestful::init(1, 'modules.json配置文件读取失败');
            } else {
                $modules = json_decode($json, true);
                $modules[$module_name] = $open;
                if (filePutContents($moduleJsonPathname, json_encode($modules)) === false) {
                    return ApiRestful::init(1, 'modules.json配置文件写入失败');
                } else {
                    return ApiRestful::init(message: '操作成功');
                }
            }
        } else {
            return ApiRestful::init(1, '没有modules.json配置文件的写入权限,请检查.');
        }
    }

    /**
     * 卸载模块
     * 并不会删除模块文件,只会将/config/modules.json中移除
     *
     * @param string $module_name
     * @return ApiRestful
     */
    protected static function UnregisterAction(string $module_name): ApiRestful
    {
        $moduleJsonPathname = config_path('modules.json');
        if (is_writable($moduleJsonPathname)) {
            $json = file_get_contents($moduleJsonPathname);
            if ($json === false) {
                return ApiRestful::init(1, 'modules.json配置文件读取失败');
            } else {
                $modules = json_decode($json, true);
                unset($modules[$module_name]);
                if (filePutContents($moduleJsonPathname, json_encode($modules)) === false) {
                    return ApiRestful::init(1, 'modules.json配置文件写入失败');
                } else {
                    return ApiRestful::init(message: '卸载成功');
                }
            }
        } else {
            return ApiRestful::init(1, '没有modules.json配置文件的写入权限,请检查.');
        }
    }

    /**
     * 开启模块访问,
     * 如果模块没有注册则会直接注册到/config/modules.json文件中
     *
     * @param string $module_name
     * @return ApiRestful
     */
    protected static function OpenAction(string $module_name): ApiRestful
    {
        //注册默认是false,将true传入后逻辑一致
        return static::registerAction($module_name, true);
    }

    /**
     * 关闭模块访问,如果模块没有注册则会直接注册
     * 如果模块没有注册则会直接注册到/config/modules.json文件中
     *
     * @param string $module_name
     * @return ApiRestful
     */
    protected static function CloseAction(string $module_name): ApiRestful
    {
        //注册默认就是false,逻辑一致
        return static::registerAction($module_name);
    }
}