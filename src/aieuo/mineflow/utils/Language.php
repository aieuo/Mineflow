<?php

namespace aieuo\mineflow\utils;

use aieuo\mineflow\Main;

class Language {

    /** @var string[][] */
    private static $messages = [
        "jpn" => [],
        "eng" => [],
    ];
    /** @var string */
    private static $language = "eng";
    /** @var string */
    private static $fallbackLanguage = "eng";

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
        return array_keys(self::$messages);
    }

    public static function loadBaseMessage(string $language = null): void {
        $language = $language ?? self::$language;
        $owner = Main::getInstance();

        $messages = [];
        foreach ($owner->getResources() as $resource) {
            if ($resource->getFilename() !== $language.".ini") continue;
            $messages = parse_ini_file($resource->getPathname());
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
            $message = str_replace(["\\n", "\\q", "\\dq"], ["\n", "'", "\""], $message);
            return $message;
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
        $text = preg_replace_callback("/@([a-zA-Z.0-9]+)/u", function ($matches) {
            return Language::get($matches[1]);
        }, $text);
        return $text;
    }

    public static function getLoadErrorMessage(string $language): array {
        switch ($language) {
            case "jpn":
                $errors = ["言語ファイルの読み込みに失敗しました", "[".implode(", ", self::getAvailableLanguages())."]が使用できます"];
                break;
            case "eng":
            default:
                $errors = [
                    "Failed to load language file.",
                    "Available languages are: [".implode(", ", self::getAvailableLanguages())."]"
                ];
        }
        return $errors;
    }
}