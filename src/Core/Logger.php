<?php

namespace App\Core;

abstract class Logger{
    abstract public function log(string $level, string $message): void;
}

