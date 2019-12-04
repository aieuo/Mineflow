<?php

namespace aieuo\mineflow\condition;

use aieuo\mineflow\utils\Logger;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Toggle;

abstract class Condition implements Conditionable, ConditionIds {

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
            "type" => Recipe::CONTENT_TYPE_CONDITION,
            "id" => $this->id,
            "contents" => $this->serializeContents(),
        ];
    }

    /**
     * @return boolean
     */
    abstract public function isDataValid(): bool;

    public function getEditForm(array $default = [], array $errors = []) {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        return ["status" => true, "contents" => [], "cancel" => $data[1], "errors" => []];
    }

    /**
     * @return array
     */
    abstract public function serializeContents(): array;

    public static function parseFromSaveDataStatic(array $content): ?self {
        $condition = ConditionFactory::get($content["id"]);
        if ($condition === null) {
            Logger::warning(Language::get("condition.not.found", [$content["id"]]));
            return null;
        }

        return $condition->parseFromSaveData($content["contents"]);
    }

    /**
     * @param array $content
     * @return Condition|null
     */
    abstract public function parseFromSaveData(array $content): ?Condition;
}