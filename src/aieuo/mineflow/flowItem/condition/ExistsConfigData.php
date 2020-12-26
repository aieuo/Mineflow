<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\flowItem\base\ConfigFileFlowItem;
use aieuo\mineflow\flowItem\base\ConfigFileFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\mineflow\ConfigVariableDropdown;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;

class ExistsConfigData extends FlowItem implements Condition, ConfigFileFlowItem {
    use ConfigFileFlowItemTrait;

    protected $id = self::EXISTS_CONFIG_DATA;

    protected $name = "condition.existsConfigData.name";
    protected $detail = "condition.existsConfigData.detail";
    protected $detailDefaultReplace = ["config", "key"];

    protected $category = Category::SCRIPT;

    /** @var string */
    private $key;

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

    public function execute(Recipe $origin): \Generator {
        $this->throwIfCannotExecute();

        $config = $this->getConfig($origin);
        $this->throwIfInvalidConfig($config);

        $key = $origin->replaceVariables($this->getKey());

        yield true;
        return $config->getNested($key) !== null;
    }

    public function getEditForm(array $variables = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new ConfigVariableDropdown($variables, $this->getConfigVariableName()),
                new ExampleInput("@condition.existsConfigData.form.key", "aieuo", $this->getKey(), true),
                new CancelToggle()
            ]);
    }

    public function parseFromFormData(array $data): array {
        return ["contents" => [$data[1], $data[2]], "cancel" => $data[3]];
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