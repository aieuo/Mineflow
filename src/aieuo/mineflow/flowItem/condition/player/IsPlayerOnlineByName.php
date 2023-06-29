<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\player;

use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\ConditionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\condition\Condition;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use pocketmine\player\Player;
use pocketmine\Server;
use SOFe\AwaitGenerator\Await;

class IsPlayerOnlineByName extends FlowItem implements Condition {
    use ConditionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private StringArgument $playerName;

    public function __construct(string $playerName = "target") {
        parent::__construct(self::IS_PLAYER_ONLINE_BY_NAME, FlowItemCategory::PLAYER);

        $this->playerName = new StringArgument("name", $playerName, "@condition.isPlayerOnline.form.name", example: "target");
    }

    public function getDetailDefaultReplaces(): array {
        return ["player"];
    }

    public function getDetailReplaces(): array {
        return [$this->playerName->get()];
    }

    public function getPlayerName(): StringArgument {
        return $this->playerName;
    }

    public function isDataValid(): bool {
        return $this->playerName->get() !== null;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $name = $this->playerName->getString($source);

        $player = Server::getInstance()->getPlayerExact($name);

        yield Await::ALL;
        return $player instanceof Player;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->playerName->createFormElement($variables),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->playerName->set($content[0]);
    }

    public function serializeContents(): array {
        return [$this->playerName->get()];
    }
}
