<?php

namespace aieuo\mineflow\ui;

use pocketmine\Player;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\action\script\RepeatScript;
use aieuo\mineflow\FormAPI\element\Toggle;

class ScriptForm {
    public function sendSetRepeatCount(Player $player, RepeatScript $script, array $default = [], array $errors = []) {
        (new CustomForm("@script.repeat.editCount"))
            ->setContents([
                new Input("@script.repeat.repeatCount", "@script.repeat.repeatCount.placeholder", $default[0] ?? $script->getRepeatCount()),
                new Toggle("@form.cancelAndBack")
            ])->onRecive(function (Player $player, ?array $data, RepeatScript $script) {
                if ($data === null) return;

                if ($data[1]) {
                    $script->sendEditForm($player, ["@form.cancelled"]);
                    return;
                }

                if ($data[0] === "") {
                    $this->sendSetRepeatCount($player, $script, $data, [["@form.insufficient", 0]]);
                    return;
                }

                if ((int)$data[0] <= 0) {
                    $this->sendSetRepeatCount($player, $script, $data, [["@script.repeat.repeatCount.zero", 0]]);
                    return;
                }

                $script->setRepeatCount((int)$data[0]);
                $script->sendEditForm($player, ["@form.changed"]);
            })->addArgs($script)->addErrors($errors)->show($player);
    }
}