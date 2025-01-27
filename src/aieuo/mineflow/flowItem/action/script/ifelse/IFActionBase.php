<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\script\ifelse;

use aieuo\mineflow\flowItem\argument\ActionArrayArgument;
use aieuo\mineflow\flowItem\argument\ConditionArrayArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\editor\ActionArrayEditor;
use aieuo\mineflow\flowItem\editor\ConditionArrayEditor;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\utils\Language;

abstract class IFActionBase extends SimpleAction {

    public function __construct(
        string  $id,
        string  $category = FlowItemCategory::SCRIPT_IF,
        array   $conditions = [],
        array   $actions = [],
        ?string $customName = null
    ) {
        parent::__construct($id, $category);

        $this->setArguments([
            ConditionArrayArgument::create("conditions", $conditions),
            ActionArrayArgument::create("actions", $actions),
        ]);
        $this->setCustomName($customName);
    }

    public function getDetail(): string {
        return Language::get("action.if_base.detail", [$this->getId(), ...$this->getDetailReplaces()]);
    }

    public function getConditions(): ConditionArrayArgument {
        return $this->getArgument("conditions");
    }

    public function getActions(): ActionArrayArgument {
        return $this->getArgument("actions");
    }

    public function getEditors(): array {
        return [
            new ConditionArrayEditor($this->getConditions()),
            new ActionArrayEditor($this->getActions()),
        ];
    }
}