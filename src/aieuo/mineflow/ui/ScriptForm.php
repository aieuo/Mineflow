<?php

namespace aieuo\mineflow\ui;

use aieuo\mineflow\Main;
use pocketmine\Player;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\flowItem\action\RepeatAction;
use aieuo\mineflow\flowItem\action\WhileTaskAction;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\utils\Language;

class ScriptForm {
    public function sendSetRepeatCount(Player $player, RepeatAction $script, array $default = [], array $errors = []) {
        (new CustomForm("@action.repeat.editCount"))
            ->setContents([
                new Input("@action.repeat.repeatCount", Language::get("form.example", ["10"]), $default[0] ?? $script->getRepeatCount()),
                new Toggle("@form.cancelAndBack")
            ])->onReceive(function (Player $player, array $data, RepeatAction $script) {
                if ($data[1]) {
                    $script->sendCustomMenu($player, ["@form.cancelled"]);
                    return;
                }

                if ($data[0] === "") {
                    $this->sendSetRepeatCount($player, $script, $data, [["@form.insufficient", 0]]);
                    return;
                }

                if (!Main::getVariableHelper()->containsVariable($data[0]) and (int)$data[0] <= 0) {
                    $this->sendSetRepeatCount($player, $script, $data, [[Language::get("flowItem.error.lessValue", [1]), 0]]);
                    return;
                }

                $script->setRepeatCount($data[0]);
                $script->sendCustomMenu($player, ["@form.changed"]);
            })->addArgs($script)->addErrors($errors)->show($player);
    }

    public function sendSetWhileInterval(Player $player, WhileTaskAction $script, array $default = [], array $errors = []) {
        (new CustomForm("@action.repeat.editCount"))
            ->setContents([
                new Input("@action.whileTask.interval", Language::get("form.example", ["20"]), $default[0] ?? $script->getInterval()),
                new Toggle("@form.cancelAndBack")
            ])->onReceive(function (Player $player, array $data, WhileTaskAction $script) {
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