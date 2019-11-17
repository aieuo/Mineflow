<?php

namespace aieuo\mineflow;

use pocketmine\utils\Config;
use pocketmine\plugin\PluginBase;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\script\ScriptFactory;
use aieuo\mineflow\recipe\RecipeManager;
use aieuo\mineflow\economy\Economy;
use aieuo\mineflow\condition\ConditionFactory;
use aieuo\mineflow\command\MineflowCommand;
use aieuo\mineflow\action\process\ProcessFactory;
use aieuo\mineflow\command\CommandManager;
use aieuo\mineflow\event\ServerStartEvent;
use aieuo\mineflow\trigger\TriggerManager;

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

        TriggerManager::init();

        ScriptFactory::init();
        ProcessFactory::init();
        ConditionFactory::init();

        $this->events = new Config($this->getDataFolder()."events.yml", Config::YAML);
        (new EventListener($this, $this->events))->registerEvents();

        $commands = new Config($this->getDataFolder()."commands.yml", Config::YAML);
        $this->commandManager = new CommandManager($this, $commands);
        $this->commandManager->addCommand("test a b c d e", "test2", "test3", "test4");

        $this->recipeManager = new RecipeManager($this->getDataFolder()."recipes/");

        $this->loaded = true;

        (new ServerStartEvent($this))->call();
    }

    public function onDisable() {
        if (!$this->loaded) return;
        $this->recipeManager->saveAll();
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
}