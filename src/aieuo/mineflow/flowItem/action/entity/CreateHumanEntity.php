<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\entity\MineflowHuman;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\placeholder\PlayerPlaceholder;
use aieuo\mineflow\flowItem\placeholder\PositionPlaceholder;
use aieuo\mineflow\flowItem\placeholder\StringPlaceholder;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\EntityVariable;
use aieuo\mineflow\variable\object\HumanVariable;
use pocketmine\entity\Location;
use SOFe\AwaitGenerator\Await;

class CreateHumanEntity extends SimpleAction {

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    private PlayerPlaceholder $player;
    private PositionPlaceholder $position;
    private StringPlaceholder $resultName;

    public function __construct(string $name = "", string $pos = "", string $resultName = "human") {
        parent::__construct(self::CREATE_HUMAN_ENTITY, FlowItemCategory::ENTITY);

        $this->player = new PlayerPlaceholder("skin", $name, "@action.createHuman.form.skin");
        $this->position = new PositionPlaceholder("pos", $pos);
        $this->resultName = new StringPlaceholder("result", $resultName, example: "entity");
    }

    public function getPlayer(): PlayerPlaceholder {
        return $this->player;
    }

    public function getPosition(): PositionPlaceholder {
        return $this->position;
    }

    public function getResultName(): StringPlaceholder {
        return $this->resultName;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $player = $this->player->getOnlinePlayer($source);
        $pos = $this->position->getPosition($source);

        $resultName = $this->resultName->getString($source);

        if (!($pos instanceof Location)) $pos = Location::fromObject($pos, $pos->getWorld());
        $entity = new MineflowHuman($pos, $player->getSkin());
        $entity->spawnToAll();

        $variable = new HumanVariable($entity);
        $source->addVariable($resultName, $variable);

        yield Await::ALL;
        return $this->resultName->get();
    }

    public function getAddingVariables(): array {
        return [
            $this->resultName->get() => new DummyVariable(EntityVariable::class, $this->player->get())
        ];
    }
}
