<?php
declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\libs\_3ced88d4028c9717\SOFe\AwaitGenerator\Await;

class TransferServer extends SimpleAction {

    public function __construct(string $player = "", string $ip = "", int $port = 19132) {
        parent::__construct(self::TRANSFER_SERVER, FlowItemCategory::PLAYER);

        $this->setArguments([
            PlayerArgument::create("player", $player),
            StringArgument::create("ip", $ip)->example("aieuo.tokyo"),
            NumberArgument::create("port", $port)->min(1)->max(65535)->example("19132"),
        ]);
    }

    public function getPlayer(): PlayerArgument {
        return $this->getArgument("player");
    }

    public function getIp(): StringArgument {
        return $this->getArgument("ip");
    }

    public function getPort(): NumberArgument {
        return $this->getArgument("port");
    }

    public function onExecute(FlowItemExecutor $source): \Generator {
        $ip = $this->getIp()->getString($source);
        $port = $this->getPort()->getInt($source);

        $player = $this->getPlayer()->getOnlinePlayer($source);
        $player->transfer($ip, $port);
        yield Await::ALL;
    }
}