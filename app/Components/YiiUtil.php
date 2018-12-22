<?php

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;

class Yii {

    public static function log($content, $name) {
        $log_name = strtolower($name);
        if (pathinfo($log_name, PATHINFO_EXTENSION) == 'op') {
            $log_name = substr($log_name, 0, strlen($log_name) - 3);
        }
        pflog($log_name, $content);
    }

}

function pflog($log_name, $log_content, $daily_dir = true) {
    if (!$log_name || !$log_content) {
        return;
    }
    $dir = __DIR__ . '/../../storage/logs';
    if ($daily_dir) {
        $dir .= '/' . date('Ymd');
    }
    $log_name_low = strtolower($log_name);
    if (pathinfo($log_name_low, PATHINFO_EXTENSION) == 'log') {
        $log_name_low = substr($log_name_low, 0, strlen($log_name_low) - 4);
    }
    if (!$log_name_low) {
        return;
    }
    $log = new Logger($log_name_low);
    $stream_handler = new StreamHandler($dir . '/' . $log_name_low . '.log', Logger::INFO);
    $output = "[%datetime%] %level_name%: %message% \n";
    $formatter = new LineFormatter($output, null, true);
    $stream_handler->setFormatter($formatter);
    $log->pushHandler($stream_handler);
    $log->addInfo($log_content);
}
