<?php

namespace aieuo\mineflow\formAPI\element\mineflow\button;

use aieuo\mineflow\command\MineflowConsoleCommandSender;
use aieuo\mineflow\formAPI\utils\ButtonImage;
use pocketmine\Server;

class CommandConsoleButton extends CommandButton {

    protected string $type = self::TYPE_COMMAND_CONSOLE;

    public function __construct(string $command, string $text = null, ?callable $onClick = null, ?ButtonImage $image = null) {
        parent::__construct($command, $text, $onClick ?? fn() => Server::getInstance()->dispatchCommand(new MineflowConsoleCommandSender(Server::getInstance(), Server::getInstance()->getLanguage()), $command), $image);
    }

    public static function fromSerializedArray(array $data): ?self {
        if (!isset($data["text"]) or !isset($data["mineflow"]["command"])) return null;

        $button = new CommandConsoleButton($data["mineflow"]["command"], $data["text"]);
        if (!empty($data["image"])) {
            $button->setImage(new ButtonImage($data["image"]["data"], $data["image"]["type"]));
        }

        return $button->uuid($data["id"] ?? "");
    }
}