<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player\message;

use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use pocketmine\network\mcpe\protocol\ToastRequestPacket;
use aieuo\mineflow\libs\_6c37ba9df39eb43f\SOFe\AwaitGenerator\Await;

class SendToast extends SimpleAction {

    public function __construct(string $player = "", string $title = "", string $body = "") {
        parent::__construct(self::SEND_TOAST, FlowItemCategory::PLAYER_MESSAGE);

        $this->setArguments([
            PlayerArgument::create("player", $player),
            StringArgument::create("title", $title)->optional()->example("aieuo"),
            StringArgument::create("body", $body)->optional()->example("aieuo"),
        ]);
    }

    public function getPlayer(): PlayerArgument {
        return $this->getArgument("player");
    }

    public function getTitle(): StringArgument {
        return $this->getArgument("title");
    }

    public function getBody(): StringArgument {
        return $this->getArgument("body");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $title = $this->getTitle()->getString($source);
        $body = $this->getBody()->getString($source);
        $player = $this->getPlayer()->getOnlinePlayer($source);

        $player->getNetworkSession()->sendDataPacket(ToastRequestPacket::create($title, $body));

        yield Await::ALL;
    }
}