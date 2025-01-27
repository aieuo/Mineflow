<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\action\variable\player;

use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\variable\CustomVariableData;
use aieuo\mineflow\variable\object\PlayerVariable;
use aieuo\mineflow\libs\_057384fe9e664697\SOFe\AwaitGenerator\Await;

class SetDefaultPlayerData extends SimpleAction {

    public function __construct(string $dataName = "", string $defaultValue = "") {
        parent::__construct(self::SET_DEFAULT_PLAYER_DATA, FlowItemCategory::PLAYER_DATA);

        $this->setArguments([
            StringArgument::create("name", $dataName, "@action.setPlayerData.form.name")->example("tag"),
            StringArgument::create("default", $defaultValue, "@action.setPlayerData.form.default")->optional()->example("aieuo"),
        ]);
    }

    public function getDataName(): StringArgument {
        return $this->getArgument("name");
    }

    public function getDefaultValue(): StringArgument {
        return $this->getArgument("default");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $helper = Mineflow::getVariableHelper();

        $name = $this->getDataName()->getString($source);
        $default = $this->getDefaultValue()->getRawString() === "" ? null : $helper->copyOrCreateVariable($this->getDefaultValue()->getRawString(), $source->getVariableRegistryCopy());

        $data = $helper->getCustomVariableData(PlayerVariable::getTypeName(), $name);
        if ($data === null) {
            $helper->setCustomVariableData(PlayerVariable::getTypeName(), $name, $data = new CustomVariableData([]));
        }
        $data->setDefault($default);

        yield Await::ALL;
    }
}