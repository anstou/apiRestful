<?php

namespace App\Modules\Application\Models;

use ApiCore\Library\DataBase\Drive\Mysql\DataBase;
use App\Modules\Application\Library\Enum\PowerStatus;

class PowerControllers extends DataBase
{
    /**
     * PowerControllers数据模型对应表名
     *
     * @var null|string
     */
    protected static ?string $table = 'power_controllers';

    /**
     * 该表中所有字段名
     * SHOW COLUMNS FROM `power_controllers`
     *
     * @var string[]
     */
    protected static array $columns = ['id', 'module_id', 'controller', 'created_at', 'updated_at'];

    /**
     * 添加一个控制器权限
     *
     * @param string $name 控制器名
     * @param int $module_id 所属模块
     * @return false|int
     */
    public static function addController(string $name, int $module_id): false|int
    {
        try {
            $controllerId = self::getControllerId($name);
            if ($controllerId === false)
                return static::insertGetId([
                    'controller' => $name,
                    'module_id' => $module_id
                ]);
        } catch (\Exception $exception) {
            return false;
        }

        return $controllerId;
    }

    /**
     * 获取控制器权限的id
     *
     * @param string $name 控制器名,控制器带命名空间所以是唯一的,不需要带所属模块id条件
     * @return int|false
     */
    public static function getControllerId(string $name): int|false
    {
        $controller = static::selectOne('SELECT id FROM power_controllers WHERE controller=?', [$name]);
        return $controller['id'] ?? false;
    }

    /**
     * 根据id获取控制器
     *
     * @param array $controllerIds
     * @return array
     */
    public static function getControllers(array $controllerIds): array
    {
        $ids = implode(',', $controllerIds);
        $controllers = static::selectOne("SELECT JSON_ARRAYAGG(controller) as controllers FROM power_controllers WHERE id IN ($ids) AND status='NORMAL'");
        $result = json_decode($controllers['controllers'] ?? '[]', true);
        return json_last_error() === JSON_ERROR_NONE ? $result : [];
    }

    /**
     * 删除控制器权限
     *
     * @param int $id
     * @return bool
     */
    public static function deleteController(int $id): bool
    {
        static::delete('id=?', [$id]);
        return true;
    }

    /**
     * 更新控制器状态
     *
     * @param int $controllerId 更改状态的控制器id
     * @param PowerStatus $status NORMAL:可访问 FROZEN:不可访问
     * @return bool
     * @throws \Exception
     */
    public static function updateStatus(int $controllerId, PowerStatus $status = PowerStatus::NORMAL): bool
    {
        static::update(['status' => $status->value], 'id=?', [$controllerId]);
        return true;
    }
}