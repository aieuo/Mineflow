<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\argument\HumanArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use SOFe\AwaitGenerator\Await;

class Emote extends SimpleAction {

    private HumanArgument $human;
    private StringArgument $emote;

    public function __construct(string $player = "", string $emote = "") {
        parent::__construct(self::EMOTE, FlowItemCategory::PLAYER);

        $this->setArguments([
            $this->human = new HumanArgument("player", $player),
            $this->emote = new StringArgument("id", $emote, example: "18891e6c-bb3d-47f6-bc15-265605d86525"),
        ]);
    }

    public function getHuman(): HumanArgument {
        return $this->human;
    }

    public function getEmote(): StringArgument {
        return $this->emote;
    }

    public function onExecute(FlowItemExecutor $source): \Generator {
        $emoteId = $this->emote->getString($source);

        $player = $this->human->getOnlineHuman($source);
        $player->emote($emoteId);
        yield Await::ALL;
    }
}
