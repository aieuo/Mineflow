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

    private EntityArgument $entity;
    private StringArgument $effectId;
    private NumberArgument $time;
    private NumberArgument $power;
    private BooleanArgument $visible;

    public function __construct(string $entity = "", string $effectId = "", int $time = 300, int $power = 1, bool $visible = false) {
        parent::__construct(self::ADD_EFFECT, FlowItemCategory::ENTITY);

        $this->setArguments([
            $this->entity = new EntityArgument("entity", $entity),
            $this->effectId = new StringArgument("effect", $effectId, example: "1"),
            $this->time = new NumberArgument("time", $time, example: "300", min: 0, max: Limits::INT32_MAX),
            $this->power = new NumberArgument("power", $power, example: "1", min: 0, max: 255),
            $this->visible = new BooleanArgument("visible", $visible),
        ]);
    }

    public function getEntity(): EntityArgument {
        return $this->entity;
    }

    public function getEffectId(): StringArgument {
        return $this->effectId;
    }

    public function getPower(): NumberArgument {
        return $this->power;
    }

    public function getTime(): NumberArgument {
        return $this->time;
    }

    public function getVisible(): BooleanArgument {
        return $this->visible;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $effectId = $this->effectId->getString($source);
        $time = $this->time->getInt($source);
        $power = $this->power->getInt($source);
        $entity = $this->entity->getOnlineEntity($source);

        $effect = StringToEffectParser::getInstance()->parse($effectId);
        if ($effect === null) $effect = EffectIdMap::getInstance()->fromId((int)$effectId);
        if ($effect === null) throw new InvalidFlowValueException($this->getName(), Language::get("action.effect.notFound", [$effectId]));

        if ($entity instanceof Living) {
            $entity->getEffects()->add(new EffectInstance($effect, $time * 20, $power - 1, $this->visible->getBool()));
        }

        yield Await::ALL;
    }
}
