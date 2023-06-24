<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\world;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\EditFormResponseProcessor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\flowItem\placeholder\EntityPlaceholder;
use aieuo\mineflow\formAPI\element\Dropdown;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\PositionVariable;
use pocketmine\math\Facing;
use pocketmine\world\Position;
use SOFe\AwaitGenerator\Await;

class GetEntitySidePosition extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

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
    private EntityPlaceholder $entity;

    public function __construct(
        string         $entity = "",
        private string $direction = "",
        private string $steps = "1",
        private string $resultName = "pos"
    ) {
        parent::__construct(self::GET_ENTITY_SIDE, FlowItemCategory::WORLD);

        $this->entity = new EntityPlaceholder("entity", $entity);
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->entity->getName(), "direction", "step", "result"];
    }

    public function getDetailReplaces(): array {
        return [$this->entity->get(), $this->getDirection(), $this->getSteps(), $this->getResultName()];
    }

    public function getEntity(): EntityPlaceholder {
        return $this->entity;
    }

    public function setDirection(string $direction): void {
        $this->direction = $direction;
    }

    public function getDirection(): string {
        return $this->direction;
    }

    public function setSteps(string $steps): void {
        $this->steps = $steps;
    }

    public function getSteps(): string {
        return $this->steps;
    }

    public function setResultName(string $resultName): void {
        $this->resultName = $resultName;
    }

    public function getResultName(): string {
        return $this->resultName;
    }

    public function isDataValid(): bool {
        return $this->entity->isNotEmpty() and $this->direction !== "" and $this->steps !== "" and $this->resultName !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $entity = $this->entity->getOnlineEntity($source);
        $side = $source->replaceVariables($this->getDirection());
        $step = $this->getInt($source->replaceVariables($this->getSteps()));
        $resultName = $source->replaceVariables($this->getResultName());

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
        return $this->getResultName();
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
           $this->entity->createFormElement($variables),
            new Dropdown("@action.getEntitySide.form.direction", $this->directions, (int)array_search($this->getDirection(), $this->directions, true)),
            new ExampleNumberInput("@action.getEntitySide.form.steps", "1", $this->getSteps(), true),
            new ExampleInput("@action.form.resultVariableName", "pos", $this->getResultName(), true),
        ])->response(function (EditFormResponseProcessor $response) {
            $response->preprocessAt(1, fn($value) => $this->directions[$value] ?? "");
        });
    }

    public function loadSaveData(array $content): void {
        $this->entity->set($content[0]);
        $this->setDirection($content[1]);
        $this->setSteps($content[2]);
        $this->setResultName($content[3]);
    }

    public function serializeContents(): array {
        return [$this->entity->get(), $this->getDirection(), $this->getSteps(), $this->getResultName()];
    }

    public function getAddingVariables(): array {
        return [
            $this->getResultName() => new DummyVariable(PositionVariable::class)
        ];
    }
}
