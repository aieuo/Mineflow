<?php
declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use SOFe\AwaitGenerator\Await;

class TransferServer extends SimpleAction {

    private PlayerArgument $player;
    private StringArgument $ip;
    private NumberArgument $port;

    public function __construct(string $player = "", string $ip = "", int $port = 19132) {
        parent::__construct(self::TRANSFER_SERVER, FlowItemCategory::PLAYER);

        $this->setArguments([
            $this->player = new PlayerArgument("player", $player),
            $this->ip = new StringArgument("ip", $ip, example: "aieuo.tokyo"),
            $this->port = new NumberArgument("port", $port, example: "19132", min: 1, max: 65535),
        ]);
    }

    public function getPlayer(): PlayerArgument {
        return $this->player;
    }

    public function getIp(): StringArgument {
        return $this->ip;
    }

    public function getPort(): NumberArgument {
        return $this->port;
    }

    public function onExecute(FlowItemExecutor $source): \Generator {
        $ip = $this->ip->getString($source);
        $port = $this->port->getInt($source);

        $player = $this->player->getOnlinePlayer($source);
        $player->transfer($ip, $port);
        yield Await::ALL;
    }
}
