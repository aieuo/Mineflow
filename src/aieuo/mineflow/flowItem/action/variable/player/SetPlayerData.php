<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\action\variable\player;

use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\variable\CustomVariableData;
use aieuo\mineflow\variable\object\PlayerVariable;
use SOFe\AwaitGenerator\Await;

class SetPlayerData extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private PlayerArgument $player;
    private StringArgument $dataName;
    private StringArgument $data;

    public function __construct(string $player = "", string $dataName = "", string $data = "") {
        parent::__construct(self::SET_PLAYER_DATA, FlowItemCategory::PLAYER_DATA);

        $this->player = new PlayerArgument("player", $player);
        $this->dataName = new StringArgument("name", $dataName, example: "tag");
        $this->data = new StringArgument("data", $data, example: "aieuo");
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->player->getName(), "name", "data"];
    }

    public function getDetailReplaces(): array {
        return [$this->player->get(), $this->dataName->get(), $this->data->get()];
    }

    public function getDataName(): StringArgument {
        return $this->dataName;
    }

    public function getData(): StringArgument {
        return $this->data;
    }

    public function isDataValid(): bool {
        return $this->player->get() !== "" and $this->dataName->isNotEmpty() and $this->data->isNotEmpty();
    }

    public function getPlayer(): PlayerArgument {
        return $this->player;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $helper = Mineflow::getVariableHelper();

        $player = $this->player->getPlayer($source);
        $name = $this->dataName->getString($source);
        $variable = $helper->copyOrCreateVariable($this->data->get(), $source);

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
            $this->dataName->createFormElement($variables),
            $this->data->createFormElement($variables),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->player->set($content[0]);
        $this->dataName->set($content[1]);
        $this->data->set($content[2]);
    }

    public function serializeContents(): array {
        return [$this->player->get(), $this->dataName->get(), $this->data->get()];
    }
}
