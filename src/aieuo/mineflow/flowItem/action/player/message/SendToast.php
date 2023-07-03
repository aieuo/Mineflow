<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player\message;

use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use pocketmine\network\mcpe\protocol\ToastRequestPacket;
use SOFe\AwaitGenerator\Await;

class SendToast extends SimpleAction {

    public function __construct(string $player = "", string $title = "", string $body = "") {
        parent::__construct(self::SEND_TOAST, FlowItemCategory::PLAYER_MESSAGE);

        $this->setArguments([
            new PlayerArgument("player", $player),
            new StringArgument("title", $title, example: "aieuo", optional: true),
            new StringArgument("body", $body, example: "aieuo", optional: true),
        ]);
    }

    public function getPlayer(): PlayerArgument {
        return $this->getArguments()[0];
    }

    public function getTitle(): StringArgument {
        return $this->getArguments()[1];
    }

    public function getBody(): StringArgument {
        return $this->getArguments()[2];
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $title = $this->getTitle()->getString($source);
        $body = $this->getBody()->getString($source);
        $player = $this->getPlayer()->getOnlinePlayer($source);

        $player->getNetworkSession()->sendDataPacket(ToastRequestPacket::create($title, $body));

        yield Await::ALL;
    }
}
