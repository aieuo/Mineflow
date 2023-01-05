<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\plugin;

use aieuo\mineflow\economy\Economy;
use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;
use pocketmine\utils\TextFormat;
use SOFe\AwaitGenerator\Await;

class TakeMoneyCondition extends TypeMoney {

    public function __construct(string $playerName = "{target.name}", string $amount = "") {
        parent::__construct(self::TAKE_MONEY_CONDITION, playerName: $playerName, amount: $amount);
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        if (!Economy::isPluginLoaded()) {
            throw new InvalidFlowValueException($this->getName(), TextFormat::RED.Language::get("economy.notfound"));
        }

        $name = $source->replaceVariables($this->getPlayerName());
        $amount = $this->getInt($source->replaceVariables($this->getAmount()), 1);

        $economy = Economy::getPlugin();
        $myMoney = $economy->getMoney($name);
        if ($myMoney >= $this->getAmount()) {
            $economy->takeMoney($name, $amount);
            return true;
        }

        yield Await::ALL;
        return false;
    }
}