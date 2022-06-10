<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\script;

use aieuo\mineflow\exception\InvalidFormValueException;
use aieuo\mineflow\flowItem\base\ConditionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\condition\Condition;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\Main;

class ExistsConfigFile extends FlowItem implements Condition {
    use ConditionNameWithMineflowLanguage;

    public function __construct(private string $fileName = "") {
        parent::__construct(self::EXISTS_CONFIG_FILE, FlowItemCategory::CONFIG);
    }

    public function getDetailDefaultReplaces(): array {
        return ["name"];
    }

    public function getDetailReplaces(): array {
        return [$this->getFileName()];
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

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $name = $source->replaceVariables($this->getFileName());
        $name = preg_replace("#[.¥/:?<>|*\"]#u", "", preg_quote($name, "/@#~"));

        yield true;
        return file_exists(Main::getInstance()->getDataFolder()."/configs/".$name.".yml");
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ExampleInput("@action.createConfig.form.name", "config", $this->getFileName(), true),
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
