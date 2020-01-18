<?php

namespace aieuo\mineflow\ui;

use pocketmine\Player;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\flowItem\action\RepeatScript;
use aieuo\mineflow\flowItem\action\WhileScript;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\utils\Language;

class ScriptForm {
    public function sendSetRepeatCount(Player $player, RepeatScript $script, array $default = [], array $errors = []) {
        (new CustomForm("@action.repeat.editCount"))
            ->setContents([
                new Input("@action.repeat.repeatCount", Language::get("form.example", ["10"]), $default[0] ?? $script->getRepeatCount()),
                new Toggle("@form.cancelAndBack")
            ])->onReceive(function (Player $player, ?array $data, RepeatScript $script) {
                if ($data === null) return;

                if ($data[1]) {
                    $script->sendCustomMenu($player, ["@form.cancelled"]);
                    return;
                }

                if ($data[0] === "") {
                    $this->sendSetRepeatCount($player, $script, $data, [["@form.insufficient", 0]]);
                    return;
                }

                if ((int)$data[0] <= 0) {
                    $this->sendSetRepeatCount($player, $script, $data, [["@action.repeat.repeatCount.zero", 0]]);
                    return;
                }

                $script->setRepeatCount((int)$data[0]);
                $script->sendCustomMenu($player, ["@form.changed"]);
            })->addArgs($script)->addErrors($errors)->show($player);
    }

    public function sendSetWhileInterval(Player $player, WhileScript $script, array $default = [], array $errors = []) {
        (new CustomForm("@action.repeat.editCount"))
            ->setContents([
                new Input("@action.while.interval", Language::get("form.example", ["20"]), $default[0] ?? $script->getInterval()),
                new Toggle("@form.cancelAndBack")
            ])->onReceive(function (Player $player, ?array $data, WhileScript $script) {
                if ($data === null) return;

                if ($data[1]) {
                    $script->sendCustomMenu($player, ["@form.cancelled"]);
                    return;
                }

                if ($data[0] === "") {
                    $this->sendSetWhileInterval($player, $script, $data, [["@form.insufficient", 0]]);
                    return;
                }

                if ((int)$data[0] <= 0) {
                    $this->sendSetWhileInterval($player, $script, $data, [["@action.repeat.repeatCount.zero", 0]]);
                    return;
                }

                $script->setInterval((int)$data[0]);
                $script->sendCustomMenu($player, ["@form.changed"]);
            })->addArgs($script)->addErrors($errors)->show($player);
    }
}