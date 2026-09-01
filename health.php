<?php
require __DIR__ . '/Database/connect.php';

$result = mysqli_query($conn, 'SELECT 1');
if (!$result) {
    http_response_code(503);
    exit('unhealthy');
}

header('Content-Type: text/plain; charset=utf-8');
echo 'healthy';
