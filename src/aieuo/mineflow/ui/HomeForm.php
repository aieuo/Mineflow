<?php

namespace aieuo\mineflow\ui;

use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\ListForm;
use pocketmine\Player;

class HomeForm {

    public function sendMenu(Player $player): void {
        (new ListForm("@form.home.title"))
            ->setContent("@form.selectButton")
            ->addButtons([
                new Button("@mineflow.recipe", function () use($player) { (new RecipeForm)->sendMenu($player); }),
                new Button("@mineflow.command", function () use($player) { (new CommandForm)->sendMenu($player); }),
                new Button("@mineflow.event", function () use($player) { (new EventTriggerForm)->sendSelectEvent($player); }),
                new Button("@mineflow.form", function () use($player) { (new CustomFormForm)->sendMenu($player); }),
                new Button("@mineflow.settings", function () use($player) { (new SettingForm)->sendMenu($player); }),
                new Button("@form.exit"),
            ])->show($player);
    }
}