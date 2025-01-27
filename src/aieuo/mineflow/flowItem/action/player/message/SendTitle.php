<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player\message;

use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use SOFe\AwaitGenerator\Await;

class SendTitle extends SimpleAction {

    public function __construct(
        string $player = "",
        string $title = "",
        string $subtitle = "",
        int $fadein = -1,
        int $stay = -1,
        int $fadeout = -1
    ) {
        parent::__construct(self::SEND_TITLE, FlowItemCategory::PLAYER_MESSAGE);

        $this->setArguments([
            PlayerArgument::create("player", $player),
            StringArgument::create("title", $title)->optional()->example("aieuo"),
            StringArgument::create("subtitle", $subtitle)->optional()->example("aieuo"),
            NumberArgument::create("fadein", $fadein)->min(-1)->example("-1"),
            NumberArgument::create("stay", $stay)->min(-1)->example("-1"),
            NumberArgument::create("fadeout", $fadeout)->min(-1)->example("-1"),
        ]);
    }

    public function getPlayer(): PlayerArgument {
        return $this->getArgument("player");
    }

    public function getTitle(): StringArgument {
        return $this->getArgument("title");
    }

    public function getSubTitle(): StringArgument {
        return $this->getArgument("subtitle");
    }

    public function getFadein(): NumberArgument {
        return $this->getArgument("fadein");
    }

    public function getStay(): NumberArgument {
        return $this->getArgument("stay");
    }

    public function getFadeout(): NumberArgument {
        return $this->getArgument("fadeout");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $title = $this->getTitle()->getString($source);
        $subtitle = $this->getSubtitle()->getString($source);
        $fadein = $this->getFadein()->getInt($source);
        $stay = $this->getStay()->getInt($source);
        $fadeout = $this->getFadeout()->getInt($source);
        $player = $this->getPlayer()->getOnlinePlayer($source);

        $player->sendTitle($title, $subtitle, $fadein, $stay, $fadeout);

        yield Await::ALL;
    }
}