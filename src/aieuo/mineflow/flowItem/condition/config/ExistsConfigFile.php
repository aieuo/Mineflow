<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\config;

use aieuo\mineflow\exception\InvalidFormValueException;
use aieuo\mineflow\flowItem\base\ConditionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\condition\Condition;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\EditFormResponseProcessor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\Main;
use aieuo\mineflow\utils\Utils;
use SOFe\AwaitGenerator\Await;

class ExistsConfigFile extends FlowItem implements Condition {
    use ConditionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    public function __construct(private string $fileName = "") {
        parent::__construct(self::EXISTS_CONFIG_FILE, FlowItemCategory::CONFIG);
    }

    public function getDetailDefaultReplaces(): array {
        return ["name"];
    }

    public function getDetailReplaces(): array {
        return [$this->getFileName()];
    }

    public function setFileName(string $name): void {
        $this->fileName = $name;
    }

    public function getFileName(): string {
        return $this->fileName;
    }

    public function isDataValid(): bool {
        return $this->getFileName() !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $name = Utils::getValidFileName($source->replaceVariables($this->getFileName()));

        yield Await::ALL;
        return file_exists(Main::getInstance()->getDataFolder()."/configs/".$name.".yml");
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            new ExampleInput("@action.createConfig.form.name", "config", $this->getFileName(), true),
        ])->response(function (EditFormResponseProcessor $response) {
            $response->validate(function (array $data) {
                if (!Utils::isValidFileName($data[0])) {
                    throw new InvalidFormValueException("@form.recipe.invalidName", 0);
                }
            });
        });
    }

    public function loadSaveData(array $content): void {
        $this->setFileName($content[0]);
    }

    public function serializeContents(): array {
        return [$this->getFileName()];
    }
}
