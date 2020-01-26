<?php

namespace aieuo\mineflow;

use aieuo\mineflow\flowItem\action\ActionFactory;
use aieuo\mineflow\flowItem\condition\ConditionFactory;
use aieuo\mineflow\utils\FormManager;
use pocketmine\utils\Config;
use pocketmine\plugin\PluginBase;
use aieuo\mineflow\variable\VariableHelper;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\recipe\RecipeManager;
use aieuo\mineflow\event\ServerStartEvent;
use aieuo\mineflow\economy\Economy;
use aieuo\mineflow\command\MineflowCommand;
use aieuo\mineflow\command\CommandManager;

class Main extends PluginBase {

    /** @var Main */
    private static $instance;

    /** @var Config */
    private $config;
    /** @var Config */
    private $favorites;
    /** @var Config */
    private $events;

    /** @var bool */
    private $loaded = false;

    /** @var RecipeManager */
    private $recipeManager;

    /** @var CommandManager */
    private $commandManager;

    /** @var VariableHelper */
    private $variableHelper;
    /**
     * @var FormManager
     */
    private $formManager;

    public static function getInstance(): ?self {
        return self::$instance;
    }

    public function onEnable() {
        self::$instance = $this;

        $serverLanguage = $this->getServer()->getLanguage()->getLang();
        $this->config = new Config($this->getDataFolder()."config.yml", Config::YAML, [
            "language" => $serverLanguage,
        ]);
        $this->config->save();
        $this->favorites = new Config($this->getDataFolder()."favorites.yml", Config::YAML);

        Language::setLanguage($this->config->get("language", "eng"));
        if (!Language::loadMessage()) {
            foreach (Language::getLoadErrorMessage($serverLanguage) as $error) {
                $this->getLogger()->warning($error);
            }
            $this->getServer()->getPluginManager()->disablePlugin($this);
            return;
        }

        $this->getServer()->getCommandMap()->register($this->getName(), new MineflowCommand);

        (new Economy($this))->loadPlugin();

        ActionFactory::init();
        ConditionFactory::init();

        $this->events = new Config($this->getDataFolder()."events.yml", Config::YAML);
        (new EventListener($this, $this->events))->registerEvents();

        $commands = new Config($this->getDataFolder()."commands.yml", Config::YAML);
        $this->commandManager = new CommandManager($this, $commands);

        $this->formManager = new FormManager(new Config($this->getDataFolder()."forms.json", Config::JSON));

        $this->variableHelper = new VariableHelper($this, new Config($this->getDataFolder()."variables.json", Config::JSON));

        $this->recipeManager = new RecipeManager($this->getDataFolder()."recipes/");

        $this->loaded = true;
        (new ServerStartEvent($this))->call();
    }

    public function onDisable() {
        if (!$this->loaded) return;
        $this->recipeManager->saveAll();
        $this->formManager->saveAll();
        $this->variableHelper->saveAll();
    }

    public function getConfig(): Config {
        return $this->config;
    }

    public function getFavorites(): Config {
        return $this->favorites;
    }

    public function getEvents(): Config {
        return $this->events;
    }

    public function getRecipeManager(): RecipeManager {
        return $this->recipeManager;
    }

    public function getCommandManager(): CommandManager {
        return $this->commandManager;
    }

    public function getFormManager(): FormManager {
        return $this->formManager;
    }

    public function getVariableHelper(): VariableHelper {
        return $this->variableHelper;
    }
}