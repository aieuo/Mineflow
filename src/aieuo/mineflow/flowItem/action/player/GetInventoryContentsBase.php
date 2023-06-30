<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\object\ItemVariable;

abstract class GetInventoryContentsBase extends SimpleAction {

    protected PlayerArgument $player;
    protected StringArgument $resultName;

    public function __construct(
        string $id,
        string $category = FlowItemCategory::PLAYER,
        string $player = "",
        string $resultName = "inventory",
    ) {
        parent::__construct($id, $category);

        $this->setArguments([
            $this->player = new PlayerArgument("player", $player),
            $this->resultName = new StringArgument("inventory", $resultName, "@action.form.resultVariableName", example: "inventory"),
        ]);
    }

    public function getPlayer(): PlayerArgument {
        return $this->player;
    }

    public function getResultName(): StringArgument {
        return $this->resultName;
    }

    public function getAddingVariables(): array {
        return [
            $this->resultName->get() => new DummyVariable(ListVariable::class, ItemVariable::getTypeName())
        ];
    }
}
