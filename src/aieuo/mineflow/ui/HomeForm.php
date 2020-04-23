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
                new Button("@mineflow.event"),
                new Button("@mineflow.form"),
                new Button("@mineflow.export"),
                new Button("@mineflow.import"),
                new Button("@mineflow.settings"),
                new Button("@form.exit"),
            ])->onReceive(function (Player $player, int $data) {
                switch ($data) {
                    case 0:
                        (new RecipeForm)->sendMenu($player);
                        break;
                    case 1:
                        (new CommandForm)->sendMenu($player);
                        break;
                    case 2:
                        (new EventTriggerForm)->sendSelectEvent($player);
                        break;
                    case 3:
                        (new CustomFormForm)->sendMenu($player);
                        break;
                    case 4:
                        (new MineflowForm)->selectRecipe($player, "@form.export.selectRecipe.title", [new ExportForm, "sendRecipeListByRecipe"]);
                        break;
                    case 5:
                        (new ImportForm)->sendSelectImportFile($player);
                        break;
                    case 6:
                        (new SettingForm)->sendMenu($player);
                }
            })->show($player);
    }
}