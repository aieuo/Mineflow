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
use aieuo\mineflow\libs\_f6944d67f135f2dc\SOFe\AwaitGenerator\Await;

class SetPlayerData extends SimpleAction {

    public function __construct(string $player = "", string $dataName = "", string $data = "") {
        parent::__construct(self::SET_PLAYER_DATA, FlowItemCategory::PLAYER_DATA);

        $this->setArguments([
            PlayerArgument::create("player", $player),
            StringArgument::create("name", $dataName)->example("tag"),
            StringArgument::create("data", $data)->example("aieuo"),
        ]);
    }

    public function getPlayer(): PlayerArgument {
        return $this->getArgument("player");
    }

    public function getDataName(): StringArgument {
        return $this->getArgument("name");
    }

    public function getData(): StringArgument {
        return $this->getArgument("data");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $helper = Mineflow::getVariableHelper();

        $player = $this->getPlayer()->getPlayer($source);
        $name = $this->getDataName()->getString($source);
        $variable = $helper->copyOrCreateVariable($this->getData()->getRawString(), $source->getVariableRegistryCopy());

        $data = $helper->getCustomVariableData(PlayerVariable::getTypeName(), $name);
        if ($data === null) {
            $helper->setCustomVariableData(PlayerVariable::getTypeName(), $name, $data = new CustomVariableData([]));
        }

        $data->setData($player->getName(), $variable);

        yield Await::ALL;
    }
}