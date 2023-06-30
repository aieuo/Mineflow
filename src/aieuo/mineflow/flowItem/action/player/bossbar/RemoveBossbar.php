<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player\bossbar;

use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Bossbar;
use SOFe\AwaitGenerator\Await;

class RemoveBossbar extends SimpleAction {

    private PlayerArgument $player;
    private StringArgument $barId;

    public function __construct(string $player = "", string $barId = "") {
        parent::__construct(self::REMOVE_BOSSBAR, FlowItemCategory::BOSSBAR);

        $this->setArguments([
            $this->player = new PlayerArgument("player", $player),
            $this->barId = new StringArgument("id", $barId, "@action.showBossbar.form.id", example: "aieuo"),
        ]);
    }

    public function getPlayer(): PlayerArgument {
        return $this->player;
    }

    public function getBarId(): StringArgument {
        return $this->barId;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $id = $this->barId->getString($source);
        $player = $this->player->getOnlinePlayer($source);

        Bossbar::remove($player, $id);

        yield Await::ALL;
    }
}
