<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\flowItem\argument\EntityArgument;
use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use pocketmine\math\Vector3;
use SOFe\AwaitGenerator\Await;

class Motion extends SimpleAction {

    private EntityArgument $entity;
    private NumberArgument $x;
    private NumberArgument $y;
    private NumberArgument $z;

    public function __construct(string $entity = "", float $x = 0, float $y = 0, float $z = 0) {
        parent::__construct(self::MOTION, FlowItemCategory::ENTITY);

        $this->entity = new EntityArgument("entity", $entity);
        $this->x = new NumberArgument("x", $x, example: "2");
        $this->y = new NumberArgument("y", $y, example: "3");
        $this->z = new NumberArgument("z", $z, example: "4");
    }

    public function getEntity(): EntityArgument {
        return $this->entity;
    }

    public function getX(): NumberArgument {
        return $this->x;
    }

    public function getY(): NumberArgument {
        return $this->y;
    }

    public function getZ(): NumberArgument {
        return $this->z;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $entity = $this->entity->getOnlineEntity($source);

        $motion = new Vector3(
            $this->x->getFloat($source),
            $this->y->getFloat($source),
            $this->z->getFloat($source),
        );
        $entity->setMotion($motion);

        yield Await::ALL;
    }
}
