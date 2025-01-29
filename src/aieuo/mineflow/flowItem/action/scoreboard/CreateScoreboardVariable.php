<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\scoreboard;

use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\argument\StringEnumArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\editor\MainFlowItemEditor;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Scoreboard;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\ScoreboardVariable;
use aieuo\mineflow\libs\_1195f54ac7f1c3fe\SOFe\AwaitGenerator\Await;

class CreateScoreboardVariable extends SimpleAction {

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    private array $displayTypes = [Scoreboard::DISPLAY_SIDEBAR, Scoreboard::DISPLAY_LIST, Scoreboard::DISPLAY_BELOWNAME];

    public function __construct(
        string $variableName = "board",
        string $boardId = "",
        string $displayName = "",
        string $displayType = Scoreboard::DISPLAY_SIDEBAR,
    ) {
        parent::__construct(self::CREATE_SCOREBOARD_VARIABLE, FlowItemCategory::SCOREBOARD);

        $this->setArguments([
            StringArgument::create("result", $variableName, "@action.form.resultVariableName")->example("board"),
            StringArgument::create("id", $boardId)->example("aieuo"),
            StringArgument::create("displayName", $displayName)->example("auieo"),
            StringEnumArgument::create("type", $displayType)->options($this->displayTypes),
        ]);
    }

    public function getVariableName(): StringArgument {
        return $this->getArgument("result");
    }

    public function getBoardId(): StringArgument {
        return $this->getArgument("id");
    }

    public function getDisplayName(): StringArgument {
        return $this->getArgument("displayName");
    }

    public function getDisplayType(): StringEnumArgument {
        return $this->getArgument("type");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $variableName = $this->getVariableName()->getString($source);
        $id = $this->getBoardId()->getString($source);
        $displayName = $this->getDisplayName()->getString($source);
        $type = $this->getDisplayType()->getEnumValue();

        $scoreboard = new Scoreboard($type, $id, $displayName);

        $variable = new ScoreboardVariable($scoreboard);
        $source->addVariable($variableName, $variable);

        yield Await::ALL;
        return (string)$this->getVariableName();
    }

    public function getAddingVariables(): array {
        return [
            (string)$this->getVariableName() => new DummyVariable(ScoreboardVariable::class, (string)$this->getDisplayName())
        ];
    }

    public function getEditors(): array {
        return [
            new MainFlowItemEditor($this, [
                $this->getBoardId(),
                $this->getDisplayName(),
                $this->getDisplayType(),
                $this->getVariableName(),
            ]),
        ];
    }
}