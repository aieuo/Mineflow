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
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\MapVariable;

class AddMapVariable extends Process {

    protected $id = self::ADD_MAP_VARIABLE;

    protected $name = "@action.addMapVariable.name";
    protected $description = "@action.addMapVariable.description";
    protected $detail = "action.addMapVariable.detail";

    protected $category = Categories::CATEGORY_ACTION_VARIABLE;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;

    /** @var string */
    private $variableName;
    /** @var string */
    private $variableKey;
    /** @var string */
    private $variableValue;
    /** @var bool */
    private $isLocal = true;

    public function __construct(string $name = "", string $key = "", string $value = "", bool $local = true) {
        $this->variableName = $name;
        $this->variableKey = $key;
        $this->variableValue = $value;
        $this->isLocal = $local;
    }

    public function setVariableName(string $variableName) {
        $this->variableName = $variableName;
    }

    public function getVariableName(): string {
        return $this->variableName;
    }

    public function setKey(string $variableKey) {
        $this->variableKey = $variableKey;
    }

    public function getKey(): string {
        return $this->variableKey;
    }

    public function setVariableValue(string $variableValue) {
        $this->variableValue = $variableValue;
    }

    public function getVariableValue(): string {
        return $this->variableValue;
    }

    public function isDataValid(): bool {
        return !empty($this->variableName) and ((!empty($this->variableKey) and !empty($this->variableValue)) or (empty($this->variableKey) and empty($this->variableValue)));
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getVariableName(), $this->isLocal ? "local" : "global", $this->getKey(), $this->getVariableValue()]);
    }

    public function execute(?Entity $target, ?Recipe $origin = null): ?bool {
        if (!$this->isDataValid()) {
            $target->sendMessage(Language::get("invalid.contents", [$this->getName()]));
            return null;
        }

        $helper = Main::getInstance()->getVariableHelper();
        $name = $this->getVariableName();
        $key = $this->getKey();
        $value = $this->getVariableValue();
        $isRecipe = $origin instanceof Recipe;

        if ($isRecipe) {
            $name = $origin->replaceVariables($name);
            $key = $origin->replaceVariables($key);
            $value = $origin->replaceVariables($value);
        } elseif (!$this->isLocal) {
            if ($target instanceof Player) $target->sendMessage(Language::get("action.error", [$this->getName(), Language::get("action.error.recipe")]));
            else Server::getInstance()->getLogger()->info(Language::get("action.error", [$this->getName(), Language::get("action.error.recipe")]));
            return null;
        }

        if ($key === "" and $value === "" and $this->isLocal) {
            $origin->addVariable(new MapVariable($name, []));
            return true;
        } elseif ($key === "" and $value === "") {
            $helper->add(new MapVariable($name, []));
            return true;
        }

        if ($this->isLocal) {
            $variable = $origin->getVariables()[$name] ?? null;
            if (!($variable instanceof MapVariable)) $variable = new MapVariable($name, []);
            $variable->addValue($key, $value);
            $origin->addVariable($variable);
        } else {
            $variable = $helper->get($name);
            if (!($variable instanceof MapVariable)) $variable = new MapVariable($name, []);
            $variable->addValue($key, $value);
            $helper->add($variable);
        }
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []) {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@action.variable.form.name", Language::get("form.example", ["aieuo"]), $default[1] ?? $this->getVariableName()),
                new Input("@action.variable.form.key", Language::get("form.example", ["auieo"]), $default[2] ?? $this->getKey()),
                new Input("@action.variable.form.value", Language::get("form.example", ["aeiuo"]), $default[3] ?? $this->getVariableValue()),
                new Toggle("@action.variable.form.global", $default[4] ?? !$this->isLocal),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $errors = [];
        $name = $data[1];
        $key = $data[2];
        $value = $data[3];
        if ($name === "") {
            $errors[] = ["@form.insufficient", 1];
        }
        if ($key === "" and $value !== "") {
            $errors[] = ["@form.insufficient", 2];
        }
        if ($key !== "" and $value === "") {
            $errors[] = ["@form.insufficient", 3];
        }
        return ["status" => empty($errors), "contents" => [$name, $key, $value, !$data[4]], "cancel" => $data[5], "errors" => $errors];
    }

    public function parseFromSaveData(array $content): ?Process {
        if (!isset($content[3])) return null;
        $this->setVariableName($content[0]);
        $this->setKey($content[1]);
        $this->setVariableValue($content[2]);
        $this->isLocal = $content[3];
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getVariableName(), $this->getKey(), $this->getVariableValue(), $this->isLocal];
    }
}