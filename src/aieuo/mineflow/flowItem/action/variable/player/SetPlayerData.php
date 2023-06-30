<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\action\variable\player;

use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\variable\CustomVariableData;
use aieuo\mineflow\variable\object\PlayerVariable;
use SOFe\AwaitGenerator\Await;

class SetPlayerData extends SimpleAction {

    private PlayerArgument $player;
    private StringArgument $dataName;
    private StringArgument $data;

    public function __construct(string $player = "", string $dataName = "", string $data = "") {
        parent::__construct(self::SET_PLAYER_DATA, FlowItemCategory::PLAYER_DATA);

        $this->setArguments([
            $this->player = new PlayerArgument("player", $player),
            $this->dataName = new StringArgument("name", $dataName, example: "tag"),
            $this->data = new StringArgument("data", $data, example: "aieuo"),
        ]);
    }

    public function getPlayer(): PlayerArgument {
        return $this->player;
    }

    public function getDataName(): StringArgument {
        return $this->dataName;
    }

    public function getData(): StringArgument {
        return $this->data;
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
}
