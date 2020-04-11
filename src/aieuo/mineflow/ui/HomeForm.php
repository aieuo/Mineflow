<?php

namespace aieuo\mineflow\ui;

use aieuo\mineflow\formAPI\ModalForm;
use aieuo\mineflow\utils\Language;
use pocketmine\Player;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\formAPI\element\Button;

class HomeForm {

    public function sendMenu(Player $player) {
        (new ListForm("@form.home.title"))
            ->setContent("@form.selectButton")
            ->addButtons([
                new Button("@mineflow.recipe"),
                new Button("@mineflow.command"),
                new Button("@mineflow.form"),
                new Button("@mineflow.settings"),
                new Button("@form.exit"),
            ])->onReceive(function (Player $player, ?int $data) {
                if ($data === null) return;
                switch ($data) {
                    case 0:
                        (new RecipeForm)->sendMenu($player);
                        break;
                    case 1:
                        (new CommandForm)->sendMenu($player);
                        break;
                    case 2:
                        (new CustomFormForm)->sendMenu($player);
                        break;
                    case 3:
                        (new SettingForm)->sendMenu($player);
                }
            })->show($player);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function sendConfirmRename(Player $player, string $name, string $newName, callable $callback) {
        (new ModalForm("@form.home.rename.title"))
            ->setContent(Language::get("form.home.rename.content", [$name, $newName]))
            ->setButton1("@form.yes")
            ->setButton2("@form.no")
            ->onReceive(function (Player $player, ?bool $data, string $name, string $newName, callable $callback) {
                if ($data === null) return;
                call_user_func_array($callback, [$data, $name, $newName]);
            })->addArgs($name, $newName, $callback)->show($player);
    }
}