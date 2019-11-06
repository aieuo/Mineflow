<?php

namespace aieuo\mineflow\action\process;

use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\action\Action;

abstract class Process extends Action implements ProcessIds {

    /** @var string */
    protected $targetRequired;

    public function jsonSerialize(): array {
        return [
            "type" => Recipe::CONTENT_TYPE_PROCESS,
            "id" => $this->getId(),
            "contents" => $this->serializeContents(),
        ];
    }

    /**
     * @return array
     */
    abstract public function serializeContents(): array;
}