<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\argument\BooleanArgument;
use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use pocketmine\network\mcpe\protocol\GameRulesChangedPacket;
use pocketmine\network\mcpe\protocol\types\BoolGameRule;
use SOFe\AwaitGenerator\Await;

class SendGameRule extends SimpleAction {

    public function __construct(string $player = "", string $gamerule = "", bool $value = true) {
        parent::__construct(self::SEND_BOOL_GAMERULE, FlowItemCategory::PLAYER);

        $this->setArguments([
            new PlayerArgument("player", $player),
            new StringArgument("gamerule", $gamerule, "@action.setGamerule.form.gamerule", example: "showcoordinates"),
            new BooleanArgument("value", $value),
        ]);
    }

    public function getPlayer(): PlayerArgument {
        return $this->getArguments()[0];
    }

    public function getGamerule(): StringArgument {
        return $this->getArguments()[1];
    }

    public function getValue(): BooleanArgument {
        return $this->getArguments()[2];
    }

    public function onExecute(FlowItemExecutor $source): \Generator {
        $gamerule = $this->getGamerule()->getString($source);
        $player = $this->getPlayer()->getOnlinePlayer($source);

        $pk = GameRulesChangedPacket::create([
            $gamerule => new BoolGameRule($this->getValue()->getBool(), true),
        ]);
        $player->getNetworkSession()->sendDataPacket($pk);
        yield Await::ALL;
    }
}
