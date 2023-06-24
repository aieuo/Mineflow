<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\flowItem\placeholder\PlayerPlaceholder;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\Main;
use pocketmine\scheduler\ClosureTask;
use SOFe\AwaitGenerator\Await;

class Kick extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private PlayerPlaceholder $player;

    public function __construct(
        string         $player = "",
        private string $reason = "",
        private bool   $isAdmin = false
    ) {
        parent::__construct(self::KICK, FlowItemCategory::PLAYER);

        $this->player = new PlayerPlaceholder("player", $player);
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->player->getName(), "reason"];
    }

    public function getDetailReplaces(): array {
        return [$this->player->get(), $this->getReason()];
    }

    public function setReason(string $reason): void {
        $this->reason = $reason;
    }

    public function getReason(): string {
        return $this->reason;
    }

    public function isDataValid(): bool {
        return $this->player->get() !== "" and $this->reason !== "";
    }

    public function getPlayer(): PlayerPlaceholder {
        return $this->player;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $reason = $source->replaceVariables($this->getReason());
        $player = $this->player->getOnlinePlayer($source);

        Main::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($player, $reason): void {
            $player->kick($reason);
        }), 1);

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->player->createFormElement($variables),
            new ExampleInput("@action.kick.form.reason", "aieuo", $this->getReason()),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->player->set($content[0]);
        $this->setReason($content[1]);
    }

    public function serializeContents(): array {
        return [$this->player->get(), $this->getReason()];
    }
}
