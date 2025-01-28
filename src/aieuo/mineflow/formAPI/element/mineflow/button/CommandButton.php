<?php

namespace aieuo\mineflow\formAPI\element\mineflow\button;

use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\utils\ButtonImage;
use aieuo\mineflow\utils\Language;
use pocketmine\player\Player;
use pocketmine\Server;

class CommandButton extends Button {

    protected string $type = self::TYPE_COMMAND;
    private string $command;

    public bool $skipIfCallOnClick = false;

    public function __construct(string $command, string $text = null, ?callable $onClick = null, ?ButtonImage $image = null) {
        $this->command = $command;
        parent::__construct($text ?? "/".$command, $onClick ?? fn(Player $player) => Server::getInstance()->dispatchCommand($player, $this->command), $image);
    }

    public function setCommand(string $command): self {
        $this->command = $command;
        return $this;
    }

    public function getCommand(): string {
        return $this->command;
    }

    public function __toString(): string {
        return Language::get("form.form.formMenu.list.".$this->getType(), [$this->getText(), $this->getCommand()]);
    }

    public function jsonSerialize(): array {
        return [
            "text" => Language::replace($this->text),
            "id" => $this->getUUID(),
            "image" => $this->getImage(),
            "mineflow" => [
                "command" => $this->command,
                "type" => $this->getType(),
            ],
        ];
    }

    public static function fromSerializedArray(array $data): ?self {
        if (!isset($data["text"]) or !isset($data["mineflow"]["command"])) return null;

        if (isset($data["mineflow"]["type"]) and $data["mineflow"]["type"] === self::TYPE_COMMAND_CONSOLE) {
            return CommandConsoleButton::fromSerializedArray($data);
        }

        $button = new CommandButton($data["mineflow"]["command"], $data["text"]);
        if (!empty($data["image"])) {
            $button->setImage(new ButtonImage($data["image"]["data"], $data["image"]["type"]));
        }

        return $button->uuid($data["id"] ?? "");
    }
}