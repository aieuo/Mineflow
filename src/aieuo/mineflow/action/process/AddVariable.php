<?php

namespace aieuo\mineflow\action\process;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\entity\Entity;
use pocketmine\Server;
use aieuo\mineflow\variable\Variable;
use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\action\process\Process;
use aieuo\mineflow\FormAPI\element\Dropdown;
use aieuo\mineflow\Main;
use aieuo\mineflow\FormAPI\element\Toggle;

class AddVariable extends Process {

    protected $id = self::ADD_VARIABLE;

    protected $name = "@action.addVariable.name";
    protected $description = "@action.addVariable.description";
    protected $detail = "action.addVariable.detail";

    protected $category = Categories::CATEGORY_ACTION_VARIABLE;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;

    /** @var string */
    private $variableName;
    /** @var string */
    private $variableValue;
    /** @var int */
    private $variableType = Variable::STRING;
    /** @var bool */
    private $isLocal = true;

    /** @var array */
    private $variableTypes = ["string", "number"];

    public function __construct(string $name = "", string $value = "", int $type = Variable::STRING, bool $local = true) {
        $this->variableName = $name;
        $this->variableValue = $value;
        $this->variableType = $type;
        $this->isLocal = $local;
    }

    public function setVariableName(string $variableName) {
        $this->variableName = $variableName;
    }

    public function getVariableName(): string {
        return $this->variableName;
    }

    public function setVariableValue(string $variableValue) {
        $this->variableValue = $variableValue;
    }

    public function getVariableValue(): string {
        return $this->variableValue;
    }

    public function isDataValid(): bool {
        return !empty($this->variableName) and $this->variableValue !== null;
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getVariableName(), $this->getVariableValue(), $this->variableTypes[$this->variableType], $this->isLocal ? "local" : "global"]);
    }

    public function execute(?Entity $target, ?Recipe $origin = null): ?bool {
        if (!$this->isDataValid()) {
            $target->sendMessage(Language::get("invalid.contents", [$this->getName()]));
            return null;
        }

        $name = $this->getVariableName();
        $value = $this->getVariableValue();
        if ($origin instanceof Recipe) {
            $name = $origin->replaceVariables($name);
            $value = $origin->replaceVariables($value);
        }

        switch ($this->variableType) {
            case Variable::STRING:
                $variable = new StringVariable($name, $value);
                break;
            case Variable::NUMBER:
                if (!is_numeric($value)) {
                    if ($target instanceof Player) $target->sendMessage(Language::get("action.error", [$this->getName(), Language::get("mineflow.contents.notNumber")]));
                    else Server::getInstance()->getLogger()->info(Language::get("action.error", [$this->getName(), Language::get("mineflow.contents.notNumber")]));
                    return null;
                }
                $variable = new NumberVariable($name, (float)$value);
                break;
        }

        if (!$this->isLocal) {
            Main::getInstance()->getVariableHelper()->add($variable);
            return true;
        }
        if (!($origin instanceof Recipe)) {
            if ($target instanceof Player) $target->sendMessage(Language::get("action.error", [$this->getName(), Language::get("action.error.recipe")]));
            else Server::getInstance()->getLogger()->info(Language::get("action.error", [$this->getName(), Language::get("action.error.recipe")]));
            return null;
        }
        $origin->addVariable($variable);
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []) {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@action.variable.form.name", Language::get("form.example", ["aieuo"]), $default[1] ?? $this->getVariableName()),
                new Input("@action.variable.form.value", Language::get("form.example", ["aeiuo"]), $default[2] ?? $this->getVariableValue()),
                new Dropdown("@action.variable.form.type", $this->variableTypes, $default[3] ?? $this->variableType),
                new Toggle("@action.variable.form.global", $default[4] ?? !$this->isLocal),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $errors = [];
        $name = $data[1];
        $value = $data[2];
        $type = $data[3];
        if ($name === "") {
            $errors[] = ["@form.insufficient", 1];
        }
        $containsVariable = Main::getInstance()->getVariableHelper()->containsVariable($value);
        if ($value === "") {
            $errors[] = ["@form.insufficient", 1];
        } elseif ($type === Variable::NUMBER and !$containsVariable and !is_numeric($value)) {
            $errors[] = ["@mineflow.contents.notNumber", 1];
        }
        return ["status" => empty($errors), "contents" => [$name, $value, $type, !$data[4]], "cancel" => $data[5], "errors" => $errors];
    }

    public function parseFromSaveData(array $content): ?Process {
        if (!isset($content[3])) return null;
        $this->setVariableName($content[0]);
        $this->setVariableValue($content[1]);
        $this->variableType = $content[2];
        $this->isLocal = $content[3];
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getVariableName(), $this->getVariableValue(), $this->variableType, $this->isLocal];
    }
}