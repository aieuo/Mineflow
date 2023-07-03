<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use SOFe\AwaitGenerator\Await;

class SetFood extends SimpleAction {

    public function __construct(string $player = "", string $food = "") {
        parent::__construct(self::SET_FOOD, FlowItemCategory::PLAYER);

        $this->setArguments([
            new PlayerArgument("player", $player),
            new NumberArgument("food", $food, example: "20", min: 0, max: 20),
        ]);
    }

    public function getPlayer(): PlayerArgument {
        return $this->getArguments()[0];
    }

    public function getFood(): NumberArgument {
        return $this->getArguments()[1];
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $health = $this->getFood()->getInt($source);
        $entity = $this->getPlayer()->getOnlinePlayer($source);

        $entity->getHungerManager()->setFood((float)$health);

        yield Await::ALL;
    }
}
