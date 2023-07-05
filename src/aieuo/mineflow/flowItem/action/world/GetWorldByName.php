<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\world;

use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NullVariable;
use aieuo\mineflow\variable\object\WorldVariable;
use pocketmine\Server;
use SOFe\AwaitGenerator\Await;

class GetWorldByName extends SimpleAction {

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    public function __construct(string $worldName = "", string $resultName = "world") {
        parent::__construct(self::GET_WORLD_BY_NAME, FlowItemCategory::WORLD);

        $this->setArguments([
            new StringArgument("name", $worldName, example: "world"),
            new StringArgument("result", $resultName, "@action.form.resultVariableName", example: "world"),
        ]);
    }

    public function getWorldName(): StringArgument {
        return $this->getArguments()[0];
    }

    public function getResultName(): StringArgument {
        return $this->getArguments()[1];
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $worldName = $this->getWorldName()->getString($source);
        $result = $this->getResultName()->getString($source);

        $world = Server::getInstance()->getWorldManager()->getWorldByName($worldName);

        $variable = $world === null ? new NullVariable() : new WorldVariable($world);
        $source->addVariable($result, $variable);

        yield Await::ALL;
        return (string)$this->getResultName();
    }

    public function getAddingVariables(): array {
        return [
            (string)$this->getResultName() => new DummyVariable(WorldVariable::class)
        ];
    }
}
