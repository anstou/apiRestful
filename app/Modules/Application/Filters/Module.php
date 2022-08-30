<?php

namespace App\Modules\Application\Filters;

use ApiCore\Library\ApiRestful\ApiRestful;
use ApiCore\Library\InterfaceWarehouse\Filter;

/**
 * 对应Controllers下Module类中的每一个Action
 * 没有对应则不会被调用
 */
class Module extends Filter
{
    /**
     * 检查模型名字
     *
     * @return ApiRestful
     */
    private function checkModuleName(): ApiRestful
    {
        $moduleName = ucfirst(strtolower($this->request->get('module_name', '')));
        if (preg_match('/^[A-Z][a-z]+$/', $moduleName) > 0) {
            return ApiRestful::init(data: ['module_name' => $moduleName]);
        }
        return ApiRestful::init(1, '模块名字错误,需要被 /^[A-Z][a-z]+$/ 匹配');
    }

    public function CreateFilter(): ApiRestful
    {
        return static::checkModuleName();
    }

    public function DeleteFilter(): ApiRestful
    {
        return static::checkModuleName();

    }

    public function registerFilter(): ApiRestful
    {
        return static::checkModuleName();

    }

    public function unregisterFilter(): ApiRestful
    {
        return static::checkModuleName();
    }

    public function openFilter(): ApiRestful
    {
        return static::checkModuleName();
    }

    public function closeFilter(): ApiRestful
    {
        return static::checkModuleName();
    }

}