<?php
session_start();
session_destroy();
$basePath = rtrim(getenv('APP_BASE_PATH') ?: '/kantinsakina', '/');
header('Location: ' . $basePath . '/login');
exit();
