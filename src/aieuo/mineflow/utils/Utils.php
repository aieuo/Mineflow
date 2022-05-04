<?php

namespace aieuo\mineflow\utils;

use function count;
use function preg_match_all;
use function stripslashes;

class Utils {
    public static function parseCommandString(string $command): array {
        // https://github.com/pmmp/PocketMine-MP/blob/stable/src/command/SimpleCommandMap.php#L203
        $commands = [];
        preg_match_all('/"((?:\\\\.|[^\\\\"])*)"|(\S+)/u', $command, $matches);
        foreach($matches[0] as $k => $_){
            for($i = 1; $i <= 2; ++$i){
                if($matches[$i][$k] !== ""){
                    $commands[$k] = $i === 1 ? stripslashes($matches[$i][$k]) : $matches[$i][$k];
                    break;
                }
            }
        }
        if (count($commands) === 0) {
            $commands[] = "";
        }
        return $commands;
    }
}