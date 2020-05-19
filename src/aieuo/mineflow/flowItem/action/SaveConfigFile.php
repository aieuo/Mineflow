<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\ConfigFileFlowItem;
use aieuo\mineflow\flowItem\base\ConfigFileFlowItemTrait;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Toggle;

class SaveConfigFile extends Action implements ConfigFileFlowItem {
    use ConfigFileFlowItemTrait;

    protected $id = self::SAVE_CONFIG_FILE;

    protected $name = "action.saveConfigFile.name";
    protected $detail = "action.saveConfigFile.detail";
    protected $detailDefaultReplace = ["config"];

    protected $category = Category::SCRIPT;

    protected $targetRequired = Recipe::TARGET_REQUIRED_PLAYER;

    protected $permission = self::PERMISSION_LEVEL_2;

    public function __construct(string $name = "config") {
        $this->configVariableName = $name;
    }

    public function isDataValid(): bool {
        return $this->getConfigVariableName() !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getConfigVariableName()]);
    }

    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        $config = $this->getConfig($origin);
        $this->throwIfInvalidConfig($config);

        $config->save();
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@flowItem.form.target.config", Language::get("form.example", ["config"]), $default[1] ?? $this->getConfigVariableName()),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $errors = [];
        if ($data[1] === "") $errors[] = ["@form.insufficient", 1];
        return ["status" => empty($errors), "contents" => [$data[1]], "cancel" => $data[2], "errors" => $errors];
    }

    public function loadSaveData(array $content): Action {
        if (!isset($content[0])) throw new \OutOfBoundsException();
        $this->setConfigVariableName($content[0]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getConfigVariableName()];
    }
}