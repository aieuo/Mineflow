<?php

namespace aieuo\mineflow\command;

use aieuo\mineflow\Main;
use pocketmine\command\ConsoleCommandSender;

class MineflowConsoleCommandSender extends ConsoleCommandSender {

    private static $instance;

    public function __construct() {
        self::$instance = $this;
        parent::__construct();

        $this->addAttachment(Main::getInstance(), "mineflow.command.mineflow", false);
    }

    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self;
        }
        return self::$instance;
    }

}