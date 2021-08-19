<?php

namespace aieuo\mineflow\formAPI\element\mineflow;

use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\utils\ButtonImage;
use aieuo\mineflow\utils\Language;
use pocketmine\Player;
use pocketmine\Server;

class CommandButton extends Button {

    protected string $type = self::TYPE_COMMAND;
    private string $command;

    public bool $skipIfCallOnClick = false;

    public function __construct(string $command, string $text = null, ?ButtonImage $image = null) {
        $this->command = $command;
        parent::__construct($text ?? "/".$command, fn(Player $player) => Server::getInstance()->dispatchCommand($player, $this->command), $image);
    }

    public function setCommand(string $command): self {
        $this->command = $command;
        return $this;
    }

    public function getCommand(): string {
        return $this->command;
    }

    public function __toString(): string {
        return Language::get("form.form.formMenu.list.commandButton", [$this->getText(), $this->getCommand()]);
    }

    public function jsonSerialize(): array {
        return [
            "text" => Language::replace($this->text),
            "id" => $this->getUUID(),
            "image" => $this->getImage(),
            "mineflow" => [
                "command" => $this->command
            ],
        ];
    }
}