<?php

namespace aieuo\mineflow\utils;

use aieuo\mineflow\Main;

class Logger {

    public static function warning(string $message): void {
        Main::getInstance()->getLogger()->warning($message);
    }
}