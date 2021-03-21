<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use pocketmine\Player;
use pocketmine\Server;

class IsPlayerOnlineByName extends FlowItem implements Condition {

    protected $id = self::IS_PLAYER_ONLINE_BY_NAME;

    protected $name = "condition.isPlayerOnlineByName.name";
    protected $detail = "condition.isPlayerOnlineByName.detail";
    protected $detailDefaultReplace = ["player"];

    protected $category = Category::PLAYER;

    /* @var string */
    private $playerName;

    public function __construct(string $playerName = "target") {
        $this->playerName = $playerName;
    }

    public function getPlayerName(): string {
        return $this->playerName;
    }

    public function setPlayerName(string $playerName): void {
        $this->playerName = $playerName;
    }

    public function isDataValid(): bool {
        return $this->getPlayerName() !== null;
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getPlayerName()]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $name = $source->replaceVariables($this->getPlayerName());

        $player = Server::getInstance()->getPlayerExact($name);

        yield true;
        return $player instanceof Player;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ExampleInput("@condition.isPlayerOnline.form.name", "target", $this->getPlayerName(), true),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setPlayerName($content[0]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getPlayerName()];
    }
}