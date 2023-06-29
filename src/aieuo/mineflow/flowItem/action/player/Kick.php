<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\Main;
use pocketmine\scheduler\ClosureTask;
use SOFe\AwaitGenerator\Await;

class Kick extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private PlayerArgument $player;
    private StringArgument $reason;

    public function __construct(string         $player = "", string $reason = "", private bool   $isAdmin = false) {
        parent::__construct(self::KICK, FlowItemCategory::PLAYER);

        $this->player = new PlayerArgument("player", $player);
        $this->reason = new StringArgument("reason", $reason, example: "aieuo");
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->player->getName(), "reason"];
    }

    public function getDetailReplaces(): array {
        return [$this->player->get(), $this->reason->get()];
    }

    public function getReason(): StringArgument {
        return $this->reason;
    }

    public function isDataValid(): bool {
        return $this->player->get() !== "" and $this->reason->isValid();
    }

    public function getPlayer(): PlayerArgument {
        return $this->player;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $reason = $this->reason->getString($source);
        $player = $this->player->getOnlinePlayer($source);

        Main::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($player, $reason): void {
            $player->kick($reason);
        }), 1);

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->player->createFormElement($variables),
            $this->reason->createFormElement($variables),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->player->set($content[0]);
        $this->reason->set($content[1]);
    }

    public function serializeContents(): array {
        return [$this->player->get(), $this->reason->get()];
    }
}
