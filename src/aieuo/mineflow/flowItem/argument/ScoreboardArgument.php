<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\argument;

use aieuo\mineflow\exception\InvalidPlaceholderValueException;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ScoreboardVariableDropdown;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Scoreboard;
use aieuo\mineflow\variable\object\ScoreboardVariable;

class ScoreboardArgument extends ObjectVariableArgument {

    public function __construct(string $name, string $value = "", string $description = null, bool $optional = false) {
        parent::__construct($name, $value, $description ?? "@action.form.target.scoreboard", $optional);
    }

    /**
     * @throws InvalidPlaceholderValueException
     */
    public function getScoreboard(FlowItemExecutor $executor): Scoreboard {
        $scoreboard = $this->getVariableName()->eval($executor->getVariableRegistryCopy());
        $variable = $executor->getVariable($scoreboard);

        if ($variable instanceof ScoreboardVariable) {
            return $variable->getValue();
        }

        throw new InvalidPlaceholderValueException(Language::get("action.target.not.valid", [["action.target.require.scoreboard"], (string)$this->getVariableName()]));
    }

    public function createFormElements(array $variables): array {
        return [
            new ScoreboardVariableDropdown($variables, (string)$this->getVariableName(), $this->getDescription(), $this->isOptional())
        ];
    }
}