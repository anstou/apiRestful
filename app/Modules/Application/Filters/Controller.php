<?php

namespace App\Modules\Application\Filters;

use ApiCore\Library\ApiRestful\ApiRestful;
use ApiCore\Library\InterfaceWarehouse\Filter;

class Controller extends Filter
{
    public bool $authorize = true;

    public static array $rules = [
        'module_name' => [
            'rule' => '/^[A-Z][a-z]+$/',
            'message' => '模块名字错误'
        ],
        'controller_name' => [
            'rule' => '/^[A-Z][a-z]+$/',// ^[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*$
            'message' => '控制器名字错误'
        ]
    ];

    /**
     * 创建控制器的参数过滤
     *
     * @return ApiRestful
     */
    public function CreateFilter(): ApiRestful
    {
        $data = [
            'module_name' => ucfirst(strtolower($this->request->get('module_name', ''))),
            'controller_name' => ucfirst(strtolower($this->request->get('controller_name', '')))
        ];

        return self::check(static::$rules, $data);
    }
    /**
     * @return ApiRestful
     * 创建时间:2022-07-27 02:33:51
     * 结果指向函数:{@link \App\Modules\Application\Controllers\Controller::DeleteAction}
     */
    public function DeleteFilter(): ApiRestful
    {
        $module_name = $this->request->get('module_name');
        $controller_name = $this->request->get('controller_name');

        
        return new ApiRestful(data: compact('module_name','controller_name'));
    }
    
}