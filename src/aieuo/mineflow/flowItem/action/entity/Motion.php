<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\flowItem\argument\EntityArgument;
use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use pocketmine\math\Vector3;
use aieuo\mineflow\libs\_6b4cfdc0a11de6c9\SOFe\AwaitGenerator\Await;

class Motion extends SimpleAction {

    public function __construct(string $entity = "", float $x = 0, float $y = 0, float $z = 0) {
        parent::__construct(self::MOTION, FlowItemCategory::ENTITY);

        $this->setArguments([
            EntityArgument::create("entity", $entity),
            NumberArgument::create("x", $x)->example("2"),
            NumberArgument::create("y", $y)->example("3"),
            NumberArgument::create("z", $z)->example("4"),
        ]);
    }

    public function getEntity(): EntityArgument {
        return $this->getArgument("entity");
    }

    public function getX(): NumberArgument {
        return $this->getArgument("x");
    }

    public function getY(): NumberArgument {
        return $this->getArgument("y");
    }

    public function getZ(): NumberArgument {
        return $this->getArgument("z");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $entity = $this->getEntity()->getOnlineEntity($source);

        $motion = new Vector3(
            $this->getX()->getFloat($source),
            $this->getY()->getFloat($source),
            $this->getZ()->getFloat($source),
        );
        $entity->setMotion($motion);

        yield Await::ALL;
    }
}