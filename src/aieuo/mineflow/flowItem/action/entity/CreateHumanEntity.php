<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\entity\MineflowHuman;
use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\argument\PositionArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\EntityVariable;
use aieuo\mineflow\variable\object\HumanVariable;
use pocketmine\entity\Location;
use SOFe\AwaitGenerator\Await;

class CreateHumanEntity extends SimpleAction {

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    public function __construct(string $name = "", string $pos = "", string $resultName = "human") {
        parent::__construct(self::CREATE_HUMAN_ENTITY, FlowItemCategory::ENTITY);

        $this->setArguments([
            new PlayerArgument("skin", $name, "@action.createHuman.form.skin"),
            new PositionArgument("pos", $pos),
            new StringArgument("result", $resultName, example: "entity"),
        ]);
    }

    public function getPlayer(): PlayerArgument {
        return $this->getArguments()[0];
    }

    public function getPosition(): PositionArgument {
        return $this->getArguments()[1];
    }

    public function getResultName(): StringArgument {
        return $this->getArguments()[2];
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $player = $this->getPlayer()->getOnlinePlayer($source);
        $pos = $this->getPosition()->getPosition($source);

        $resultName = $this->getResultName()->getString($source);

        if (!($pos instanceof Location)) $pos = Location::fromObject($pos, $pos->getWorld());
        $entity = new MineflowHuman($pos, $player->getSkin());
        $entity->spawnToAll();

        $variable = new HumanVariable($entity);
        $source->addVariable($resultName, $variable);

        yield Await::ALL;
        return (string)$this->getResultName();
    }

    public function getAddingVariables(): array {
        return [
            (string)$this->getResultName() => new DummyVariable(EntityVariable::class, (string)$this->getPlayer())
        ];
    }
}
