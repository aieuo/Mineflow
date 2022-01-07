<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\script;

use aieuo\mineflow\flowItem\base\ConfigFileFlowItem;
use aieuo\mineflow\flowItem\base\ConfigFileFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ConfigVariableDropdown;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\utils\Language;

class RemoveConfigData extends FlowItem implements ConfigFileFlowItem {
    use ConfigFileFlowItemTrait;

    protected string $id = self::REMOVE_CONFIG_VALUE;

    protected string $name = "action.removeConfigData.name";
    protected string $detail = "action.removeConfigData.detail";
    protected array $detailDefaultReplace = ["config", "key"];

    protected string $category = FlowItemCategory::CONFIG;

    private string $key;

    public function __construct(string $config = "", string $key = "") {
        $this->setConfigVariableName($config);
        $this->key = $key;
    }

    public function getPermissions(): array {
        return [self::PERMISSION_CONFIG];
    }

    public function setKey(string $health): void {
        $this->key = $health;
    }

    public function getKey(): string {
        return $this->key;
    }

    public function isDataValid(): bool {
        return $this->getConfigVariableName() !== "" and $this->key !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getConfigVariableName(), $this->getKey()]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $key = $source->replaceVariables($this->getKey());

        $config = $this->getConfig($source);

        $config->removeNested($key);
        yield true;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ConfigVariableDropdown($variables, $this->getConfigVariableName()),
            new ExampleInput("@action.setConfigData.form.key", "aieuo", $this->getKey(), true),
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