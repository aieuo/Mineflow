<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\Main;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\ListVariable;

class ExistsListVariableKey extends FlowItem implements Condition {

    protected $id = self::EXISTS_LIST_VARIABLE_KEY;

    protected $name = "condition.existsListVariableKey.name";
    protected $detail = "condition.existsListVariableKey.detail";
    protected $detailDefaultReplace = ["scope", "name", "key"];

    protected $category = Category::VARIABLE;

    /** @var string */
    private $variableName;
    /** @var string */
    private $variableKey;
    /** @var bool */
    private $isLocal;

    public function __construct(string $name = "", string $key = "", bool $local = true) {
        $this->variableName = $name;
        $this->variableKey = $key;
        $this->isLocal = $local;
    }

    public function setVariableName(string $variableName): void {
        $this->variableName = $variableName;
    }

    public function getVariableName(): string {
        return $this->variableName;
    }

    public function setKey(string $variableKey): void {
        $this->variableKey = $variableKey;
    }

    public function getKey(): string {
        return $this->variableKey;
    }

    public function isDataValid(): bool {
        return $this->variableName !== "" and $this->variableKey !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->isLocal ? "local" : "global", $this->getVariableName(), $this->getKey()]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $helper = Main::getVariableHelper();
        $name = $source->replaceVariables($this->getVariableName());
        $key = $source->replaceVariables($this->getKey());

        $variable = $this->isLocal ? $source->getVariable($name) : $helper->get($name);
        if (!($variable instanceof ListVariable)) return false;
        $value = $variable->getValue();

        yield true;
        return isset($value[$key]);
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ExampleInput("@action.variable.form.name", "aieuo", $this->getVariableName(), true),
            new ExampleInput("@action.variable.form.key", "auieo", $this->getKey(), true),
            new Toggle("@action.variable.form.global", !$this->isLocal),
        ];
    }

    public function parseFromFormData(array $data): array {
        return ["contents" => [$data[0], $data[1], !$data[2]]];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setVariableName($content[0]);
        $this->setKey($content[1]);
        $this->isLocal = $content[2];
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getVariableName(), $this->getKey(), $this->isLocal];
    }
}