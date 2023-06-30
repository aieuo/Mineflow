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

    private PlayerArgument $player;
    private StringArgument $gamerule;
    private BooleanArgument $value;

    public function __construct(string $player = "", string $gamerule = "", bool $value = true) {
        parent::__construct(self::SEND_BOOL_GAMERULE, FlowItemCategory::PLAYER);

        $this->setArguments([
            $this->player = new PlayerArgument("player", $player),
            $this->gamerule = new StringArgument("gamerule", $gamerule, "@action.setGamerule.form.gamerule", example: "showcoordinates"),
            $this->value = new BooleanArgument("value", $value),
        ]);
    }

    public function getPlayer(): PlayerArgument {
        return $this->player;
    }

    public function getGamerule(): StringArgument {
        return $this->gamerule;
    }

    public function getValue(): BooleanArgument {
        return $this->value;
    }

    public function onExecute(FlowItemExecutor $source): \Generator {
        $gamerule = $this->gamerule->getString($source);
        $player = $this->player->getOnlinePlayer($source);

        $pk = GameRulesChangedPacket::create([
            $gamerule => new BoolGameRule($this->value->getBool(), true),
        ]);
        $player->getNetworkSession()->sendDataPacket($pk);
        yield Await::ALL;
    }
}
