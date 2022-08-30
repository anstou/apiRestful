<?php

use ApiCore\Library\Cache\FileStorage;
use ApiCore\Library\Log\LogManger;

return [
    'Cache' => FileStorage::class,
    'Log' => LogManger::class
];