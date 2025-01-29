<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\world;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\argument\EntityArgument;
use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\argument\StringEnumArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\PositionVariable;
use pocketmine\math\Facing;
use pocketmine\world\Position;
use aieuo\mineflow\libs\_1195f54ac7f1c3fe\SOFe\AwaitGenerator\Await;

class GetEntitySidePosition extends SimpleAction {

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    public const SIDE_DOWN = "down";
    public const SIDE_UP = "up";
    public const SIDE_NORTH = "north";
    public const SIDE_SOUTH = "south";
    public const SIDE_WEST = "west";
    public const SIDE_EAST = "east";
    public const SIDE_FRONT = "front";
    public const SIDE_BEHIND = "behind";
    public const SIDE_LEFT = "left";
    public const SIDE_RIGHT = "right";

    /** @var string[] */
    private array $directions = [
        self::SIDE_DOWN,
        self::SIDE_UP,
        self::SIDE_NORTH,
        self::SIDE_SOUTH,
        self::SIDE_WEST,
        self::SIDE_EAST,
        self::SIDE_FRONT,
        self::SIDE_BEHIND,
        self::SIDE_LEFT,
        self::SIDE_RIGHT,
    ];

    private array $vector3SideMap = [
        self::SIDE_DOWN => Facing::DOWN,
        self::SIDE_UP => Facing::UP,
        self::SIDE_NORTH => Facing::NORTH,
        self::SIDE_SOUTH => Facing::SOUTH,
        self::SIDE_WEST => Facing::WEST,
        self::SIDE_EAST => Facing::EAST,
    ];

    private array $directionSideMap = [
        Facing::EAST,
        Facing::SOUTH,
        Facing::WEST,
        Facing::NORTH,
    ];

    public function __construct(string $entity = "", string $direction = self::SIDE_DOWN, int $steps = 1, string $resultName = "pos") {
        parent::__construct(self::GET_ENTITY_SIDE, FlowItemCategory::WORLD);

        $this->setArguments([
            EntityArgument::create("entity", $entity),
            StringEnumArgument::create("direction", $direction)->options($this->directions),
            NumberArgument::create("steps", $steps)->example("1"),
            StringArgument::create("result", $resultName, "@action.form.resultVariableName")->example("pos"),
        ]);
    }

    public function getEntity(): EntityArgument {
        return $this->getArgument("entity");
    }

    public function getDirection(): StringEnumArgument {
        return $this->getArgument("direction");
    }

    public function getSteps(): NumberArgument {
        return $this->getArgument("steps");
    }

    public function getResultName(): StringArgument {
        return $this->getArgument("result");
    }

    public function isDataValid(): bool {
        return $this->getEntity()->isValid() and $this->getDirection()->isValid() and $this->getSteps()->isValid() and $this->getResultName()->isValid();
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $entity = $this->getEntity()->getOnlineEntity($source);
        $side = $this->getDirection()->getEnumValue();
        $step = $this->getSteps()->getInt($source);
        $resultName = $this->getResultName()->getString($source);

        $direction = $entity->getHorizontalFacing();
        $pos = $entity->getPosition()->floor()->add(0.5, 0.5, 0.5);
        switch ($side) {
            case self::SIDE_DOWN:
            case self::SIDE_UP:
            case self::SIDE_NORTH:
            case self::SIDE_SOUTH:
            case self::SIDE_WEST:
            case self::SIDE_EAST:
                $pos = $pos->getSide($this->vector3SideMap[$side], $step);
                break;
            /** @noinspection PhpMissingBreakStatementInspection */
            case self::SIDE_LEFT:
                $direction++;
            /** @noinspection PhpMissingBreakStatementInspection */
            case self::SIDE_BEHIND:
                $direction++;
            /** @noinspection PhpMissingBreakStatementInspection */
            case self::SIDE_RIGHT:
                $direction++;
            case self::SIDE_FRONT:
                $pos = $pos->getSide($this->directionSideMap[$direction % 4], $step);
                break;
            default:
                throw new InvalidFlowValueException($this->getName(), Language::get("action.getEntitySide.direction.notFound", [$side]));
        }

        $source->addVariable($resultName, new PositionVariable(Position::fromObject($pos, $entity->getWorld())));

        yield Await::ALL;
        return (string)$this->getResultName();
    }

    public function getAddingVariables(): array {
        return [
            (string)$this->getResultName() => new DummyVariable(PositionVariable::class)
        ];
    }
}