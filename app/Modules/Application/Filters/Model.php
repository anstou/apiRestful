<?php

namespace App\Modules\Application\Filters;

use ApiCore\Library\ApiRestful\ApiRestful;
use ApiCore\Library\InterfaceWarehouse\Filter;

class Model extends Filter
{
    public bool $authorize = true;

    public static array $rules = [
        'module_name' => [
            'rule' => '/^[A-Z][a-z]+$/',
            'message' => '模块名字错误'
        ],
        'model_name' => [
            'rule' => '/^[a-zA-Z]+$/',
            'message' => '模型名字错误'
        ]
    ];


    /**
     * @return ApiRestful
     */
    public function CreateFilter(): ApiRestful
    {
        $data = [
            'module_name' => ucfirst(strtolower($this->request->get('module_name', ''))),
            'model_name' => lcfirst($this->request->get('model_name', ''))
        ];

        return self::check(static::$rules, $data);
    }

}