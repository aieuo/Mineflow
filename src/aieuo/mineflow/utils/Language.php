<?php

namespace aieuo\mineflow\utils;

use aieuo\mineflow\Main;

class Language {

    /** @var array */
    private static $messages = [];
    /** @var string */
    private static $language = "eng";
    /** @var array */
    private static $availableLanguages = ["jpn", "eng"];

    public static function getLanguage(): string {
        return self::$language;
    }

    public static function setLanguage(string $languageName): void {
        self::$language = $languageName;
    }

    public static function isAvailableLanguage(string $languageName): bool {
        return in_array($languageName, self::$availableLanguages);
    }

    public static function getAvailableLanguages(): array {
        return self::$availableLanguages;
    }


    public static function loadMessage(): bool {
        $owner = Main::getInstance();

        $messages = [];
        foreach ($owner->getResources() as $resource) {
            if ($resource->getFilename() !== self::$language.".ini") continue;
            $messages = parse_ini_file($resource->getPathname());
        }
        self::$messages = $messages;

        return !empty($messages);
    }

    public static function add(array $messages): void {
        self::$messages = array_merge(self::$messages, $messages);
    }

    public static function get(string $key, array $replaces = []): string {
        if (isset(self::$messages[$key])) {
            $message = self::$messages[$key];
            foreach ($replaces as $cnt => $value) {
                if (is_array($value)) $value = self::get($value[0], $value[1] ?? []);
                $message = str_replace("{%".$cnt."}", $value, $message);
            }
            $message = str_replace(["\\n", "\\q", "\\dq"], ["\n", "'", "\""], $message);
            return $message;
        }
        return $key;
    }

    public static function exists(string $key): bool {
        return isset(self::$messages[$key]);
    }

    public static function replace(string $text) {
        $text = preg_replace_callback("/@([a-zA-Z.0-9]+)/", function ($matches) {
            return Language::get($matches[1]);
        }, $text);
        return $text;
    }

    public static function getLoadErrorMessage(string $language): array {
        switch ($language) {
            case "jpn":
                $errors = ["言語ファイルの読み込みに失敗しました", "[".implode(", ", self::$availableLanguages)."]が使用できます"];
                break;
            case "eng":
            default:
                $errors = [
                    "Failed to load language file.",
                    "Available languages are: [".implode(", ", self::$availableLanguages)."]"
                ];
        }
        return $errors;
    }
}