<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\libs\_30a18b127a564f2c\SOFe\AwaitGenerator\Await;

class SetFood extends SimpleAction {

    public function __construct(string $player = "", string $food = "") {
        parent::__construct(self::SET_FOOD, FlowItemCategory::PLAYER);

        $this->setArguments([
            PlayerArgument::create("player", $player),
            NumberArgument::create("food", $food)->min(0)->max(20)->example("20"),
        ]);
    }

    public function getPlayer(): PlayerArgument {
        return $this->getArgument("player");
    }

    public function getFood(): NumberArgument {
        return $this->getArgument("food");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $health = $this->getFood()->getInt($source);
        $entity = $this->getPlayer()->getOnlinePlayer($source);

        $entity->getHungerManager()->setFood((float)$health);

        yield Await::ALL;
    }
}