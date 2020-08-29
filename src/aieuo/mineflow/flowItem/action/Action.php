<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\exception\FlowItemLoadException;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\recipe\Recipe;

abstract class Action extends FlowItem implements ActionIds {

    /** @var string */
    protected $type = Recipe::CONTENT_TYPE_ACTION;

    /* @var ActionContainer */
    private $parent;

    public function parent(ActionContainer $container): self {
        $this->parent = $container;
        return $this;
    }

    public function getParent(): ActionContainer {
        return $this->parent;
    }

    /**
     * @param array $content
     * @return self
     * @throws FlowItemLoadException|\ErrorException
     */
    public static function loadSaveDataStatic(array $content): self {
        $action = ActionFactory::get($content["id"]);
        if ($action === null) {
            throw new FlowItemLoadException(Language::get("action.not.found", [$content["id"]]));
        }

        $action->setCustomName($content["customName"] ?? "");
        return $action->loadSaveData($content["contents"]);
    }

    /**
     * @param array $content
     * @return Action
     * @throws FlowItemLoadException|\ErrorException
     */
    abstract public function loadSaveData(array $content): Action;
}