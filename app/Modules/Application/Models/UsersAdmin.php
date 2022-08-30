<?php

namespace App\Modules\Application\Models;

use ApiCore\Library\DataBase\Drive\Mysql\DataBase;

class UsersAdmin extends DataBase
{

    /**
     * UsersAdmin数据模型对应表名
     * @var null|string
     */
    protected static ?string $table = 'users_admin';
    
    /**
     * 该表中所有字段名
     * SHOW COLUMNS FROM `users_admin`
     *
     * @var string[]
     */
    protected static array $columns = ['id', 'user_id', 'admin_account', 'admin_password', 'admin_status', 'admin_type', 'power_ids', 'des', 'created_at', 'updated_at'];


    /**
     * 验证账号
     * 不会生成token
     *
     * @param string $account
     * @param string $password
     * @return array [id,account,status,type,powers]|[]
     */
    public function auth(string $account, string $password): array
    {
        $select = 'id,admin_account, admin_password, admin_status, admin_type, power_ids';
        $user = parent::selectOne("SELECT $select FROM users_admin WHERE admin_account=?", [$account]);
        if (empty($user)) return [];
        $up = sha1(sha256($password));
        if (strcasecmp($up, $user['admin_password']) === 0) {
            $powerIds = empty($user['power_ids']) ? [] : json_decode($user['power_ids'], true);
            $powers = [];
            if (!empty($powerIds)) {
                $place = $this->getPlaceholder(count($powerIds));
                $powers = parent::select("SELECT url,status,method FROM admin_power WHERE id IN ($place)", $powerIds);
            }
            return [
                'id' => $user['id'],
                'account' => $user['admin_account'],
                'status' => $user['admin_status'],
                'type' => $user['admin_type'],
                'powers' => $powers,
                'pwd_hash' => $up,
            ];
        }
        return [];
    }


    /**
     * 修改密码
     *
     * @param int $adminId
     * @param string $password
     * @return bool
     */
    public function changePassword(int $adminId, string $password): bool
    {
        $up = sha1(sha256($password));
        try {
            return self::update(['admin_password' => $up], 'id=?', [$adminId]) > 0;
        } catch (\Exception $e) {
        }
        return false;
    }
}