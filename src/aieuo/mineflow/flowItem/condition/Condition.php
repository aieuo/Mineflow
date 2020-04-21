<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\exception\FlowItemLoadException;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Toggle;

abstract class Condition extends FlowItem implements ConditionIds {

    /** @var string */
    protected $type = Recipe::CONTENT_TYPE_CONDITION;

    public function getEditForm(array $default = [], array $errors = []): Form {
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
     * @param array $content
     * @return self
     * @throws FlowItemLoadException
     * @throws \OutOfBoundsException
     * @throws \InvalidArgumentException
     */
    public static function loadSaveDataStatic(array $content): ?self {
        $condition = ConditionFactory::get($content["id"]);
        if ($condition === null) {
            throw new FlowItemLoadException(Language::get("condition.not.found", [$content["id"]]));
        }

        $condition->setCustomName($content["customName"] ?? "");
        return $condition->loadSaveData($content["contents"]);
    }

    /**
     * @param array $content
     * @return Condition
     * @throws FlowItemLoadException
     * @throws \OutOfBoundsException
     */
    abstract public function loadSaveData(array $content): Condition;

    public function allowDirectCall(): bool {
        return false;
    }
}