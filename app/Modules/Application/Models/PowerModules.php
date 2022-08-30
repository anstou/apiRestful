<?php

namespace App\Modules\Application\Models;

use App\Library\DataBase\Mysql;
use App\Modules\Application\Library\Enum\PowerStatus;

class PowerModules extends Mysql\DataBase
{
    /**
     * PowerModules数据模型对应表名
     *
     * @var null|string
     */
    protected static ?string $table = 'power_modules';

    /**
     * 该表中所有字段名
     * SHOW COLUMNS FROM `power_modules`
     *
     * @var string[]
     */
    protected static array $columns = ['id', 'module', 'status', 'created_at', 'updated_at'];

    /**
     * 添加一个模块权限
     *
     * @param string $name 模块名
     * @param PowerStatus $status
     * @return false|int
     */
    public static function addModule(string $name, PowerStatus $status = PowerStatus::NORMAL): false|int
    {
        try {
            $moduleId = self::getModuleId($name);
            if ($moduleId === false)
                return static::insertGetId([
                    'module' => $name,
                    'status' => $status->value,
                ]);
        } catch (\Exception $exception) {
            return false;
        }
        return $moduleId;
    }

    /**
     * 获取模块权限的id
     *
     * @param string $name 模块名,唯一的
     * @return int|false
     */
    public static function getModuleId(string $name): int|false
    {
        $module = static::selectOne('SELECT id FROM power_modules WHERE module=?', [$name]);
        return $module['id'] ?? false;
    }

    /**
     * 根据id获取模块
     *
     * @param array $moduleIds
     * @return array
     */
    public static function getModules(array $moduleIds): array
    {
        $ids = implode(',', $moduleIds);
        $modules = static::selectOne("SELECT JSON_ARRAYAGG(module) as modules FROM power_modules WHERE id IN ($ids) AND status='NORMAL'");
        $result = json_decode($modules['modules'] ?? '[]', true);
        return json_last_error() === JSON_ERROR_NONE ? $result : [];
    }


    /**
     * 删除模块权限
     *
     * @param int $id
     * @return bool
     */
    public static function deleteModule(int $id): bool
    {
        static::delete('id=?', [$id]);
        return true;
    }

}