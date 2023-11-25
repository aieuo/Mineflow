<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\scoreboard;

use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\argument\StringEnumArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\page\custom\CustomFormResponseProcessor;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\utils\Scoreboard;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\ScoreboardVariable;
use SOFe\AwaitGenerator\Await;

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
            new StringArgument("result", $variableName, "@action.form.resultVariableName", example: "board"),
            new StringArgument("id", $boardId, example: "aieuo"),
            new StringArgument("displayName", $displayName, example: "auieo"),
            new StringEnumArgument("type", $displayType, $this->displayTypes),
        ]);
    }

    public function getVariableName(): StringArgument {
        return $this->getArguments()[0];
    }

    public function getBoardId(): StringArgument {
        return $this->getArguments()[1];
    }

    public function getDisplayName(): StringArgument {
        return $this->getArguments()[2];
    }

    public function getDisplayType(): StringEnumArgument {
        return $this->getArguments()[3];
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

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->getBoardId()->createFormElements($variables)[0],
            $this->getDisplayName()->createFormElements($variables)[0],
            $this->getDisplayType()->createFormElements($variables)[0],
            $this->getVariableName()->createFormElements($variables)[0],
        ])->response(function (CustomFormResponseProcessor $response) {
            $response->rearrange([3, 0, 1, 2]);
        });
    }

    public function getAddingVariables(): array {
        return [
            (string)$this->getVariableName() => new DummyVariable(ScoreboardVariable::class, (string)$this->getDisplayName())
        ];
    }
}
