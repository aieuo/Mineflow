<?php

namespace aieuo\mineflow\formAPI\element\mineflow;

use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\utils\Language;
use pocketmine\Player;
use pocketmine\Server;

class CommandButton extends Button {

    protected $type = self::TYPE_COMMAND;
    /** @var string */
    private $command;

    public $skipIfCallOnClick = false;

    public function __construct(string $command, string $text = null) {
        $this->command = $command;
        parent::__construct($text ?? "/".$command, function (Player $player) {
            Server::getInstance()->dispatchCommand($player, $this->command);
        });
    }

    public function setCommand(string $command): self {
        $this->command = $command;
        return $this;
    }

    public function getCommand(): string {
        return $this->command;
    }

    public function __toString() {
        return Language::get("form.form.formMenu.list.commandButton", [$this->getText(), $this->getCommand()]);
    }

    public function jsonSerialize(): array {
        return [
            "text" => Language::replace($this->text),
            "id" => $this->getUUID(),
            "mineflow" => [
                "command" => $this->command
            ],
        ];
    }
}