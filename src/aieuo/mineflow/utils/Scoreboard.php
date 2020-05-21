<?php

namespace aieuo\mineflow\utils;

use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use pocketmine\Player;
use pocketmine\Server;

class Scoreboard {

    const DISPLAY_SIDEBAR = "sidebar";
    const DISPLAY_LIST = "list";
    const DISPLAY_BELOWNAME = "belowname";

    /* @var string */
    private $type;
    /* @var string */
    private $id;
    /* @var string */
    private $displayName;

    /** @var array<string, int> */
    private $scores = [];
    /** @var array<string, int> */
    private $scoreIds = [];
    /** @var int */
    private $scoreId = 0;

    private $show = [];

    public function __construct(string $type = self::DISPLAY_SIDEBAR, string $id = "objective", string $displayName = "") {
        $this->type = $type;
        $this->id = $id;
        $this->displayName = $displayName;
    }

    public function getScores(): array {
        return $this->scores;
    }

    public function setScore(string $name, int $value, int $id = null): self {
        $this->scores[$name] = $value;
        if (!isset($this->scoreIds[$name])) $this->scoreIds[$name] = $id ?? $this->scoreId ++;

        $this->updateScoreToAllPlayer($name, $value, $this->scoreIds[$name]);
        return $this;
    }

    public function setScoreName(string $name, int $score): self {
        $oldNames = array_keys($this->scores, $score);
        if (empty($oldNames)) {
            $this->setScore($name, $score);
            return $this;
        }

        foreach ($oldNames as $oldName) {
            $id = $this->scoreIds[$oldName];
            $this->removeScore($oldName);
            $this->setScore($name, $score, $id);
        }
        return $this;
    }

    public function getScore(string $name): ?int {
        return $this->scores[$name] ?? null;
    }

    public function removeScore(string $name): self {
        $this->removeScoreFromAllPlayer($name);

        unset($this->scores[$name], $this->scoreIds[$name]);
        return $this;
    }

    public function show(Player $player) {
        $pk = new SetDisplayObjectivePacket();
        $pk->displaySlot = $this->type;
        $pk->objectiveName = $this->id;
        $pk->displayName = $this->displayName;
        $pk->criteriaName = "dummy";
        $pk->sortOrder = 0;
        $player->sendDataPacket($pk);

        $pk = new SetScorePacket();
        $pk->type = SetScorePacket::TYPE_CHANGE;

        foreach ($this->scores as $name => $score) {
            $entry = new ScorePacketEntry();
            $entry->objectiveName = $this->id;
            $entry->type = ScorePacketEntry::TYPE_FAKE_PLAYER;
            $entry->customName = (string)$name;
            $entry->score = $score;
            $entry->scoreboardId = $this->scoreIds[$name];
            $pk->entries[] = $entry;
        }

        $player->sendDataPacket($pk);

        $this->show[$player->getName()] = true;
    }

    public function hide(Player $player) {
        $pk = new RemoveObjectivePacket();
        $pk->objectiveName = $this->id;
        $player->sendDataPacket($pk);

        unset($this->show[$player->getName()]);
    }

    public function removeScoreFromAllPlayer(string $scoreName) {
        foreach ($this->show as $name => $value) {
            $player = Server::getInstance()->getPlayerExact($name);
            if (!($player instanceof Player)) continue;
            $this->removeScoreFromPlayer($player, $scoreName);
        }
    }

    public function removeScoreFromPlayer(Player $player, string $scoreName) {
        $entry = new ScorePacketEntry();
        $entry->objectiveName = $this->id;
        $entry->scoreboardId = $this->scoreIds[$scoreName];

        $pk = new SetScorePacket();
        $pk->type = $pk::TYPE_REMOVE;
        $pk->entries[] = $entry;
        $player->sendDataPacket($pk);
    }

    public function updateScoreToAllPlayer(string $scoreName, int $score, int $id) {
        foreach ($this->show as $name => $value) {
            $player = Server::getInstance()->getPlayerExact($name);
            if (!($player instanceof Player)) continue;
            $this->updateScoreToPlayer($player, $scoreName, $score, $id);
        }
    }

    public function updateScoreToPlayer(Player $player, string $scoreName, int $value, int $id) {
        $entry = new ScorePacketEntry();
        $entry->objectiveName = $this->id;
        $entry->type = ScorePacketEntry::TYPE_FAKE_PLAYER;
        $entry->customName = $scoreName;
        $entry->score = $value;
        $entry->scoreboardId = $id;

        $pk = new SetScorePacket();
        $pk->type = $pk::TYPE_CHANGE;
        $pk->entries[] = $entry;
        $player->sendDataPacket($pk);
    }
}