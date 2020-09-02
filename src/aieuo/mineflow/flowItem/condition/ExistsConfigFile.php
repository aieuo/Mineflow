<?php

namespace aieuo\mineflow\flowItem\condition;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\ExampleInput;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\Main;

class ExistsConfigFile extends FlowItem implements Condition {

    protected $id = self::EXISTS_CONFIG_FILE;

    protected $name = "condition.existsConfigFile.name";
    protected $detail = "condition.existsConfigFile.detail";
    protected $detailDefaultReplace = ["name"];

    protected $category = Category::SCRIPT;

    /** @var string */
    private $fileName;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;

    public function __construct(string $name = "") {
        $this->fileName = $name;
    }

    public function setFileName(string $name): self {
        $this->fileName = $name;
        return $this;
    }

    public function getFileName(): string {
        return $this->fileName;
    }

    public function isDataValid(): bool {
        return $this->getFileName() !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getFileName()]);
    }

    public function execute(Recipe $origin) {
        $this->throwIfCannotExecute();

        $name = $origin->replaceVariables($this->getFileName());
        $name = preg_replace("#[.¥/:?<>|*\"]#", "", preg_quote($name));

        yield true;
        return file_exists(Main::getInstance()->getDataFolder()."/configs/".$name.".yml");
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new ExampleInput("@action.createConfigVariable.form.name", "config", $default[1] ?? $this->getFileName(), true),
                new CancelToggle()
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $errors = [];
        if (preg_match("#[.¥/:?<>|*\"]#", preg_quote($data[1]))) $errors = ["@form.recipe.invalidName", 1];
        return ["contents" => [$data[1]], "cancel" => $data[2], "errors" => $errors];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setFileName($content[0]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getFileName()];
    }
}