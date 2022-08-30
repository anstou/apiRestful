<?php

namespace App\Modules\Application\Controllers;

use ApiCore\App;
use ApiCore\Library\ApiRestful\ApiRestful;
use ApiCore\Library\DataBase\Drive\Mysql\DataBase;
use ApiCore\Library\Module\Module as ModuleBase;

/**
 * 容易混淆的变量:
 *
 * $module_name => 模块名
 * $model_name  => 模型名
 *
 */
class Model extends App
{

    /**
     * 创建数据模型
     * 需要数据库中该表已存在
     *
     * @param string $module_name 要创建的模型的模块
     * @param string $model_name 要创建的模型名
     * @return ApiRestful
     * @throws \Exception
     */
    protected function CreateAction(string $module_name, string $model_name): ApiRestful
    {
        $ModelDirname = ModuleBase::getModulePath($module_name . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . ucfirst($model_name) . '.php');
        if (file_exists($ModelDirname)) {
            return new ApiRestful(1, '模型文件已存在');
        }

        //根据模型名转化为表明AaaBbb->aaa_bbb
        $table_name = preg_replace_callback('/([A-Z]+)/', function ($matchs) {
            return '_' . strtolower($matchs[0]);
        }, $model_name);

        //取到表中的所有字段名
        $statement = DataBase::PDO()->query("SHOW COLUMNS FROM `$table_name`");
        $columns = $statement->fetchAll(\PDO::FETCH_ASSOC);
        if (empty($columns)) return new ApiRestful(1, $table_name . '表取字段失败');
        $names = [];
        foreach ($columns as $column) {
            $names[] = $column['Field'];
        }
        $table_columns = '\'' . implode('\', \'', $names) . '\'';


        $ModelTemplatePath = module_path('Application' . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'model.template');
        $ModelCodeStr = file_get_contents($ModelTemplatePath);
        $ModelCodeStr = str_replace(['{module_name}', '{model_name}', '{table_name}', '{table_columns}'], [$module_name, ucfirst($model_name), $table_name, $table_columns,], $ModelCodeStr);
        try {
            filePutContents($ModelDirname, $ModelCodeStr);
        } catch (\Exception $exception) {
            return new ApiRestful(1, '文件写入失败');
        }

        return new ApiRestful();
    }
}