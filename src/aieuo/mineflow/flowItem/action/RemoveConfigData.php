<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\ConfigFileFlowItem;
use aieuo\mineflow\flowItem\base\ConfigFileFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\element\mineflow\ConfigVariableDropdown;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;

class RemoveConfigData extends FlowItem implements ConfigFileFlowItem {
    use ConfigFileFlowItemTrait;

    protected $id = self::REMOVE_CONFIG_VALUE;

    protected $name = "action.removeConfigData.name";
    protected $detail = "action.removeConfigData.detail";
    protected $detailDefaultReplace = ["config", "key"];

    protected $category = Category::SCRIPT;

    protected $permission = self::PERMISSION_LEVEL_2;

    /** @var string */
    private $key;

    public function __construct(string $config = "", string $key = "") {
        $this->setConfigVariableName($config);
        $this->key = $key;
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

    public function execute(Recipe $origin): \Generator {
        $this->throwIfCannotExecute();

        $key = $origin->replaceVariables($this->getKey());

        $config = $this->getConfig($origin);

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