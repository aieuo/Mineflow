<?php

namespace aieuo\mineflow\ui;

use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\trigger\event\EventTriggerForm;
use aieuo\mineflow\ui\customForm\CustomFormForm;
use pocketmine\player\Player;

class HomeForm {

    public function sendMenu(Player $player): void {
        (new ListForm("@form.home.title"))
            ->addButtons([
                new Button("@mineflow.recipe", fn() => (new RecipeForm)->sendMenu($player)),
                new Button("@mineflow.command", fn() => (new CommandForm)->sendMenu($player)),
                new Button("@mineflow.event", fn() => (new EventTriggerForm)->sendSelectEvent($player)),
                new Button("@mineflow.form", fn() => (new CustomFormForm)->sendMenu($player)),
                new Button("@mineflow.settings", fn() => (new SettingForm)->sendMenu($player)),
                new Button("@form.exit"),
            ])->show($player);
    }
}