<?php

namespace aieuo\mineflow\action\script;

use aieuo\mineflow\utils\Logger;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\action\Action;

abstract class Script extends Action implements ScriptIds {

    public function jsonSerialize(): array {
        return [
            "type" => Recipe::CONTENT_TYPE_SCRIPT,
            "id" => $this->getId(),
            "contents" => $this->serializeContents(),
        ];
    }

    /**
     * @return array
     */
    abstract public function serializeContents(): array;

    public static function parseFromSaveDataStatic(array $content): ?self {
        $script = ScriptFactory::get($content["id"]);
        if ($script === null) {
            Logger::warning(Language::get("action.not.found", [$content["id"]]));
            return null;
        }

        return $script->parseFromSaveData($content["contents"]);
    }
}