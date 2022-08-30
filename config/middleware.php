<?php
/**
 * 这里是中间件配置,所有的请求都会经过对应规则中间件
 * 请确保中间件都继承了\App\Middleware::MiddlewareBase
 */

//判断优先级 global > controller_methods > controllers > modules
// 注意这里的controller_methods中method要写方法全名 如 LoginAction 不能只写 Login

//[
//    Middleware::class=>[
//        'global'=>true|false,
//        'controller_methods' => [Controller::class => [method,method2....]|'all'],
//        'controllers' => [
//            Controller::class
//        ],//传入控制器类名 classname::class
//        'modules' => [],//传入模块名:字符串 大小写不敏感
//    ]
//]



return [

];