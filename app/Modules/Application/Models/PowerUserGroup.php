<?php

namespace App\Modules\Application\Models;

use ApiCore\Library\DataBase\Drive\Mysql\DataBase;
use JetBrains\PhpStorm\ArrayShape;

class PowerUserGroup extends DataBase
{
    /**
     * PowerUserGroup数据模型对应表名
     *
     * @var null|string
     */
    protected static ?string $table = 'power_user_group';

    /**
     * 该表中所有字段名
     * SHOW COLUMNS FROM `power_user_group`
     *
     * @var string[]
     */
    protected static array $columns = ['id', 'user_id', 'group_ids', 'module_ids', 'controller_ids', 'method_ids', 'created_at', 'updated_at'];

    /**
     * 添加用户的权限组关联
     *
     * @param int $userId
     * @param int[] $group_ids 所属权限组
     * @param int[] $module_ids 拥有的额外模块访问权限
     * @param int[] $controller_ids 拥有的额外控制器访问权限
     * @param int[] $method_ids 拥有的额外方法访问权限
     * @return bool
     */
    public static function addUserPowerGroupRelation(int   $userId,
                                              array $group_ids,
                                              array $module_ids = [],
                                              array $controller_ids = [],
                                              array $method_ids = []
    ): bool
    {
        try {
            return static::insert([
                'user_id' => $userId,
                'group_ids' => implode(',', $group_ids),
                'module_ids' => implode(',', $module_ids),
                'controller_ids' => implode(',', $controller_ids),
                'method_ids' => implode(',', $method_ids)
            ]);
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * 删除用户的权限组关联
     * 这会导致用户失去所有访问权限
     *
     *
     * @param int $userId
     * @return bool
     */
    public static function deleteUserPowerGroupRelation(int $userId): bool
    {
        static::delete('user_id=?', [$userId]);
        return true;
    }


    /**
     * 修改用户的权限组关联
     * 不存在则会新建
     *
     * @param int $userId 要修改权限的用户id
     * @param int[] $group_ids 所属权限组
     * @param int[] $module_ids 拥有的额外模块访问权限
     * @param int[] $controller_ids 拥有的额外控制器访问权限
     * @param int[] $method_ids 拥有的额外方法访问权限
     * @return bool
     * @throws \Exception
     */
    public static function updateUserPowerGroupRelation(int   $userId,
                                                 array $group_ids,
                                                 array $module_ids = [],
                                                 array $controller_ids = [],
                                                 array $method_ids = []
    ): bool
    {
        if (static::exists('user_id=?', [$userId])) {
            static::update([
                'group_ids' => implode(',', $group_ids),
                'module_ids' => implode(',', $module_ids),
                'controller_ids' => implode(',', $controller_ids),
                'method_ids' => implode(',', $method_ids)], 'user_id=?', [$userId]);
        } else {
            return self::addUserPowerGroupRelation($userId, $group_ids, $module_ids, $controller_ids, $method_ids);
        }
        return true;
    }

    /**
     * 获取指定用户的权限
     *
     * @param int $userId
     * @return array
     */
    #[ArrayShape(['modules' => "array", 'controllers' => "array", 'methods' => "array"])]
    public static function getUserPower(int $userId): array
    {
        $power = ['modules' => [], 'controllers' => [], 'methods' => []];
        $userPower = static::selectOne('SELECT group_ids, module_ids, controller_ids, method_ids FROM power_user_group WHERE user_id=?', [$userId]);
        if (!empty($userPower)) {
            $moduleIds = array_filter(explode(',', $userPower['module_ids'] ?? ''));
            $controllerIds = array_filter(explode(',', $userPower['controller_ids'] ?? ''));
            $methodIds = array_filter(explode(',', $userPower['method_ids'] ?? ''));

            $group_ids = array_filter(explode(',', $userPower['group_ids'] ?? ''));
            if (!empty($group_ids)) {
                $groups = PowerGroups::getGroups($group_ids);
                foreach ($groups as $group) {
                    $moduleIds = array_merge($moduleIds, array_filter(explode(',', $group['module_ids'] ?? '')));
                    $controllerIds = array_merge($controllerIds, array_filter(explode(',', $group['controller_ids'] ?? '')));
                    $methodIds = array_merge($methodIds, array_filter(explode(',', $group['method_ids'] ?? '')));
                }
            }

            if (!empty($moduleIds))
                $power['modules'] = PowerModules::getModules($moduleIds);


            if (!empty($controllerIds))
                $power['controllers'] = PowerControllers::getControllers($controllerIds);

            //只能以原型链形式储存,因为方法名在不同的类中名字一样,储存结构为classname::method
            if (!empty($methodIds))
                foreach (PowerMethods::getMethods($methodIds) as $method) {
                    $controller = array_key_first($method);
                    foreach ($method[$controller] as $name) {
                        $power['methods'][] = $controller . '::' . $name;
                    }
                }

        }
        return $power;
    }
}