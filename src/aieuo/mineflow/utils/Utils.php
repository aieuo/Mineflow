<?php

namespace aieuo\mineflow\utils;

use function count;
use function preg_match;
use function preg_match_all;
use function preg_quote;
use function preg_replace;
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

    public static function isValidFileName(string $name): bool {
        return !preg_match("#[.¥/:?<>|*\"]#u", preg_quote($name, "/@#~"));
    }

    public static function getValidFileName(string $name): string {
        return preg_replace("#[.¥/:?<>|*\"]#u", "", $name);
    }

    public static function isValidGroupName(string $name): bool {
        return !preg_match("#[.¥:?<>|*\"]#u", preg_quote($name, "/@#~"));
    }

    public static function getValidGroupName(string $name): string {
        return preg_replace("#[.¥:?<>|*\"]#u", "", $name);
    }

}
