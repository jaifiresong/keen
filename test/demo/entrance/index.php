<?php

require_once __DIR__ . '/../../keen/keen.php';
$config = require_once '../config/config.php';
Dispatcher::createWebApplication($config)->run();