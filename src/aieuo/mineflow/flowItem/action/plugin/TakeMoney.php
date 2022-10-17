<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\plugin;

use aieuo\mineflow\economy\Economy;
use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;
use pocketmine\utils\TextFormat;
use SOFe\AwaitGenerator\Await;

class TakeMoney extends TypeMoney {

    public function __construct(string $playerName = "{target.name}", string $amount = "") {
        parent::__construct(self::TAKE_MONEY, playerName: $playerName, amount: $amount);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        if (!Economy::isPluginLoaded()) {
            throw new InvalidFlowValueException($this->getName(), TextFormat::RED.Language::get("economy.notfound"));
        }

        $name = $source->replaceVariables($this->getPlayerName());
        $amount = $this->getInt($source->replaceVariables($this->getAmount()), 1);

        $economy = Economy::getPlugin();
        $economy->takeMoney($name, $amount);

        yield Await::ALL;
    }
}
