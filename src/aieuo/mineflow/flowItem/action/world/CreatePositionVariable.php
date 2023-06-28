<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\world;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\EditFormResponseProcessor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
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

    private NumberArgument $x;
    private NumberArgument $y;
    private NumberArgument $z;

    public function __construct(
        float          $x = 0,
        float          $y = 0,
        float          $z = 0,
        private string $world = "{target.world.name}",
        private string $variableName = "pos"
    ) {
        parent::__construct(self::CREATE_POSITION_VARIABLE, FlowItemCategory::WORLD);

        $this->x = new NumberArgument("x", $x, example: "0");
        $this->y = new NumberArgument("y", $y, example: "100");
        $this->z = new NumberArgument("z", $z, example: "16");
    }

    public function getDetailDefaultReplaces(): array {
        return ["position", "x", "y", "z", "world"];
    }

    public function getDetailReplaces(): array {
        return [$this->getVariableName(), $this->x->get(), $this->y->get(), $this->z->get(), $this->getWorld()];
    }

    public function setVariableName(string $variableName): void {
        $this->variableName = $variableName;
    }

    public function getVariableName(): string {
        return $this->variableName;
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

    public function setWorld(string $world): void {
        $this->world = $world;
    }

    public function getWorld(): string {
        return $this->world;
    }

    public function isDataValid(): bool {
        return $this->variableName !== "" and $this->x->get() !== "" and $this->y->get() !== "" and $this->z->get() !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $name = $source->replaceVariables($this->getVariableName());
        $x = $this->x->getFloat($source);
        $y = $this->y->getFloat($source);
        $z = $this->z->getFloat($source);
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
            $this->x->createFormElement($variables),
            $this->y->createFormElement($variables),
            $this->z->createFormElement($variables),
            new ExampleInput("@action.createPosition.form.world", "{target.level}", $this->getWorld(), true),
            new ExampleInput("@action.form.resultVariableName", "pos", $this->getVariableName(), true),
        ])->response(function (EditFormResponseProcessor $response) {
            $response->rearrange([4, 0, 1, 2, 3]);
        });
    }

    public function loadSaveData(array $content): void {
        $this->setVariableName($content[0]);
        $this->x->set($content[1]);
        $this->y->set($content[2]);
        $this->z->set($content[3]);
        $this->setWorld($content[4]);
    }

    public function serializeContents(): array {
        return [$this->getVariableName(), $this->x->get(), $this->y->get(), $this->z->get(), $this->getWorld()];
    }

    public function getAddingVariables(): array {
        $pos = $this->x->get().", ".$this->y->get().", ".$this->z->get().", ".$this->getWorld();
        return [
            $this->getVariableName() => new DummyVariable(PositionVariable::class, $pos)
        ];
    }
}
