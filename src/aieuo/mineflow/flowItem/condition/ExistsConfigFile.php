<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\exception\InvalidFormValueException;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\Main;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;

class ExistsConfigFile extends FlowItem implements Condition {

    protected string $id = self::EXISTS_CONFIG_FILE;

    protected string $name = "condition.existsConfigFile.name";
    protected string $detail = "condition.existsConfigFile.detail";
    protected array $detailDefaultReplace = ["name"];

    protected string $category = Category::CONFIG;

    private string $fileName;

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

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $name = $source->replaceVariables($this->getFileName());
        $name = preg_replace("#[.¥/:?<>|*\"]#u", "", preg_quote($name, "/@#~"));

        yield true;
        return file_exists(Main::getInstance()->getDataFolder()."/configs/".$name.".yml");
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ExampleInput("@action.createConfigVariable.form.name", "config", $this->getFileName(), true),
        ];
    }

    public function parseFromFormData(array $data): array {
        if (preg_match("#[.¥/:?<>|*\"]#u", preg_quote($data[0], "/@#~"))) throw new InvalidFormValueException("@form.recipe.invalidName", 0);
        return [$data[0]];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setFileName($content[0]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getFileName()];
    }
}