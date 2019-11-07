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
     * @return boolean
     */
    abstract public function isDataValid(): bool;

    /**
     * @return array
     */
    abstract public function serializeContents(): array;

    public static function parseFromSaveDataStatic(array $content): ?self {
        $process = ProcessFactory::get($content["id"]);
        if ($process === null) return null;

        return $process->parseFromSaveData($content["contents"]);
    }

    /**
     * @param array $content
     * @return Process|null
     */
    abstract public function parseFromSaveData(array $content): ?Process;
}