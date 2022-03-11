<?php

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\formAPI\element\mineflow\PlayerVariableDropdown;
use aieuo\mineflow\utils\Language;

class SetFood extends FlowItem implements PlayerFlowItem {
    use PlayerFlowItemTrait;

    protected string $name = "action.setFood.name";
    protected string $detail = "action.setFood.detail";
    protected array $detailDefaultReplace = ["player", "food"];

    private string $food;

    public function __construct(string $player = "", string $health = "") {
        parent::__construct(self::SET_FOOD, FlowItemCategory::PLAYER);

        $this->setPlayerVariableName($player);
        $this->food = $health;
    }

    public function setFood(string $health): void {
        $this->food = $health;
    }

    public function getFood(): string {
        return $this->food;
    }

    public function isDataValid(): bool {
        return $this->getPlayerVariableName() !== "" and $this->food !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getPlayerVariableName(), $this->getFood()]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $health = $source->replaceVariables($this->getFood());

        $this->throwIfInvalidNumber($health, 0, 20);

        $entity = $this->getPlayer($source);
        $this->throwIfInvalidPlayer($entity);

        $entity->getHungerManager()->setFood((float)$health);
        yield true;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new PlayerVariableDropdown($variables, $this->getPlayerVariableName()),
            new ExampleNumberInput("@action.setFood.form.food", "20", $this->getFood(), true, 0, 20),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setPlayerVariableName($content[0]);
        $this->setFood($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getPlayerVariableName(), $this->getFood()];
    }
}