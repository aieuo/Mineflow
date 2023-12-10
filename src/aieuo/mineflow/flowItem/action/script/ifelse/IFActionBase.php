<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\script\ifelse;

use aieuo\mineflow\flowItem\argument\ActionArrayArgument;
use aieuo\mineflow\flowItem\argument\ConditionArrayArgument;
use aieuo\mineflow\flowItem\editor\ActionArrayEditor;
use aieuo\mineflow\flowItem\editor\ConditionArrayEditor;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;

abstract class IFActionBase extends FlowItem {

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

    public function getConditions(): ConditionArrayArgument {
        return $this->getArguments()[0];
    }

    public function getActions(): ActionArrayArgument {
        return $this->getArguments()[1];
    }

    public function getDetail(): string {
        return <<<END
            
            §7========§f {$this->getId()} §7========§f
            {$this->getConditions()}
            §7~~~~~~~~~~~~~~~~~~~~~~~~~~~§f
            {$this->getActions()}
            §7================================§f
            END;
    }

    public function getEditors(): array {
        return [
            new ConditionArrayEditor($this->getConditions()),
            new ActionArrayEditor($this->getActions()),
        ];
    }
}
