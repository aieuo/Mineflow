<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\flowItem\base\ConfigFileFlowItem;
use aieuo\mineflow\flowItem\base\ConfigFileFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ConfigVariableDropdown;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;

class ExistsConfigData extends FlowItem implements Condition, ConfigFileFlowItem {
    use ConfigFileFlowItemTrait;

    protected string $id = self::EXISTS_CONFIG_DATA;

    protected string $name = "condition.existsConfigData.name";
    protected string $detail = "condition.existsConfigData.detail";
    protected array $detailDefaultReplace = ["config", "key"];

    protected string $category = Category::SCRIPT;

    private string $key;

    public function __construct(string $config = "", string $permission = "") {
        $this->setConfigVariableName($config);
        $this->key = $permission;
    }

    public function setKey(string $key): void {
        $this->key = $key;
    }

    public function getKey(): string {
        return $this->key;
    }

    public function isDataValid(): bool {
        return $this->getConfigVariableName() !== "" and $this->getKey() !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getConfigVariableName(), $this->getKey()]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $config = $this->getConfig($source);

        $key = $source->replaceVariables($this->getKey());

        FlowItemExexutor::CONTINUE;
        return $config->getNested($key) !== null;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ConfigVariableDropdown($variables, $this->getConfigVariableName()),
            new ExampleInput("@condition.existsConfigData.form.key", "aieuo", $this->getKey(), true),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setConfigVariableName($content[0]);
        $this->setKey($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getConfigVariableName(), $this->getKey()];
    }
}