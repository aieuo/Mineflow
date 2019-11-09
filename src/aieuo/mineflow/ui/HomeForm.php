<?php

namespace aieuo\mineflow\ui;

use pocketmine\Player;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\FormAPI\element\Button;

class HomeForm {

    public function sendMenuForm(Player $player) {
        (new ListForm("@fome.home.title"))
            ->setContent("@form.selectButton")
            ->addButtons([
                new Button("@mineflow.recipe"),
                new Button("@form.exit")
            ])->onRecive(function (Player $player, ?int $data) {
                if ($data === null) return;
                switch ($data) {
                    case 0:
                        (new RecipeForm)->sendMenuForm($player);
                        break;
                }
            })->show($player);
    }
}