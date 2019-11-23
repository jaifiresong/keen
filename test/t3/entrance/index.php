<?php

require_once __DIR__ . '/../../../src/keen.php';
$config = require_once '../config/config.php';
Dispatcher::createWebApplication($config)->run();