<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Toggle;
use pocketmine\Player;
use pocketmine\Server;

class IsPlayerOnlineByName extends Condition {

    protected $id = self::IS_PLAYER_ONLINE_BY_NAME;

    protected $name = "condition.isPlayerOnlineByName.name";
    protected $detail = "condition.isPlayerOnlineByName.detail";
    protected $detailDefaultReplace = ["player"];

    protected $category = Category::PLAYER;

    protected $targetRequired = Recipe::TARGET_REQUIRED_PLAYER;

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

    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        $name = $origin->replaceVariables($this->getPlayerName());

        $player = Server::getInstance()->getPlayerExact($name);

        return $player instanceof Player;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@flowItem.form.target.player", Language::get("form.example", ["target"]), $default[1] ?? $this->getPlayerName()),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $errors = [];
        if ($data[1] === "") $data[1] = "target";
        return ["contents" => [$data[1]], "cancel" => $data[2], "errors" => $errors];
    }

    public function loadSaveData(array $content): Condition {
        if (!isset($content[0])) throw new \OutOfBoundsException();
        $this->setPlayerName($content[0]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getPlayerName()];
    }
}