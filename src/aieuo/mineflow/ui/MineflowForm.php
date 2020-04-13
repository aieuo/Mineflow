<?php

namespace aieuo\mineflow\ui;

use aieuo\mineflow\formAPI\ModalForm;
use aieuo\mineflow\utils\Language;
use pocketmine\Player;

class MineflowForm {

    public function confirmRename(Player $player, string $name, string $newName, callable $onAccept, callable $onRefuse) {
        (new ModalForm("@form.home.rename.title"))
            ->setContent(Language::get("form.home.rename.content", [$name, $newName]))
            ->setButton1("@form.yes")
            ->setButton2("@form.no")
            ->onReceive(function (Player $player, ?bool $data, string $name, string $newName, callable $onTrue, callable $onFalse) {
                if ($data) {
                    call_user_func_array($onTrue, [$player, $newName]);
                } else {
                    call_user_func_array($onFalse, [$player, $name]);
                }
            })->addArgs($name, $newName, $onAccept, $onRefuse)->show($player);
    }

    public function confirmDelete(Player $player, string $title, string $name, callable $onAccept, callable $onRefuse) {
        (new ModalForm($title))
            ->setContent(Language::get("form.delete.confirm", [$name]))
            ->setButton1("@form.yes")
            ->setButton2("@form.no")
            ->onReceive(function (Player $player, ?bool $data, callable $onTrue, callable $onFalse) {
                if ($data) {
                    call_user_func_array($onTrue, [$player]);
                } else {
                    call_user_func_array($onFalse, [$player]);
                }
            })->addArgs($onAccept, $onRefuse)->show($player);
    }
}