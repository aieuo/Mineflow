<?php

namespace aieuo\mineflow\action\script;

use aieuo\mineflow\action\Action;
use aieuo\mineflow\recipe\Recipe;

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
}