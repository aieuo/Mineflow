<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\argument\BooleanArgument;
use aieuo\mineflow\flowItem\argument\EntityArgument;
use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;
use pocketmine\data\bedrock\EffectIdMap;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\StringToEffectParser;
use pocketmine\entity\Living;
use pocketmine\utils\Limits;
use SOFe\AwaitGenerator\Await;

class AddEffect extends SimpleAction {

    public function __construct(string $entity = "", string $effectId = "", int $time = 300, int $power = 1, bool $visible = false) {
        parent::__construct(self::ADD_EFFECT, FlowItemCategory::ENTITY);

        $this->setArguments([
            new EntityArgument("entity", $entity),
            new StringArgument("effect", $effectId, example: "1"),
            new NumberArgument("time", $time, example: "300", min: 0, max: Limits::INT32_MAX),
            new NumberArgument("power", $power, example: "1", min: 0, max: 255),
            new BooleanArgument("visible", $visible),
        ]);
    }

    public function getEntity(): EntityArgument {
        return $this->getArguments()[0];
    }

    public function getEffectId(): StringArgument {
        return $this->getArguments()[1];
    }

    public function getPower(): NumberArgument {
        return $this->getArguments()[3];
    }

    public function getTime(): NumberArgument {
        return $this->getArguments()[2];
    }

    public function getVisible(): BooleanArgument {
        return $this->getArguments()[4];
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $effectId = $this->getEffectId()->getString($source);
        $time = $this->getTime()->getInt($source);
        $power = $this->getPower()->getInt($source);
        $entity = $this->getEntity()->getOnlineEntity($source);

        $effect = StringToEffectParser::getInstance()->parse($effectId);
        if ($effect === null) $effect = EffectIdMap::getInstance()->fromId((int)$effectId);
        if ($effect === null) throw new InvalidFlowValueException($this->getName(), Language::get("action.effect.notFound", [$effectId]));

        if ($entity instanceof Living) {
            $entity->getEffects()->add(new EffectInstance($effect, $time * 20, $power - 1, $this->getVisible()->getBool()));
        }

        yield Await::ALL;
    }
}
