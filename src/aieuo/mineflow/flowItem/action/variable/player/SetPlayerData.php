<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\action\variable\player;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\flowItem\placeholder\PlayerPlaceholder;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\variable\CustomVariableData;
use aieuo\mineflow\variable\object\PlayerVariable;
use SOFe\AwaitGenerator\Await;

class SetPlayerData extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private PlayerPlaceholder $player;

    public function __construct(
        string         $player = "",
        private string $dataName = "",
        private string $data = "",
    ) {
        parent::__construct(self::SET_PLAYER_DATA, FlowItemCategory::PLAYER_DATA);
        $this->player = new PlayerPlaceholder("player", $player);
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->player->getName(), "name", "data"];
    }

    public function getDetailReplaces(): array {
        return [$this->player->get(), $this->getDataName(), $this->getData()];
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

    public function isDataValid(): bool {
        return $this->player->get() !== "" and $this->dataName !== "" and $this->data !== "";
    }

    public function getPlayer(): PlayerPlaceholder {
        return $this->player;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $helper = Mineflow::getVariableHelper();

        $player = $this->player->getPlayer($source);
        $name = $source->replaceVariables($this->getDataName());
        $variable = $helper->copyOrCreateVariable($this->getData(), $source);

        $data = $helper->getCustomVariableData(PlayerVariable::getTypeName(), $name);
        if ($data === null) {
            $helper->setCustomVariableData(PlayerVariable::getTypeName(), $name, $data = new CustomVariableData([]));
        }

        $data->setData($player->getName(), $variable);

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->player->createFormElement($variables),
            new ExampleInput("@action.setPlayerData.form.name", "tag", $this->getDataName(), true),
            new ExampleInput("@action.setPlayerData.form.data", "aieuo", $this->getData(), true),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->player->set($content[0]);
        $this->setDataName($content[1]);
        $this->setData($content[2]);
    }

    public function serializeContents(): array {
        return [$this->player->get(), $this->getDataName(), $this->getData()];
    }
}
