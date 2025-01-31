<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\argument\HumanArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\libs\_6b4cfdc0a11de6c9\SOFe\AwaitGenerator\Await;

class Emote extends SimpleAction {

    public function __construct(string $player = "", string $emote = "") {
        parent::__construct(self::EMOTE, FlowItemCategory::PLAYER);

        $this->setArguments([
            HumanArgument::create("player", $player),
            StringArgument::create("id", $emote)->example("18891e6c-bb3d-47f6-bc15-265605d86525"),
        ]);
    }

    public function getHuman(): HumanArgument {
        return $this->getArgument("player");
    }

    public function getEmote(): StringArgument {
        return $this->getArgument("id");
    }

    public function onExecute(FlowItemExecutor $source): \Generator {
        $emoteId = $this->getEmote()->getString($source);

        $player = $this->getHuman()->getOnlineHuman($source);
        $player->emote($emoteId);
        yield Await::ALL;
    }
}