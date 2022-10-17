<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\plugin;

use aieuo\mineflow\economy\Economy;
use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;
use pocketmine\utils\TextFormat;
use SOFe\AwaitGenerator\Await;

class LessMoney extends TypeMoney {

    public function __construct(string $playerName = "{target.name}", string $amount = "") {
        parent::__construct(self::LESS_MONEY, playerName: $playerName, amount: $amount);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        if (!Economy::isPluginLoaded()) {
            throw new InvalidFlowValueException($this->getName(), TextFormat::RED.Language::get("economy.notfound"));
        }

        $name = $source->replaceVariables($this->getPlayerName());
        $amount = $this->getInt($source->replaceVariables($this->getAmount()));

        $myMoney = Economy::getPlugin()->getMoney($name);

        yield Await::ALL;
        return $myMoney <= $amount;
    }
}
