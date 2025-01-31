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
use aieuo\mineflow\libs\_6b4cfdc0a11de6c9\SOFe\AwaitGenerator\Await;

class ClearEffect extends SimpleAction {

    public function __construct(string $entity = "", string $effectId = "") {
        parent::__construct(self::CLEAR_EFFECT, FlowItemCategory::ENTITY);

        $this->setArguments([
            EntityArgument::create("entity", $entity),
            StringArgument::create("id", $effectId)->example("1"),
        ]);
    }

    public function getEntity(): EntityArgument {
        return $this->getArgument("entity");
    }

    public function getEffectId(): StringArgument {
        return $this->getArgument("id");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $effectId = $this->getEffectId()->getString($source);

        $effect = StringToEffectParser::getInstance()->parse($effectId);
        if ($effect === null) $effect = EffectIdMap::getInstance()->fromId((int)$effectId);
        if ($effect === null) throw new InvalidFlowValueException($this->getName(), Language::get("action.effect.notFound", [$effectId]));

        $entity = $this->getEntity()->getOnlineEntity($source);

        if ($entity instanceof Living) {
            $entity->getEffects()->remove($effect);
        }

        yield Await::ALL;
    }
}