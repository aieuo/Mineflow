<?php

namespace aieuo\mineflow\flowItem\condition;

use aieuo\mineflow\economy\Economy;
use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;
use pocketmine\utils\TextFormat;

class OverMoney extends TypeMoney {

    protected string $id = self::OVER_MONEY;

    protected string $name = "condition.overMoney.name";
    protected string $detail = "condition.overMoney.detail";

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        if (!Economy::isPluginLoaded()) {
            throw new InvalidFlowValueException(TextFormat::RED.Language::get("economy.notfound"));
        }

        $name = $source->replaceVariables($this->getPlayerName());
        $amount = $source->replaceVariables($this->getAmount());

        $this->throwIfInvalidNumber($amount);

        $myMoney = Economy::getPlugin()->getMoney($name);

        yield FlowItemExecutor::CONTINUE;
        return $myMoney >= (int)$amount;
    }
}