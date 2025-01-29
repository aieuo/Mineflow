<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player\bossbar;

use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Bossbar;
use aieuo\mineflow\libs\_6c37ba9df39eb43f\SOFe\AwaitGenerator\Await;

class RemoveBossbar extends SimpleAction {

    public function __construct(string $player = "", string $barId = "") {
        parent::__construct(self::REMOVE_BOSSBAR, FlowItemCategory::BOSSBAR);

        $this->setArguments([
            PlayerArgument::create("player", $player),
            StringArgument::create("id", $barId, "@action.showBossbar.form.id")->example("aieuo"),
        ]);
    }

    public function getPlayer(): PlayerArgument {
        return $this->getArgument("player");
    }

    public function getBarId(): StringArgument {
        return $this->getArgument("id");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $id = $this->getBarId()->getString($source);
        $player = $this->getPlayer()->getOnlinePlayer($source);

        Bossbar::remove($player, $id);

        yield Await::ALL;
    }
}