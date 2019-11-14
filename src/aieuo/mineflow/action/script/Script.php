<?php

namespace aieuo\mineflow\action\script;

use aieuo\mineflow\utils\Logger;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\recipe\Recipe;

abstract class Script implements ScriptIds {

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

    public function jsonSerialize(): array {
        return [
            "type" => Recipe::CONTENT_TYPE_SCRIPT,
            "id" => $this->getId(),
            "contents" => $this->serializeContents(),
        ];
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

    /**
     * @param array $content
     * @return Script|null
     */
    abstract public function parseFromSaveData(array $content): ?Script;
}