<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\object\ItemVariable;

abstract class GetInventoryContentsBase extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    protected PlayerArgument $player;
    protected StringArgument $resultName;

    public function __construct(
        string $id,
        string $category = FlowItemCategory::PLAYER,
        string $player = "",
        string $resultName = "inventory",
    ) {
        parent::__construct($id, $category);

        $this->player = new PlayerArgument("player", $player);
        $this->resultName = new StringArgument("inventory", $resultName, "@action.form.resultVariableName", example: "inventory");
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->player->getName(), "inventory"];
    }

    public function getDetailReplaces(): array {
        return [$this->player->get(), $this->getResultName()];
    }

    public function getResultName(): StringArgument {
        return $this->resultName;
    }

    public function isDataValid(): bool {
        return $this->player->get() !== "" and $this->resultName->isNotEmpty();
    }

    public function getPlayer(): PlayerArgument {
        return $this->player;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->player->createFormElement($variables),
            $this->resultName->createFormElement($variables),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->player->set($content[0]);
        $this->resultName->set($content[1]);
    }

    public function serializeContents(): array {
        return [$this->player->get(), $this->resultName->get()];
    }

    public function getAddingVariables(): array {
        return [
            $this->resultName->get() => new DummyVariable(ListVariable::class, ItemVariable::getTypeName())
        ];
    }
}
