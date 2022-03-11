<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\plugin;

use aieuo\mineflow\economy\Economy;
use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;
use pocketmine\utils\TextFormat;

class SetMoney extends TypeMoney {

    protected string $name = "action.setMoney.name";
    protected string $detail = "action.setMoney.detail";

    public function __construct(string $playerName = "{target.name}", string $amount = "") {
        parent::__construct(self::SET_MONEY, playerName: $playerName, amount: $amount);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        if (!Economy::isPluginLoaded()) {
            throw new InvalidFlowValueException(TextFormat::RED.Language::get("economy.notfound"));
        }

        $name = $source->replaceVariables($this->getPlayerName());
        $amount = $source->replaceVariables($this->getAmount());

        $this->throwIfInvalidNumber($amount, 0);

        $economy = Economy::getPlugin();
        $economy->setMoney($name, (int)$amount);
        yield true;
    }
}