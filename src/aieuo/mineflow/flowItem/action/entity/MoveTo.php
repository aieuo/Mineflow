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

    private PositionArgument $position;
    private EntityArgument $entity;
    private NumberArgument $speedX;
    private NumberArgument $speedY;
    private NumberArgument $speedZ;

    public function __construct(string $entity = "", string $position = "", float $speedX = 0.1, float $speedY = 0, float $speedZ = 0.1) {
        parent::__construct(self::MOVE_TO, FlowItemCategory::ENTITY);
        $this->setPermissions([FlowItemPermission::LOOP]);

        $this->entity = new EntityArgument("entity", $entity);
        $this->position = new PositionArgument("position", $position);
        $this->speedX = new NumberArgument("speedX", $speedX, example: "0.1", min: 0);
        $this->speedY = new NumberArgument("speedY", $speedY, example: "0", min: 0);
        $this->speedZ = new NumberArgument("speedZ", $speedZ, example: "0.1", min: 0);
    }

    public function getEntity(): EntityArgument {
        return $this->entity;
    }

    public function getPosition(): PositionArgument {
        return $this->position;
    }

    public function getSpeedX(): NumberArgument {
        return $this->speedX;
    }

    public function getSpeedY(): NumberArgument {
        return $this->speedY;
    }

    public function getSpeedZ(): NumberArgument {
        return $this->speedZ;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $entity = $this->entity->getOnlineEntity($source);
        $position = $this->position->getPosition($source);
        $entityPosition = $entity->getLocation();

        $speedX = $this->speedX->getFloat($source);
        $speedY = $this->speedY->getFloat($source);
        $speedZ = $this->speedZ->getFloat($source);

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
