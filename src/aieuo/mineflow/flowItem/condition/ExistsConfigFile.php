<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\Main;

class ExistsConfigFile extends Condition {

    protected $id = self::EXISTS_CONFIG_FILE;

    protected $name = "condition.existsConfigFile.name";
    protected $detail = "condition.existsConfigFile.detail";
    protected $detailDefaultReplace = ["name"];

    protected $category = Category::SCRIPT;

    /** @var string */
    private $fileName = "";

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

    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        $name = $origin->replaceVariables($this->getFileName());
        $name = preg_replace("#[.¥/:?<>|*\"]#", "", preg_quote($name));

        return file_exists(Main::getInstance()->getDataFolder()."/configs/".$name.".yml");
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@action.createConfigVariable.form.name", Language::get("form.example", ["config"]), $default[1] ?? $this->getFileName()),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $errors = [];
        if ($data[1] === "") $errors[] = ["@form.insufficient", 1];
        if (preg_match("#[.¥/:?<>|*\"]#", preg_quote($data[1]))) $errors = ["@form.recipe.invalidName", 1];
        return ["status" => empty($errors), "contents" => [$data[1]], "cancel" => $data[2], "errors" => $errors];
    }

    public function loadSaveData(array $content): Condition {
        if (!isset($content[0])) throw new \OutOfBoundsException();

        $this->setFileName($content[0]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getFileName()];
    }
}