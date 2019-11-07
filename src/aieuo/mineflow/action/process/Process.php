<?php

namespace aieuo\mineflow\action\process;

use aieuo\mineflow\utils\Language;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\action\Action;

abstract class Process implements Action, ProcessIds {

    /** @var string */
    protected $id;

    /** @var string */
    protected $name;
    /** @var string */
    protected $description;
    /** @var string */
    protected $detail;

    /** @var int */
    protected $category;

    /** @var string */
    private $customName;

    /** @var string */
    protected $targetRequired;

    public function getId(): string {
        return $this->id;
    }

    public function getName(): string {
        $name = $this->name;
        if (($name[0] ?? "") === "@") {
            $name = Language::get(substr($name, 1));
        }
        return $name;
    }

    public function getDescription(): string {
        $description = $this->description;
        if (($description[0] ?? "") === "@") {
            $description = Language::get(substr($description, 1));
        }
        return $description;
    }

    public function getDetail(): string {
        $detail = $this->detail;
        if (($detail[0] ?? "") === "@") {
            $detail = Language::get(substr($detail, 1));
        }
        return $detail;
    }

    public function setCustomName(?string $name = null): void {
        $this->customName = $name;
    }

    public function getCustomName(): string {
        return $this->customName ?? $this->getName();
    }

    public function getCategory(): int {
        return $this->category;
    }

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
        if ($process === null) {
            Logger::warning(Language::get("action.not.found", [$content["id"]]));
            return null;
        }

        return $process->parseFromSaveData($content["contents"]);
    }

    /**
     * @param array $content
     * @return Process|null
     */
    abstract public function parseFromSaveData(array $content): ?Process;
}