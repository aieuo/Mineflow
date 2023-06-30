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

    private StringArgument $worldName;
    private StringArgument $resultName;

    public function __construct(string $worldName = "", string $resultName = "world") {
        parent::__construct(self::GET_WORLD_BY_NAME, FlowItemCategory::WORLD);

        $this->setArguments([
            $this->worldName = new StringArgument("name", $worldName, example: "world"),
            $this->resultName = new StringArgument("result", $resultName, "@action.form.resultVariableName", example: "world"),
        ]);
    }

    public function getWorldName(): StringArgument {
        return $this->worldName;
    }

    public function getResultName(): StringArgument {
        return $this->resultName;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $worldName = $this->worldName->getString($source);
        $result = $this->resultName->getString($source);

        $world = Server::getInstance()->getWorldManager()->getWorldByName($worldName);

        $variable = $world === null ? new NullVariable() : new WorldVariable($world);
        $source->addVariable($result, $variable);

        yield Await::ALL;
        return $this->resultName->get();
    }

    public function getAddingVariables(): array {
        return [
            $this->resultName->get() => new DummyVariable(WorldVariable::class)
        ];
    }
}
