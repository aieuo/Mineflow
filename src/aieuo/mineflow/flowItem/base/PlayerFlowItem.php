<?php


namespace aieuo\mineflow\flowItem\base;


use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use pocketmine\Player;

interface PlayerFlowItem {

    public function getPlayerVariableName(string $name = ""): string;

    public function setPlayerVariableName(string $player, string $name = ""): void;

    /** @throws InvalidFlowValueException */
    public function getPlayer(FlowItemExecutor $source, string $name): Player;

    /** @throws InvalidFlowValueException */
    public function getOnlinePlayer(FlowItemExecutor $source, string $name = ""): Player;
}