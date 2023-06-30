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

    private PlayerArgument $player;
    private StringArgument $title;
    private StringArgument $body;

    public function __construct(string $player = "", string $title = "", string $body = "") {
        parent::__construct(self::SEND_TOAST, FlowItemCategory::PLAYER_MESSAGE);

        $this->setArguments([
            $this->player = new PlayerArgument("player", $player),
            $this->title = new StringArgument("title", $title, example: "aieuo", optional: true),
            $this->body = new StringArgument("body", $body, example: "aieuo", optional: true),
        ]);
    }

    public function getPlayer(): PlayerArgument {
        return $this->player;
    }

    public function getTitle(): StringArgument {
        return $this->title;
    }

    public function getBody(): StringArgument {
        return $this->body;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $title = $this->title->getString($source);
        $body = $this->body->getString($source);
        $player = $this->player->getOnlinePlayer($source);

        $player->getNetworkSession()->sendDataPacket(ToastRequestPacket::create($title, $body));

        yield Await::ALL;
    }
}
