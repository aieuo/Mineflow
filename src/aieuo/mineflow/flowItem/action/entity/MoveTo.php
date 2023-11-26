<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\flowItem\argument\EntityArgument;
use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\argument\PositionArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\FlowItemPermission;
use pocketmine\math\Vector3;
use SOFe\AwaitGenerator\Await;

class MoveTo extends SimpleAction {

    public function __construct(string $entity = "", string $position = "", float $speedX = 0.1, float $speedY = 0, float $speedZ = 0.1) {
        parent::__construct(self::MOVE_TO, FlowItemCategory::ENTITY);
        $this->setPermissions([FlowItemPermission::LOOP]);

        $this->setArguments([
            EntityArgument::create("entity", $entity),
            PositionArgument::create("position", $position),
            NumberArgument::create("speedX", $speedX)->min(0)->example("0.1"),
            NumberArgument::create("speedY", $speedY)->min(0)->example("0"),
            NumberArgument::create("speedZ", $speedZ)->min(0)->example("0.1"),
        ]);
    }

    public function getEntity(): EntityArgument {
        return $this->getArguments()[0];
    }

    public function getPosition(): PositionArgument {
        return $this->getArguments()[1];
    }

    public function getSpeedX(): NumberArgument {
        return $this->getArguments()[2];
    }

    public function getSpeedY(): NumberArgument {
        return $this->getArguments()[3];
    }

    public function getSpeedZ(): NumberArgument {
        return $this->getArguments()[4];
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $entity = $this->getEntity()->getOnlineEntity($source);
        $position = $this->getPosition()->getPosition($source);
        $entityPosition = $entity->getLocation();

        $speedX = $this->getSpeedX()->getFloat($source);
        $speedY = $this->getSpeedY()->getFloat($source);
        $speedZ = $this->getSpeedZ()->getFloat($source);

        $dis = $entityPosition->distance($position);
        if ($dis > 1) {
            $x = $speedX * (($position->x - $entityPosition->x) / $dis);
            $y = $speedY * (($position->y - $entityPosition->y) / $dis);
            $z = $speedZ * (($position->z - $entityPosition->z) / $dis);

            $entity->setMotion(new Vector3($x, $y, $z));
        }

        yield Await::ALL;
    }
}
