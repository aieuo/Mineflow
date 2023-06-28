<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\argument\EntityArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;
use pocketmine\data\bedrock\EffectIdMap;
use pocketmine\entity\effect\StringToEffectParser;
use pocketmine\entity\Living;
use SOFe\AwaitGenerator\Await;

class ClearEffect extends SimpleAction {

    private EntityArgument $entity;
    private StringArgument $effectId;

    public function __construct(string $entity = "", string $effectId = "") {
        parent::__construct(self::CLEAR_EFFECT, FlowItemCategory::ENTITY);

        $this->setArguments([
            $this->entity = new EntityArgument("entity", $entity),
            $this->effectId = new StringArgument("id", $effectId, example: "1"),
        ]);
    }

    public function getEntity(): EntityArgument {
        return $this->entity;
    }

    public function getEffectId(): StringArgument {
        return $this->effectId;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $effectId = $this->effectId->getString($source);

        $effect = StringToEffectParser::getInstance()->parse($effectId);
        if ($effect === null) $effect = EffectIdMap::getInstance()->fromId((int)$effectId);
        if ($effect === null) throw new InvalidFlowValueException($this->getName(), Language::get("action.effect.notFound", [$effectId]));

        $entity = $this->entity->getOnlineEntity($source);

        if ($entity instanceof Living) {
            $entity->getEffects()->remove($effect);
        }

        yield Await::ALL;
    }
}
