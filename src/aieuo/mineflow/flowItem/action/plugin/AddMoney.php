<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\plugin;

use aieuo\mineflow\economy\Economy;
use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;
use pocketmine\utils\TextFormat;

class AddMoney extends TypeMoney {

    protected string $id = self::ADD_MONEY;

    protected string $name = "action.addMoney.name";
    protected string $detail = "action.addMoney.detail";

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        if (!Economy::isPluginLoaded()) {
            throw new InvalidFlowValueException(TextFormat::RED.Language::get("economy.notfound"));
        }

        $name = $source->replaceVariables($this->getPlayerName());
        $amount = $this->getInt($source->replaceVariables($this->getAmount()), 1);

        $economy = Economy::getPlugin();
        $economy->addMoney($name, $amount);
        yield FlowItemExecutor::CONTINUE;
    }
}