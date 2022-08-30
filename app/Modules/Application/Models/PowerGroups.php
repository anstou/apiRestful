<?php

namespace App\Modules\Application\Models;

use ApiCore\Library\DataBase\Drive\Mysql\DataBase;
use App\Modules\Application\Library\Enum\PowerStatus;

class PowerGroups extends DataBase
{
    /**
     * PowerGroups数据模型对应表名
     *
     * @var null|string
     */
    protected static ?string $table = 'power_groups';

    /**
     * 该表中所有字段名
     * SHOW COLUMNS FROM `power_groups`
     *
     * @var string[]
     */
    protected static array $columns = ['id', 'group_name', 'module_ids', 'controller_ids', 'method_ids', 'created_at', 'updated_at'];


    /**
     * 添加一个组
     *
     * @param string $name
     * @param int[] $module_ids
     * @param int[] $controller_ids
     * @param int[] $method_ids
     * @return bool
     */
    public static function addGroup(string $name, array $module_ids = [], array $controller_ids = [], array $method_ids = []): bool
    {
        try {
            return static::insert([
                'group_name' => $name,
                'module_ids' => implode(',', $module_ids),
                'controller_ids' => implode(',', $controller_ids),
                'method_ids' => implode(',', $method_ids)
            ]);
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * 删除一个组
     *
     * @param int $groupId
     * @return bool
     */
    public static function deleteGroup(int $groupId): bool
    {
        static::delete('id=?', [$groupId]);
        return true;
    }

    /**
     * 更新一个权限组的权限
     *
     * @param int $groupId
     * @param int[] $module_ids
     * @param int[] $controller_ids
     * @param int[] $method_ids
     * @return bool
     * @throws \Exception
     */
    public static function updateGroup(int $groupId, array $module_ids = [], array $controller_ids = [], array $method_ids = []): bool
    {
        $update = [
            'module_ids' => implode(',', $module_ids),
            'controller_ids' => implode(',', $controller_ids),
            'method_ids' => implode(',', $method_ids)
        ];
        static::update($update, 'id=?', [$groupId]);
        return true;
    }


    /**
     * 更新组 状态
     *
     * @param int $groupId 组id
     * @param PowerStatus $status NORMAL:可访问 FROZEN:不可访问
     * @return bool
     * @throws \Exception
     */
    public static function updateStatus(int $groupId, PowerStatus $status = PowerStatus::NORMAL): bool
    {
        static::update(['status' => $status->value], 'id=?', [$groupId]);
        return true;
    }

    /**
     * 根据id获取组
     *
     * @param array $groupIds
     * @return array
     */
    public static function getGroups(array $groupIds): array
    {
        $ids = implode(',', $groupIds);
        return static::select("SELECT group_name,module_ids,controller_ids,method_ids FROM power_groups WHERE id IN ($ids) AND status='NORMAL'");
    }
}