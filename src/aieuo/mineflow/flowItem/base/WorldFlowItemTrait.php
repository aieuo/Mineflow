<?php


namespace aieuo\mineflow\flowItem\base;


use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\object\WorldVariable;
use pocketmine\world\World;

trait WorldFlowItemTrait {

    /* @var string[] */
    private array $worldVariableNames = [];

    public function getWorldVariableName(string $name = ""): string {
        return $this->worldVariableNames[$name] ?? "";
    }

    public function setWorldVariableName(string $world, string $name = ""): void {
        $this->worldVariableNames[$name] = $world;
    }

    public function getWorld(FlowItemExecutor $source, string $name = ""): World {
        $world = $source->replaceVariables($rawName = $this->getWorldVariableName($name));

        $variable = $source->getVariable($world);
        if ($variable instanceof WorldVariable and ($world = $variable->getWorld()) instanceof World) {
            return $world;
        }

        throw new InvalidFlowValueException($this->getName(), Language::get("action.target.not.valid", [["action.target.require.world"], $rawName]));
    }
}