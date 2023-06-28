<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\world;

use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\argument\PositionArgument;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\PositionVariable;
use pocketmine\world\Position;
use SOFe\AwaitGenerator\Await;

class PositionVariableAddition extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    private PositionArgument $position;
    private NumberArgument $x;
    private NumberArgument $y;
    private NumberArgument $z;

    public function __construct(
        string         $position = "pos",
        string         $x = null,
        string         $y = null,
        string         $z = null,
        private string $resultName = "pos"
    ) {
        parent::__construct(self::POSITION_VARIABLE_ADDITION, FlowItemCategory::WORLD);

        $this->position = new PositionArgument("position", $position);
        $this->x = new NumberArgument("x", $x, example: "0");
        $this->y = new NumberArgument("y", $y, example: "100");
        $this->z = new NumberArgument("z", $z, example: "16");
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->position->getName(), "x", "y", "z", "result"];
    }

    public function getDetailReplaces(): array {
        return [$this->position->get(), $this->x->get(), $this->y->get(), $this->z->get(), $this->getResultName()];
    }

    public function getPosition(): PositionArgument {
        return $this->position;
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

    public function setResultName(string $name): void {
        $this->resultName = $name;
    }

    public function getResultName(): string {
        return $this->resultName;
    }

    public function isDataValid(): bool {
        return $this->position->isNotEmpty() and $this->x->get() !== "" and $this->y->get() !== "" and $this->z->get() !== "" and $this->resultName !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $pos = $this->position->getPosition($source);

        $x = $this->x->getFloat($source);
        $y = $this->y->getFloat($source);
        $z = $this->z->getFloat($source);
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
            $this->x->createFormElement($variables),
            $this->y->createFormElement($variables),
            $this->z->createFormElement($variables),
            new ExampleInput("@action.form.resultVariableName", "pos", $this->getResultName(), true),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->position->set($content[0]);
        $this->x->set($content[1]);
        $this->y->set($content[2]);
        $this->z->set($content[3]);
        $this->setResultName($content[4]);
    }

    public function serializeContents(): array {
        return [$this->position->get(), $this->x->get(), $this->y->get(), $this->z->get(), $this->getResultName()];
    }

    public function getAddingVariables(): array {
        $desc = $this->position->get()." + (".$this->x->get().",".$this->y->get().",".$this->z->get().")";
        return [
            $this->getResultName() => new DummyVariable(PositionVariable::class, $desc)
        ];
    }
}
