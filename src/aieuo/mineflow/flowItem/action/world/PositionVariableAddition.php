<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\world;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\flowItem\argument\PositionArgument;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\PositionVariable;
use pocketmine\world\Position;
use SOFe\AwaitGenerator\Await;

class PositionVariableAddition extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    private PositionArgument $position;

    public function __construct(
        string         $position = "pos",
        private string $x = "",
        private string $y = "",
        private string $z = "",
        private string $resultName = "pos"
    ) {
        parent::__construct(self::POSITION_VARIABLE_ADDITION, FlowItemCategory::WORLD);

        $this->position = new PositionArgument("position", $position);
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->position->getName(), "x", "y", "z", "result"];
    }

    public function getDetailReplaces(): array {
        return [$this->position->get(), $this->getX(), $this->getY(), $this->getZ(), $this->getResultName()];
    }

    public function getPosition(): PositionArgument {
        return $this->position;
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

    public function setResultName(string $name): void {
        $this->resultName = $name;
    }

    public function getResultName(): string {
        return $this->resultName;
    }

    public function isDataValid(): bool {
        return $this->position->isNotEmpty() and $this->x !== "" and $this->y !== "" and $this->z !== "" and $this->resultName !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $pos = $this->position->getPosition($source);

        $x = $this->getFloat($source->replaceVariables($this->getX()));
        $y = $this->getFloat($source->replaceVariables($this->getY()));
        $z = $this->getFloat($source->replaceVariables($this->getZ()));
        $name = $source->replaceVariables($this->getResultName());

        $position = Position::fromObject($pos->add($x, $y, $z), $pos->getWorld());

        $variable = new PositionVariable($position);
        $source->addVariable($name, $variable);

        yield Await::ALL;
        return $this->getResultName();
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->position->createFormElement($variables),
            new ExampleNumberInput("@action.positionAddition.form.x", "0", $this->getX(), true),
            new ExampleNumberInput("@action.positionAddition.form.y", "100", $this->getY(), true),
            new ExampleNumberInput("@action.positionAddition.form.z", "16", $this->getZ(), true),
            new ExampleInput("@action.form.resultVariableName", "pos", $this->getResultName(), true),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->position->set($content[0]);
        $this->setX($content[1]);
        $this->setY($content[2]);
        $this->setZ($content[3]);
        $this->setResultName($content[4]);
    }

    public function serializeContents(): array {
        return [$this->position->get(), $this->getX(), $this->getY(), $this->getZ(), $this->getResultName()];
    }

    public function getAddingVariables(): array {
        $desc = $this->position->get()." + (".$this->getX().",".$this->getY().",".$this->getZ().")";
        return [
            $this->getResultName() => new DummyVariable(PositionVariable::class, $desc)
        ];
    }
}
