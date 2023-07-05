<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\world;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\EditFormResponseProcessor;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\PositionVariable;
use pocketmine\Server;
use pocketmine\world\Position;
use SOFe\AwaitGenerator\Await;

class CreatePositionVariable extends SimpleAction {

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    public function __construct(float $x = 0, float $y = 0, float $z = 0, string $world = "{target.world.name}", string $variableName = "pos") {
        parent::__construct(self::CREATE_POSITION_VARIABLE, FlowItemCategory::WORLD);

        $this->setArguments([
            new StringArgument("position", $variableName, "@action.form.resultVariableName", example: "pos"),
            new NumberArgument("x", $x, example: "0"),
            new NumberArgument("y", $y, example: "100"),
            new NumberArgument("z", $z, example: "16"),
            new StringArgument("world", $world, example: "{target.level}"),
        ]);
    }

    public function getVariableName(): StringArgument {
        return $this->getArguments()[0];
    }

    public function getX(): NumberArgument {
        return $this->getArguments()[1];
    }

    public function getY(): NumberArgument {
        return $this->getArguments()[2];
    }

    public function getZ(): NumberArgument {
        return $this->getArguments()[3];
    }

    public function getWorld(): StringArgument {
        return $this->getArguments()[4];
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $name = $this->getVariableName()->getString($source);
        $x = $this->getX()->getFloat($source);
        $y = $this->getY()->getFloat($source);
        $z = $this->getZ()->getFloat($source);
        $levelName = $this->getWorld()->getString($source);
        $level = Server::getInstance()->getWorldManager()->getWorldByName($levelName);

        if ($level === null) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.createPosition.world.notFound"));
        }

        $position = new Position($x, $y, $z, $level);

        $variable = new PositionVariable($position);
        $source->addVariable($name, $variable);

        yield Await::ALL;
        return (string)$this->getVariableName();
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->getX()->createFormElement($variables),
            $this->getY()->createFormElement($variables),
            $this->getZ()->createFormElement($variables),
            $this->getWorld()->createFormElement($variables),
            $this->getVariableName()->createFormElement($variables),
        ])->response(function (EditFormResponseProcessor $response) {
            $response->rearrange([4, 0, 1, 2, 3]);
        });
    }

    public function getAddingVariables(): array {
        $pos = $this->getX().", ".$this->getY().", ".$this->getZ().", ".$this->getWorld();
        return [
            (string)$this->getVariableName() => new DummyVariable(PositionVariable::class, $pos)
        ];
    }
}
