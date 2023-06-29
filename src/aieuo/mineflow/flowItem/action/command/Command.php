<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\command;

use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use pocketmine\Server;
use SOFe\AwaitGenerator\Await;

class Command extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private PlayerArgument $player;
    private StringArgument $command;

    public function __construct(string $player = "", string $command = "") {
        parent::__construct(self::COMMAND, FlowItemCategory::COMMAND);

        $this->player = new PlayerArgument("player", $player);
        $this->command = new StringArgument("command", $command, example: "command");
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->player->getName(), "command"];
    }

    public function getDetailReplaces(): array {
        return [$this->player->get(), $this->command->get()];
    }

    public function getCommand(): StringArgument {
        return $this->command;
    }

    public function isDataValid(): bool {
        return $this->player->get() !== "" and $this->command->isNotEmpty();
    }

    public function getPlayer(): PlayerArgument {
        return $this->player;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $command = $this->command->getString($source);
        $player = $this->player->getOnlinePlayer($source);

        Server::getInstance()->dispatchCommand($player, $command);

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->player->createFormElement($variables),
            $this->command->createFormElement($variables),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->player->set($content[0]);
        $this->command->set($content[1]);
    }

    public function serializeContents(): array {
        return [$this->player->get(), $this->command->get()];
    }
}
