<?php
declare(strict_types=1);

namespace aieuo\mineflow\utils;

use pocketmine\command\utils\CommandStringHelper;
use function count;
use function implode;
use function in_array;
use function is_numeric;
use function preg_match;
use function preg_quote;
use function preg_replace;

class Utils {
    public static function parseCommandString(string $command): array {
        $commands = CommandStringHelper::parseQuoteAware($command);
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

    /**
     * @param string $path
     * @return \Iterator<\SplFileInfo>
     */
    public static function getRecipeFiles(string $path): \Iterator {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $path,
                \FilesystemIterator::CURRENT_AS_FILEINFO | \FilesystemIterator::KEY_AS_PATHNAME | \FilesystemIterator::SKIP_DOTS
            )
        );
        return new \RegexIterator($files, '/\.json$/', \RegexIterator::MATCH);
    }

    /**
     * @throws \InvalidArgumentException
     */
    public static function validateNumberString(string|float|int $number, float|int|null $min = null, float|int|null $max = null, array $exclude = []): void {
        if (!is_numeric($number)) {
            throw new \InvalidArgumentException(Language::get("action.error.notNumber", [$number]));
        }
        $number = (float)$number;
        if ($min !== null and $number < $min) {
            throw new \InvalidArgumentException(Language::get("action.error.lessValue", [$min, $number]));
        }
        if ($max !== null and $number > $max) {
            throw new \InvalidArgumentException(Language::get("action.error.overValue", [$max, $number]));
        }
        if (!empty($exclude) and in_array($number, $exclude, false)) {
            throw new \InvalidArgumentException(Language::get("action.error.excludedNumber", [implode(",", $exclude), $number]));
        }
    }

    public static function getInt(string|int $number, ?int $min = null, ?int $max = null, array $exclude = []): int {
        static::validateNumberString($number, $min, $max, $exclude);
        return (int)$number;
    }

    public static function getFloat(string|float $number, ?float $min = null, ?float $max = null, array $exclude = []): float {
        static::validateNumberString($number, $min, $max, $exclude);
        return (float)$number;
    }

}