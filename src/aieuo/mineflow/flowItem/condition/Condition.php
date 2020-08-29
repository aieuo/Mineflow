<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\exception\FlowItemLoadException;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\recipe\Recipe;

abstract class Condition extends FlowItem implements ConditionIds {

    /** @var string */
    protected $type = Recipe::CONTENT_TYPE_CONDITION;

    /**
     * @param array $content
     * @return self
     * @throws FlowItemLoadException|\ErrorException
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
     * @throws FlowItemLoadException|\ErrorException
     */
    abstract public function loadSaveData(array $content): Condition;
}