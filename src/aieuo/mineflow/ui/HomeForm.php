<?php

namespace aieuo\mineflow\ui;

use pocketmine\Player;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\FormAPI\element\Button;

class HomeForm {

    public function sendMenu(Player $player) {
        (new ListForm("@form.home.title"))
            ->setContent("@form.selectButton")
            ->addButtons([
                new Button("@mineflow.recipe"),
                new Button("@form.exit")
            ])->onRecive(function (Player $player, ?int $data) {
                if ($data === null) return;
                switch ($data) {
                    case 0:
                        (new RecipeForm)->sendMenu($player);
                        break;
                }
            })->show($player);
    }
}