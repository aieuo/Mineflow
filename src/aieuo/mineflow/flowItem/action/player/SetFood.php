<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use SOFe\AwaitGenerator\Await;

class SetFood extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private PlayerArgument $player;

    public function __construct(string $player = "", private string $food = "") {
        parent::__construct(self::SET_FOOD, FlowItemCategory::PLAYER);

        $this->player = new PlayerArgument("player", $player);
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->player->getName(), "food"];
    }

    public function getDetailReplaces(): array {
        return [$this->player->get(), $this->getFood()];
    }

    public function setFood(string $health): void {
        $this->food = $health;
    }

    public function getFood(): string {
        return $this->food;
    }

    public function isDataValid(): bool {
        return $this->player->get() !== "" and $this->food !== "";
    }

    public function getPlayer(): PlayerArgument {
        return $this->player;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $health = $this->getInt($source->replaceVariables($this->getFood()), 0, 20);
        $entity = $this->player->getOnlinePlayer($source);

        $entity->getHungerManager()->setFood((float)$health);

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->player->createFormElement($variables),
            new ExampleNumberInput("@action.setFood.form.food", "20", $this->getFood(), true, 0, 20),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->player->set($content[0]);
        $this->setFood($content[1]);
    }

    public function serializeContents(): array {
        return [$this->player->get(), $this->getFood()];
    }
}
