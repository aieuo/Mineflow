<?php

namespace aieuo\mineflow\ui;

use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\formAPI\ModalForm;
use aieuo\mineflow\Main;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\recipe\RecipePack;
use aieuo\mineflow\utils\Language;
use pocketmine\Player;

class ImportForm {

    public function sendSelectImportFile(Player $player, array $messages = []) {
        $files = glob(Main::getInstance()->getDataFolder()."imports/*.json");

        $buttons = [new Button("@form.back")];
        foreach ($files as $file) {
            $buttons[] = new Button(basename($file, ".json"));
        }

        (new ListForm("@form.import.selectFile.title"))
            ->setContent("@form.selectButton")
            ->setButtons($buttons)
            ->onReceive(function (Player $player, int $data, array $files) {
                if ($data === 0) {
                    (new HomeForm)->sendMenu($player);
                    return;
                }
                $data -= 1;

                $file = $files[$data];
                $this->sendFileMenu($player, $file);
            })->addMessages($messages)->addArgs($files)->show($player);
    }

    public function sendFileMenu(Player $player, string $path) {
        $data = json_decode(file_get_contents($path), true);
        if ($data === null) {
            $this->sendSelectImportFile($player, [Language::get("recipe.json.decode.failed", [basename($path, ".json"), json_last_error_msg()])]);
            return;
        }

        (new ListForm(basename($path, ".json")))
            ->setContent("name: ".$data["name"]."\ndetail: ".$data["detail"]."\nauthor: ".$data["author"])
            ->setButtons([
                new Button("@form.back"),
                new Button("@form.import.selectFile"),
            ])->onReceive(function (Player $player, int $data, string $path) {
                if ($data === 0) {
                    $this->sendSelectImportFile($player);
                    return;
                }

                $pack = RecipePack::import($path);
                $this->importPack($player, $pack);
            })->addArgs($path)->show($player);

    }

    public function importPack(Player $player, RecipePack $pack) {
        $this->importRecipes($player, $pack->getRecipes(), function () use ($player, $pack) {
            $this->importCommands($player, $pack->getCommands(), function () use ($player, $pack) {
                $this->importForms($player, $pack->getForms(), function () use ($player) {
                    $player->sendMessage(Language::get("form.import.success"));
                });
            });
        });
    }

    public function importRecipes(Player $player, array $recipes, callable $onComplete = null, int $start = 0) {
        $manager = Main::getRecipeManager();
        for ($i=$start; $i<count($recipes); $i++) {
            /** @var Recipe $recipe */
            $recipe = $recipes[$i];

            if ($manager->exists($recipe->getName(), $recipe->getGroup())) {
                $this->confirmOverwrite($player, $recipe->getGroup()."/".$recipe->getName(),
                    function (Player $player, bool $overwrite) use ($manager, $recipe, $recipes, $onComplete, $i) {
                        if ($overwrite) {
                            $manager->add($recipe);
                        }
                        $this->importRecipes($player, $recipes, $onComplete, $i + 1);
                    });
                return;
            }

            $manager->add($recipe);
        }
        if (is_callable($onComplete)) call_user_func($onComplete);
    }

    public function importCommands(Player $player, array $commands, callable $onComplete = null, int $start = 0) {
        $manager = Main::getCommandManager();
        $commands = array_values($commands);
        for ($i=$start; $i<count($commands); $i++) {
            $data = $commands[$i];
            $command = $data["command"];

            if ($manager->existsCommand($command) or $manager->isRegistered($command)) {
                $this->confirmOverwrite($player, $command,
                    function (Player $player, bool $overwrite) use ($manager, $data, $commands, $onComplete, $i) {
                        if ($overwrite) {
                            $manager->addCommand($data["command"], $data["permission"], $data["description"]);
                        }
                        $this->importCommands($player, $commands, $onComplete, $i + 1);
                    });
                return;
            }

            $manager->addCommand($data["command"], $data["permission"], $data["description"]);
        }
        if (is_callable($onComplete)) call_user_func($onComplete);
    }

    public function importForms(Player $player, array $forms, callable $onComplete = null, int $start = 0) {
        $manager = Main::getFormManager();
        $names = array_keys($forms);
        $forms = array_values($forms);
        for ($i=$start; $i<count($forms); $i++) {
            $name = $names[$i];
            $formData = $forms[$i];
            $form = Form::createFromArray($formData, $name);

            if ($manager->existsForm($name)) {
                $this->confirmOverwrite($player, $name,
                    function (Player $player, bool $overwrite) use ($manager, $form, $forms, $onComplete, $i) {
                        if ($overwrite) {
                            $manager->addForm($form->getName(), $form);
                        }
                        $this->importForms($player, $forms, $onComplete, $i + 1);
                    });
                return;
            }

            $manager->addForm($name, $form);
        }
        if (is_callable($onComplete)) call_user_func($onComplete);
    }


    public function confirmOverwrite(Player $player, string $name, callable $callback) {
        (new ModalForm("@mineflow.import"))
            ->setContent(Language::get("form.import.duplicate", [$name]))
            ->setButton1("@form.yes")
            ->setButton2("@form.no")
            ->onReceive($callback)
            ->show($player);
    }
}