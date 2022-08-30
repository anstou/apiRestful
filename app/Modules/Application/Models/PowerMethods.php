<?php

namespace App\Modules\Application\Models;

use App\Library\DataBase\Mysql;
use App\Modules\Application\Library\Enum\PowerStatus;

class PowerMethods extends Mysql\DataBase
{
    /**
     * PowerMethods数据模型对应表名
     *
     * @var null|string
     */
    protected static ?string $table = 'power_methods';

    /**
     * 该表中所有字段名
     * SHOW COLUMNS FROM `power_methods`
     *
     * @var string[]
     */
    protected static array $columns = ['id', 'controller_id', 'method', 'created_at', 'updated_at'];

    /**
     * 添加一个方法权限
     *
     * @param string $name 控制器名
     * @param int $controller_id 所属控制器
     * @return false|int
     */
    public static function addMethod(string $name, int $controller_id): false|int
    {
        try {
            $methodId = self::getMethodId($name, $controller_id);
            if ($methodId === false)
                return static::insertGetId([
                    'method' => $name,
                    'controller_id' => $controller_id
                ]);
        } catch (\Exception $exception) {
            return false;
        }
        return $methodId;
    }

    /**
     * 获取方法权限的id
     *
     * @param string $name 方法名,不是唯一的,需要带所属控制器id条件
     * @param int $controllerId 所属控制器id
     * @return int|false
     */
    public static function getMethodId(string $name, int $controllerId): int|false
    {
        $controller = static::selectOne('SELECT id FROM power_methods WHERE method=? AND controller_id=?', [$name, $controllerId]);
        return $controller['id'] ?? false;
    }

    /**
     * 删除方法权限
     *
     * @param int $id
     * @return bool
     */
    public static function deleteMethod(int $id): bool
    {
        static::delete('id=?', [$id]);
        return true;
    }

    /**
     * 更新方法状态
     *
     * @param int $methodId 更改状态的方法id
     * @param PowerStatus $status NORMAL:可访问 FROZEN:不可访问
     * @return bool
     * @throws \Exception
     */
    public static function updateStatus(int $methodId, PowerStatus $status = PowerStatus::NORMAL): bool
    {
        static::update(['status' => $status->value], 'id=?', [$methodId]);
        return true;
    }

    /**
     * 根据id获取方法
     *
     * @param int[] $methodIds
     * @return array [controller=>[method,method2...]]
     */
    public static function getMethods(array $methodIds): array
    {
        $ids = implode(',', $methodIds);
        $sql = <<<SQL
SELECT
 JSON_OBJECT(power_controllers.controller,	JSON_ARRAYAGG(power_methods.method ) ) as methods
FROM `power_methods`
	 INNER JOIN `power_controllers` ON power_controllers.id = power_methods.controller_id
WHERE
	`power_methods`.id IN ({$ids}) AND power_methods.status='NORMAL'
GROUP BY power_controllers.controller
SQL;
        $resultMethods = [];
        $result = static::select($sql);
        foreach ($result as $methodJson) {
            $method = json_decode($methodJson['methods'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $resultMethods[] = $method;
            }
        }
        return $resultMethods;
    }
}