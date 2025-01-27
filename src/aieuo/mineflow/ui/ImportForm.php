<?php

namespace aieuo\mineflow\ui;

use aieuo\mineflow\exception\FlowItemLoadException;
use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\formAPI\ModalForm;
use aieuo\mineflow\Main;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\recipe\RecipePack;
use aieuo\mineflow\utils\ConfigHolder;
use aieuo\mineflow\utils\Language;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use function basename;

class ImportForm {

    public function sendSelectImportFile(Player $player, array $messages = []): void {
        $files = glob(Main::getInstance()->getDataFolder()."imports/*.json");

        $buttons = [];
        foreach ($files as $file) {
            $buttons[] = new Button(basename($file, ".json"), fn() => $this->sendFileMenu($player, $file));
        }

        (new ListForm("@form.import.selectFile.title"))
            ->addButton(new Button("@form.back", fn() => (new RecipeForm)->sendMenu($player)))
            ->addButtons($buttons)
            ->addMessages($messages)
            ->show($player);
    }

    public function sendFileMenu(Player $player, string $path): void {
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

                try {
                    $pack = RecipePack::load($path);
                } catch (\ErrorException|\UnexpectedValueException $e) {
                    $player->sendMessage(TextFormat::RED.Language::get("recipe.load.failed", [basename($path, ".json"), $e->getMessage()]));
                    return;
                } catch (FlowItemLoadException|\InvalidArgumentException $e) {
                    $player->sendMessage(TextFormat::RED.Language::get("recipe.load.failed", [basename($path, ".json"), ""]));
                    $player->sendMessage(TextFormat::RED.$e->getMessage());
                    return;
                }

                if (version_compare(Main::getInstance()->getDescription()->getVersion(), $pack->getVersion()) < 0) {
                    $player->sendMessage(Language::get("import.plugin.outdated"));
                    return;
                }
                $this->importPack($player, $pack);
            })->addArgs($path)->show($player);

    }

    public function importPack(Player $player, RecipePack $pack): void {
        $this->importRecipes($player, $pack->getRecipes(), function () use ($player, $pack) {
            $this->importCommands($player, $pack->getCommands(), function () use ($player, $pack) {
                $this->importForms($player, $pack->getForms(), function () use ($player, $pack) {
                    $this->importConfigs($player, $pack->getConfigs(), function () use ($player) {
                        $player->sendMessage(Language::get("form.import.success"));
                    });
                });
            });
        });
    }

    public function importRecipes(Player $player, array $recipes, callable $onComplete = null, int $start = 0): void {
        $manager = Mineflow::getRecipeManager();
        for ($i = $start, $iMax = count($recipes); $i < $iMax; $i++) {
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
        if (is_callable($onComplete)) $onComplete();
    }

    public function importCommands(Player $player, array $commands, callable $onComplete = null, int $start = 0): void {
        $manager = Mineflow::getCommandManager();
        $commands = array_values($commands);
        for ($i = $start, $iMax = count($commands); $i < $iMax; $i++) {
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
        if (is_callable($onComplete)) $onComplete();
    }

    public function importForms(Player $player, array $forms, callable $onComplete = null, int $start = 0): void {
        $manager = Mineflow::getFormManager();
        $names = array_keys($forms);
        for ($i = $start, $iMax = count($forms); $i < $iMax; $i++) {
            $name = $names[$i];
            $formData = $forms[$name];
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
        if (is_callable($onComplete)) $onComplete();
    }

    public function importConfigs(Player $player, array $configs, callable $onComplete = null, int $start = 0): void {
        $names = array_keys($configs);
        for ($i = $start, $iMax = count($configs); $i < $iMax; $i++) {
            $name = $names[$i];
            $configData = $configs[$name];

            if (ConfigHolder::existsConfigFile($name)) {
                $this->confirmOverwrite($player, $name.".yml",
                    function (Player $player, bool $overwrite) use ($name, $configData, $configs, $onComplete, $i) {
                        if ($overwrite) {
                            ConfigHolder::setConfig($name, $configData, true);
                        }
                        $this->importConfigs($player, $configs, $onComplete, $i + 1);
                    });
                return;
            }

            ConfigHolder::setConfig($name, $configData, true);
        }
        if (is_callable($onComplete)) $onComplete();
    }


    public function confirmOverwrite(Player $player, string $name, callable $callback): void {
        (new ModalForm("@mineflow.import"))
            ->setContent(Language::get("form.import.duplicate", [$name]))
            ->setButton1("@form.yes")
            ->setButton2("@form.no")
            ->onReceive($callback)
            ->show($player);
    }
}