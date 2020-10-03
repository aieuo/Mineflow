<?php

namespace aieuo\mineflow\ui;

use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\mineflow\CancelToggle;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\ModalForm;
use aieuo\mineflow\Main;
use aieuo\mineflow\utils\Language;
use pocketmine\Player;

class MineflowForm {

    public function confirmRename(Player $player, string $name, string $newName, callable $onAccept, callable $onRefuse): void {
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

    public function confirmDelete(Player $player, string $title, string $name, callable $onAccept, callable $onRefuse): void {
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

    public function selectRecipe(Player $player, string $title, callable $callback, ?callable $onCancel = null, array $default = [], array $errors = []): void {
        (new CustomForm($title))
            ->setContents([
                new Input("@form.recipe.recipeName", "", $default[0] ?? "", true),
                new Input("@form.recipe.groupName", "", $default[1] ?? ""),
                new CancelToggle(),
            ])->onReceive(function (Player $player, array $data, string $title, callable $callback, ?callable $onCancel) {
                if ($data[2]) {
                    if (is_callable($onCancel)) {
                        call_user_func($onCancel, $player);
                        return;
                    }
                    (new HomeForm)->sendMenu($player);
                    return;
                }

                $manager = Main::getRecipeManager();

                $name = $data[0];
                $group = $data[1];
                if ($group === "") [$name, $group] = $manager->parseName($data[0]);
                if (!$manager->exists($name, $group)) {
                    $this->selectRecipe($player, $title, $callback, $onCancel, $data, [["@form.recipe.select.notfound", 0]]);
                    return;
                }

                $recipe = $manager->get($name, $group);
                if ($recipe === null) {
                    $this->selectRecipe($player, $title, $callback, $onCancel, $data, [["@form.recipe.select.notfound", 0]]);
                    return;
                }

                call_user_func_array($callback, [$player, $recipe]);
            })->addArgs($title, $callback, $onCancel)->addErrors($errors)->show($player);
    }
}