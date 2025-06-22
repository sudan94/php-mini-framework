<?php

namespace App\Core;

use DateTime;

class FileLogger extends Logger
{
    protected string $logDir;

    public function __construct(string $logDir = __DIR__."/../../logs")
    {
        $this->logDir = $logDir;
        if(!is_dir($logDir)){
            mkdir($logDir,0775,true);
        }
    }

    public function log(string $level, string $message): void {
        $date = (new DateTime())->format("Y-m-d H:i:s");
        $formatted = "[$date] [$level] $message" . PHP_EOL;

        $file = $level === "ERROR" ? $this->logDir .'/error.log' : $this->logDir. '/app.log';
        file_put_contents($file, $formatted, FILE_APPEND);
    }
}