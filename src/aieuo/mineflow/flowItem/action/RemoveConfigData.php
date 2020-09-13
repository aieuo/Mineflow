<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\ConfigFileFlowItem;
use aieuo\mineflow\flowItem\base\ConfigFileFlowItemTrait;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\Main;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\NumberVariable;

class RemoveConfigData extends Action implements ConfigFileFlowItem {
    use ConfigFileFlowItemTrait;

    protected $id = self::REMOVE_CONFIG_VALUE;

    protected $name = "action.removeConfigData.name";
    protected $detail = "action.removeConfigData.detail";
    protected $detailDefaultReplace = ["config", "key"];

    protected $category = Category::SCRIPT;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;

    protected $permission = self::PERMISSION_LEVEL_2;

    /** @var string */
    private $key;

    public function __construct(string $config = "config", string $key = "") {
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

    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        $key = $origin->replaceVariables($this->getKey());

        $config = $this->getConfig($origin);
        $this->throwIfInvalidConfig($config);

        $config->removeNested($key);
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@flowItem.form.target.player", Language::get("form.example", ["target"]), $default[1] ?? $this->getConfigVariableName()),
                new Input("@action.setConfigData.form.key", Language::get("form.example", ["aieuo"]), $default[2] ?? $this->getKey()),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $errors = [];
        if ($data[1] === "") $errors[] = ["@form.insufficient", 1];
        if ($data[2] === "") $errors[] = ["@form.insufficient", 2];
        return ["contents" => [$data[1], $data[2]], "cancel" => $data[3], "errors" => $errors];
    }

    public function loadSaveData(array $content): Action {
        if (!isset($content[1])) throw new \OutOfBoundsException();
        $this->setConfigVariableName($content[0]);
        $this->setKey($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getConfigVariableName(), $this->getKey()];
    }
}