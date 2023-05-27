<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\action\variable\player;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\variable\CustomVariableData;
use aieuo\mineflow\variable\object\PlayerVariable;
use SOFe\AwaitGenerator\Await;

class SetDefaultPlayerData extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    public function __construct(
        private string $dataName = "",
        private string $defaultValue = ""
    ) {
        parent::__construct(self::SET_DEFAULT_PLAYER_DATA, FlowItemCategory::PLAYER_DATA);
    }

    public function getDetailDefaultReplaces(): array {
        return ["name", "default"];
    }

    public function getDetailReplaces(): array {
        return [$this->getDataName(), $this->getDefaultValue()];
    }

    public function getDataName(): string {
        return $this->dataName;
    }

    public function setDataName(string $dataName): void {
        $this->dataName = $dataName;
    }

    public function getDefaultValue(): string {
        return $this->defaultValue;
    }

    public function setDefaultValue(string $defaultValue): void {
        $this->defaultValue = $defaultValue;
    }

    public function isDataValid(): bool {
        return $this->dataName !== "" and $this->defaultValue !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $helper = Mineflow::getVariableHelper();

        $name = $source->replaceVariables($this->getDataName());
        $default = $this->getDefaultValue() === "" ? null : $helper->copyOrCreateVariable($this->getDefaultValue(), $source);

        $data = $helper->getCustomVariableData(PlayerVariable::getTypeName(), $name);
        if ($data === null) {
            $helper->setCustomVariableData(PlayerVariable::getTypeName(), $name, $data = new CustomVariableData([]));
        }
        $data->setDefault($default);

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            new ExampleInput("@action.setPlayerData.form.name", "tag", $this->getDataName(), true),
            new ExampleInput("@action.setPlayerData.form.default", "aieuo", $this->getDefaultValue(), false),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->setDataName($content[0]);
        $this->setDefaultValue($content[1]);
    }

    public function serializeContents(): array {
        return [$this->getDataName(), $this->getDefaultValue()];
    }
}
