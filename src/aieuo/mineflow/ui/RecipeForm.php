<?php

namespace aieuo\mineflow\ui;

use aieuo\mineflow\formAPI\CustomForm;
use pocketmine\Player;
use aieuo\mineflow\FormAPI\element\Button;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\FormAPI\element\Toggle;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\formAPI\ModalForm;
use aieuo\mineflow\Main;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Language;

class RecipeForm {

    public function sendMenuForm(Player $player) {
        (new ListForm("@mineflow.recipe"))
            ->setContent("@form.selectButton")
            ->addButtons([
                new Button("@form.action.add"),
                new Button("@form.action.edit"),
                new Button("@form.recipe.menu.recipeList"),
                new Button("@form.back"),
            ])->onRecive(function (Player $player, ?int $data) {
                if ($data === null) return;
                switch ($data) {
                    case 0:
                        $this->sendAddRecipeForm($player);
                        break;
                    case 1:
                        break;
                    case 2:
                        break;
                    default:
                        (new HomeForm)->sendMenuForm($player);
                        break;
                }
            })->show($player);
    }

    public function sendAddRecipeForm(Player $player, string $default = "", string $error = null) {
        $manager = Main::getInstance()->getRecipeManager();
        $name = $manager->getNotDuplicatedName("Recipe");

        $form = new CustomForm("@form.recipe.addRecipe.title");
        $form->setContents([
                new Input("@form.recipe.addRecipe.recipeName", $name, $default),
                new Toggle("@form.cancelAndBack"),
            ])->onRecive(function (Player $player, ?array $data, string $defaultName) {
                if ($data === null) return;

                if ($data[1]) {
                    $this->sendMenuForm($player);
                    return;
                }

                $manager = Main::getInstance()->getRecipeManager();
                $name = $data[0] === "" ? $defaultName : $data[0];

                if ($manager->exists($name)) {
                    $this->sendConfirmRenameNewRecipe($player, $name);
                    return;
                }

                $manager->add(new Recipe($name));
            })->addArgs($name);
        if ($error) $form->addError($error, 0);
        $form->show($player);
    }

    public function sendConfirmRenameNewRecipe(Player $player, string $name) {
        $manager = Main::getInstance()->getRecipeManager();
        $newName = $manager->getNotDuplicatedName($name);

        (new ModalForm("@form.recipe.renameNewRecipe.title"))
            ->setContent(Language::get("form.recipe.renameNewRecipe.content", [$name, $newName]))
            ->setButton1("@form.yes")
            ->setButton2("@form.no")
            ->onRecive(function (Player $player, ?bool $data, string $name, string $newName) {
                if ($data === null) return;
                if ($data) {
                    $manager = Main::getInstance()->getRecipeManager();
                    $manager->add(new Recipe($newName));
                } else {
                    $this->sendAddRecipeForm($player, $name, Language::get("form.recipe.addRecipe.exists", [$name]));
                }
            })->addArgs($name, $newName)->show($player);
    }

    public function sendRecipeMenuForm(Player $player) {

    }
}