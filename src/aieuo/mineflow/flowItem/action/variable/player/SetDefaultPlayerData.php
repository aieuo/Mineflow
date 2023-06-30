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
use SOFe\AwaitGenerator\Await;

class SetDefaultPlayerData extends SimpleAction {

    private StringArgument $dataName;
    private StringArgument $defaultValue;

    public function __construct(string $dataName = "", string $defaultValue = "") {
        parent::__construct(self::SET_DEFAULT_PLAYER_DATA, FlowItemCategory::PLAYER_DATA);

        $this->setArguments([
            $this->dataName = new StringArgument("name", $dataName, "@action.setPlayerData.form.name", example: "tag"),
            $this->defaultValue = new StringArgument("default", $defaultValue, "@action.setPlayerData.form.default", example: "aieuo", optional: true),
        ]);
    }

    public function getDataName(): StringArgument {
        return $this->dataName;
    }

    public function getDefaultValue(): StringArgument {
        return $this->defaultValue;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $helper = Mineflow::getVariableHelper();

        $name = $this->dataName->getString($source);
        $default = $this->defaultValue->get() === "" ? null : $helper->copyOrCreateVariable($this->defaultValue->get(), $source);

        $data = $helper->getCustomVariableData(PlayerVariable::getTypeName(), $name);
        if ($data === null) {
            $helper->setCustomVariableData(PlayerVariable::getTypeName(), $name, $data = new CustomVariableData([]));
        }
        $data->setDefault($default);

        yield Await::ALL;
    }
}
