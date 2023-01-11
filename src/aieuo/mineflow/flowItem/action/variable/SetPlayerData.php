<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\action\variable;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\PlayerVariableDropdown;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\variable\CustomVariableData;
use aieuo\mineflow\variable\object\PlayerVariable;
use SOFe\AwaitGenerator\Await;

class SetPlayerData extends FlowItem implements PlayerFlowItem {
    use PlayerFlowItemTrait;
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    public function __construct(
        string         $player = "",
        private string $dataName = "",
        private string $data = "",
        private string $defaultValue = ""
    ) {
        parent::__construct(self::SET_PLAYER_DATA, FlowItemCategory::VARIABLE);
        $this->setPlayerVariableName($player);
    }

    public function getDetailDefaultReplaces(): array {
        return ["player", "name", "data"];
    }

    public function getDetailReplaces(): array {
        return [$this->getPlayerVariableName(), $this->getDataName(), $this->getData()];
    }

    public function getDataName(): string {
        return $this->dataName;
    }

    public function setDataName(string $dataName): void {
        $this->dataName = $dataName;
    }

    public function getData(): string {
        return $this->data;
    }

    public function setData(string $data): void {
        $this->data = $data;
    }

    public function getDefaultValue(): string {
        return $this->defaultValue;
    }

    public function setDefaultValue(string $defaultValue): void {
        $this->defaultValue = $defaultValue;
    }

    public function isDataValid(): bool {
        return $this->getPlayerVariableName() !== "" and $this->dataName !== "" and $this->data !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $helper = Mineflow::getVariableHelper();

        $player = $this->getPlayer($source);
        $name = $source->replaceVariables($this->getDataName());
        $variable = $helper->copyOrCreateVariable($this->getData(), $source);
        $default = $this->getDefaultValue() === "" ? null : $helper->copyOrCreateVariable($this->getDefaultValue(), $source);

        $data = $helper->getCustomVariableData(PlayerVariable::getTypeName(), $name);
        if ($data === null) {
            $helper->setCustomVariableData(PlayerVariable::getTypeName(), $name, $data = new CustomVariableData([]));
        }

        $data->setData($player->getName(), $variable);
        $data->setDefault($default);

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            new PlayerVariableDropdown($variables, $this->getPlayerVariableName()),
            new ExampleInput("@action.setPlayerData.form.name", "tag", $this->getDataName(), true),
            new ExampleInput("@action.setPlayerData.form.data", "aieuo", $this->getData(), true),
            new ExampleInput("@action.setPlayerData.form.default", "aieuo", $this->getDefaultValue(), false),
        ]);
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setPlayerVariableName($content[0]);
        $this->setDataName($content[1]);
        $this->setData($content[2]);
        $this->setDefaultValue($content[3]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getPlayerVariableName(), $this->getDataName(), $this->getData(), $this->getDefaultValue()];
    }
}
