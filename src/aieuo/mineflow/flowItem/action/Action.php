<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\exception\FlowItemLoadException;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Toggle;

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
    public static function loadSaveDataStatic(array $content): self {
        $process = ActionFactory::get($content["id"]);
        if ($process === null) {
            throw new FlowItemLoadException(Language::get("action.not.found", [$content["id"]]));
        }

        return $process->loadSaveData($content["contents"]);
    }

    /**
     * @param array $content
     * @return Action
     * @throws FlowItemLoadException
     * @throws \OutOfBoundsException
     */
    abstract public function loadSaveData(array $content): Action;
}