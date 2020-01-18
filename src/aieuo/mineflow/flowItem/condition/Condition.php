<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\utils\Logger;
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

    public static function loadSaveDataStatic(array $content): ?self {
        $condition = ConditionFactory::get($content["id"]);
        if ($condition === null) {
            Logger::warning(Language::get("condition.not.found", [$content["id"]]));
            return null;
        }

        return $condition->loadSaveData($content["contents"]);
    }

    /**
     * @return boolean
     */
    abstract public function isDataValid(): bool;

    /**
     * @param array $content
     * @return Condition|null
     */
    abstract public function loadSaveData(array $content): ?Condition;
}