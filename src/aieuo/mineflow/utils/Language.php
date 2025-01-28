<?php

namespace aieuo\mineflow\utils;

use aieuo\mineflow\Main;
use function array_key_last;
use function implode;
use function parse_ini_file;
use const INI_SCANNER_RAW;

class Language {

    /** @var string[][] */
    private static array $messages = [];
    private static string $language = "eng";
    private static string $fallbackLanguage = "eng";

    public static function init(): void {
        $owner = Main::getInstance();

        foreach ($owner->getResources() as $resource) {
            $names = explode("/", str_replace("\\", "/", $resource->getPath()));
            $folderName = $names[array_key_last($names)];

            if ($folderName !== "language" or $resource->getExtension() !== "ini") continue;

            $language = str_replace(".".$resource->getExtension(), "", $resource->getFilename());
            self::loadBaseMessage($language);
        }
    }

    public static function getLanguage(): string {
        return self::$language;
    }

    public static function setLanguage(string $languageName): void {
        self::$language = $languageName;
    }

    public static function setFallbackLanguage(string $languageName): void {
        self::$fallbackLanguage = $languageName;
    }

    public static function isAvailableLanguage(string $languageName): bool {
        return in_array($languageName, self::getAvailableLanguages(), true);
    }

    public static function getAvailableLanguages(): array {
        return array_values(array_filter(array_keys(self::$messages), fn(string $name) => !empty(self::$messages[$name])));
    }

    public static function loadBaseMessage(string $language = null): void {
        $language = $language ?? self::$language;
        $owner = Main::getInstance();

        $messages = [];
        foreach ($owner->getResources() as $resource) {
            if ($resource->getFilename() !== $language.".ini") continue;
            $messages = parse_ini_file($resource->getPathname(), scanner_mode: INI_SCANNER_RAW);
        }
        self::$messages[$language] = $messages;
    }

    public static function add(array $messages, string $language = null): void {
        $language = $language ?? self::$language;
        if (!isset(self::$messages[$language])) self::$messages[$language] = [];
        self::$messages[$language] = array_merge(self::$messages[$language], $messages);
    }

    public static function get(string $key, array $replaces = [], string $language = null): string {
        $language = $language ?? self::$language;
        if (isset(self::$messages[$language][$key])) {
            $message = self::$messages[$language][$key];
            foreach ($replaces as $cnt => $value) {
                if (is_array($value)) $value = self::get($value[0], $value[1] ?? [], $language);
                $message = str_replace("{%".$cnt."}", $value, $message);
            }
            return str_replace(["\\n", "\\q", "\\dq", "\\sc"], ["\n", "'", "\"", ";"], $message);
        }

        if ($language !== self::$fallbackLanguage) {
            return self::get($key, $replaces, self::$fallbackLanguage);
        }

        return $key;
    }

    public static function exists(string $key, string $language = null): bool {
        return isset(self::$messages[$language ?? self::$language][$key]);
    }

    public static function replace(string $text): string {
        return preg_replace_callback("/@([a-zA-Z.0-9_]+)/u", fn($matches) => Language::get($matches[1]), $text);
    }

    public static function getLoadErrorMessage(string $language): array {
        return match ($language) {
            "jpn" => [
                "言語ファイルの読み込みに失敗しました",
                "[".implode(", ", self::getAvailableLanguages())."]が使用できます"
            ],
            "ind" => [
                "Gagal memuat file bahasa.",
                "Bahasa yang tersedia adalah: [".implode(", ", self::getAvailableLanguages())."]"
            ],
            default => [
                "Failed to load language file.",
                "Available languages are: [".implode(", ", self::getAvailableLanguages())."]"
            ],
        };
    }
}