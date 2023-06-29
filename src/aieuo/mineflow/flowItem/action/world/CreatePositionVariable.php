<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\world;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\EditFormResponseProcessor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
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
    private StringArgument $world;
    private StringArgument $variableName;

    public function __construct(float $x = 0, float $y = 0, float $z = 0, string $world = "{target.world.name}", string $variableName = "pos") {
        parent::__construct(self::CREATE_POSITION_VARIABLE, FlowItemCategory::WORLD);

        $this->variableName = new StringArgument("position", $variableName, "@action.form.resultVariableName", example: "pos");
        $this->x = new NumberArgument("x", $x, example: "0");
        $this->y = new NumberArgument("y", $y, example: "100");
        $this->z = new NumberArgument("z", $z, example: "16");
        $this->world = new StringArgument("world", $world, example: "{target.level}");
    }

    public function getDetailDefaultReplaces(): array {
        return ["position", "x", "y", "z", "world"];
    }

    public function getDetailReplaces(): array {
        return [$this->variableName->get(), $this->x->get(), $this->y->get(), $this->z->get(), $this->world->get()];
    }

    public function getVariableName(): StringArgument {
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

    public function getWorld(): StringArgument {
        return $this->world;
    }

    public function isDataValid(): bool {
        return $this->variableName->isNotEmpty() and $this->x->get() !== "" and $this->y->get() !== "" and $this->z->get() !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $name = $this->variableName->getString($source);
        $x = $this->x->getFloat($source);
        $y = $this->y->getFloat($source);
        $z = $this->z->getFloat($source);
        $levelName = $this->world->getString($source);
        $level = Server::getInstance()->getWorldManager()->getWorldByName($levelName);

        if ($level === null) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.createPosition.world.notFound"));
        }

        $position = new Position($x, $y, $z, $level);

        $variable = new PositionVariable($position);
        $source->addVariable($name, $variable);

        yield Await::ALL;
        return $this->variableName->get();
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->x->createFormElement($variables),
            $this->y->createFormElement($variables),
            $this->z->createFormElement($variables),
            $this->world->createFormElement($variables),
            $this->variableName->createFormElement($variables),
        ])->response(function (EditFormResponseProcessor $response) {
            $response->rearrange([4, 0, 1, 2, 3]);
        });
    }

    public function loadSaveData(array $content): void {
        $this->variableName->set($content[0]);
        $this->x->set($content[1]);
        $this->y->set($content[2]);
        $this->z->set($content[3]);
        $this->world->set($content[4]);
    }

    public function serializeContents(): array {
        return [$this->variableName->get(), $this->x->get(), $this->y->get(), $this->z->get(), $this->world->get()];
    }

    public function getAddingVariables(): array {
        $pos = $this->x->get().", ".$this->y->get().", ".$this->z->get().", ".$this->world->get();
        return [
            $this->variableName->get() => new DummyVariable(PositionVariable::class, $pos)
        ];
    }
}
