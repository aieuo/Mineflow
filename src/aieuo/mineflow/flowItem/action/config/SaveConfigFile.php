<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\config;

use aieuo\mineflow\flowItem\base\ConfigFileFlowItem;
use aieuo\mineflow\flowItem\base\ConfigFileFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ConfigVariableDropdown;
use aieuo\mineflow\utils\Language;

class SaveConfigFile extends FlowItem implements ConfigFileFlowItem {
    use ConfigFileFlowItemTrait;

    protected string $name = "action.saveConfig.name";
    protected string $detail = "action.saveConfig.detail";
    protected array $detailDefaultReplace = ["config"];

    public function __construct(string $config = "") {
        parent::__construct(self::SAVE_CONFIG_FILE, FlowItemCategory::CONFIG);

        $this->setConfigVariableName($config);
    }

    public function getPermissions(): array {
        return [self::PERMISSION_CONFIG];
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getConfigVariableName()]);
    }

    public function isDataValid(): bool {
        return $this->getConfigVariableName() !== "";
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $config = $this->getConfig($source);

        $config->save();
        yield true;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ConfigVariableDropdown($variables, $this->getConfigVariableName()),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setConfigVariableName($content[0]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getConfigVariableName()];
    }
}