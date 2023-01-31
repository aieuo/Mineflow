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
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\PositionVariable;
use pocketmine\Server;
use pocketmine\world\Position;
use SOFe\AwaitGenerator\Await;

class CreatePositionVariable extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    public function __construct(
        private string $x = "",
        private string $y = "",
        private string $z = "",
        private string $world = "{target.world.name}",
        private string $variableName = "pos"
    ) {
        parent::__construct(self::CREATE_POSITION_VARIABLE, FlowItemCategory::WORLD);
    }

    public function getDetailDefaultReplaces(): array {
        return ["position", "x", "y", "z", "world"];
    }

    public function getDetailReplaces(): array {
        return [$this->getVariableName(), $this->getX(), $this->getY(), $this->getZ(), $this->getWorld()];
    }

    public function setVariableName(string $variableName): void {
        $this->variableName = $variableName;
    }

    public function getVariableName(): string {
        return $this->variableName;
    }

    public function setX(string $x): void {
        $this->x = $x;
    }

    public function getX(): string {
        return $this->x;
    }

    public function setY(string $y): void {
        $this->y = $y;
    }

    public function getY(): string {
        return $this->y;
    }

    public function setZ(string $z): void {
        $this->z = $z;
    }

    public function getZ(): string {
        return $this->z;
    }

    public function setWorld(string $world): void {
        $this->world = $world;
    }

    public function getWorld(): string {
        return $this->world;
    }

    public function isDataValid(): bool {
        return $this->variableName !== "" and $this->x !== "" and $this->y !== "" and $this->z !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $name = $source->replaceVariables($this->getVariableName());
        $x = $this->getFloat($source->replaceVariables($this->getX()));
        $y = $this->getFloat($source->replaceVariables($this->getY()));
        $z = $this->getFloat($source->replaceVariables($this->getZ()));
        $levelName = $source->replaceVariables($this->getWorld());
        $level = Server::getInstance()->getWorldManager()->getWorldByName($levelName);

        if ($level === null) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.createPosition.world.notFound"));
        }

        $position = new Position($x, $y, $z, $level);

        $variable = new PositionVariable($position);
        $source->addVariable($name, $variable);

        yield Await::ALL;
        return $this->getVariableName();
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            new ExampleNumberInput("@action.createPosition.form.x", "0", $this->getX(), true),
            new ExampleNumberInput("@action.createPosition.form.y", "100", $this->getY(), true),
            new ExampleNumberInput("@action.createPosition.form.z", "16", $this->getZ(), true),
            new ExampleInput("@action.createPosition.form.world", "{target.level}", $this->getWorld(), true),
            new ExampleInput("@action.form.resultVariableName", "pos", $this->getVariableName(), true),
        ])->response(function (EditFormResponseProcessor $response) {
            $response->rearrange([4, 0, 1, 2, 3]);
        });
    }

    public function loadSaveData(array $content): void {
        $this->setVariableName($content[0]);
        $this->setX($content[1]);
        $this->setY($content[2]);
        $this->setZ($content[3]);
        $this->setWorld($content[4]);
    }

    public function serializeContents(): array {
        return [$this->getVariableName(), $this->getX(), $this->getY(), $this->getZ(), $this->getWorld()];
    }

    public function getAddingVariables(): array {
        $pos = $this->getX().", ".$this->getY().", ".$this->getZ().", ".$this->getWorld();
        return [
            $this->getVariableName() => new DummyVariable(PositionVariable::class, $pos)
        ];
    }
}
