<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\placeholder;

use aieuo\mineflow\exception\InvalidPlaceholderValueException;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\Element;
use aieuo\mineflow\formAPI\element\mineflow\ScoreboardVariableDropdown;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Scoreboard;
use aieuo\mineflow\variable\object\ScoreboardVariable;

class ScoreboardPlaceholder extends Placeholder {

    public function __construct(string $name, string $value = "", string $description = null) {
        parent::__construct($name, $value, $description ?? "@action.form.target.scoreboard");
    }

    /**
     * @throws InvalidPlaceholderValueException
     */
    public function getScoreboard(FlowItemExecutor $executor): Scoreboard {
        $scoreboard = $executor->replaceVariables($this->get());
        $variable = $executor->getVariable($scoreboard);

        if ($variable instanceof ScoreboardVariable) {
            return $variable->getValue();
        }

        throw new InvalidPlaceholderValueException(Language::get("action.target.not.valid", [["action.target.require.scoreboard"], $this->get()]));
    }

    public function createFormElement(array $variables): Element {
        return new ScoreboardVariableDropdown($variables, $this->get());
    }
}