<?php

use ApiCore\Facades\Log;
use ApiCore\Library\ApiRestful\ApiCode;
use ApiCore\Library\Command\Command;
use ApiCore\Library\Http\Response;
use App\Server\Bootstrap;

date_default_timezone_set('Asia/Shanghai');
define("START_TIME", $_SERVER['REQUEST_TIME_FLOAT'] * 1000);
define('APP_BASE_PATH', dirname(__FILE__));
include APP_BASE_PATH . '/vendor/autoload.php';

error_reporting(E_ERROR);
register_shutdown_function(function () {
    $e = error_get_last();
    if (!is_null($e)) {
        $msg = match ($e['type']) {
            E_ERROR, E_COMPILE_ERROR => '致命的运行时错误。',
            default => '其它的致命错误'
        };;
        Log::error($e['message'], [$e['file'], $e['line']]);
        Response::response($msg, ApiCode::UNEXPECTED_ERROR, ['error' => $e]);
    }
});


try {
    Bootstrap::run();
    Command::CommandAutoHandle();
} catch (Throwable $exception) {

    if (is_string($exception->getCode())) {
        Response::response('意料外的错误', ApiCode::UNEXPECTED_ERROR);
    }

    Response::response('意料外的错误:' . $exception->getMessage(), $exception->getCode());

}