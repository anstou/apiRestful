<?php

namespace App\Modules\Application\Middlewares;

use ApiCore\Library\ApiRestful\ApiCode;
use ApiCore\Library\ApiRestful\ApiRestful;
use ApiCore\Library\InterfaceWarehouse\MiddlewareBase;

class AdminPowerAuth extends MiddlewareBase
{
    protected array $exceptionUrls = [
        '/application/admin/user/knock',//登录
        '/application/admin/user/logout',//退出登录
        '/application/admin/user/has',//token是否有效
    ];

    /**
     * 返回的ApiRestful的data会被控制器request->state->set键对值的方式储存
     *
     * @return ApiRestful
     */
    protected function FilterBeforeHandle(): ApiRestful
    {
        $user = $this->request->state->get('user');
        if (empty($user)) return new ApiRestful(ApiCode::NEED_LOGIN, '请重新登录');
        if (in_array($user['type'], ['SUPER', 'DEVELOPER'])) return new ApiRestful();//开发权限,管理员权限,直接跳过

        foreach ($user['powers'] ?? [] as $power) {
            if (strcasecmp($power['url'], $this->request->URL) === 0
                && (strcasecmp($power['method'], $this->request->getMethod()) === 0 || $power['method'] === 'ALL')
                && strcasecmp($power['status'], 'NORMAL') === 0
            ) {
                return new ApiRestful();
            }
        }

        // TODO: Implement FilterBeforeHandle() method.
        return new ApiRestful(1, '没有权限');
    }
}