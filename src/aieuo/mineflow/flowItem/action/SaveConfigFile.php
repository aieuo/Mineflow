<?php

namespace aieuo\mineflow\flowItem\action;
use aieuo\mineflow\flowItem\base\ConfigFileFlowItem;
use aieuo\mineflow\flowItem\base\ConfigFileFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\ExampleInput;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\CustomForm;

class SaveConfigFile extends FlowItem implements ConfigFileFlowItem {
    use ConfigFileFlowItemTrait;

    protected $id = self::SAVE_CONFIG_FILE;

    protected $name = "action.saveConfigFile.name";
    protected $detail = "action.saveConfigFile.detail";
    protected $detailDefaultReplace = ["config"];

    protected $category = Category::SCRIPT;

    protected $targetRequired = Recipe::TARGET_REQUIRED_PLAYER;

    protected $permission = self::PERMISSION_LEVEL_2;

    public function __construct(string $config = "config") {
        $this->setConfigVariableName($config);
    }

    public function isDataValid(): bool {
        return $this->getConfigVariableName() !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getConfigVariableName()]);
    }

    public function execute(Recipe $origin) {
        $this->throwIfCannotExecute();

        $config = $this->getConfig($origin);
        $this->throwIfInvalidConfig($config);

        $config->save();
        yield true;
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new ExampleInput("@flowItem.form.target.config", "config", $default[1] ?? $this->getConfigVariableName(), true),
                new CancelToggle()
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        return ["contents" => [$data[1]], "cancel" => $data[2], "errors" => []];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setConfigVariableName($content[0]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getConfigVariableName()];
    }
}