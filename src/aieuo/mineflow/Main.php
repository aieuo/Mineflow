<?php

namespace aieuo\mineflow;

use pocketmine\utils\Config;
use pocketmine\plugin\PluginBase;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\recipe\RecipeManager;
use aieuo\mineflow\condition\ConditionFactory;
use aieuo\mineflow\command\MineflowCommand;
use aieuo\mineflow\script\ScriptFactory;
use aieuo\mineflow\action\process\ProcessFactory;

class Main extends PluginBase {

    /** @var Main */
    private static $instance;

    /** @var Config */
    private $config;

    /** @var bool */
    private $loaded = false;

    /** @var RecipeManager */
    private $recipeManager;

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

        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);

        ScriptFactory::init();
        ProcessFactory::init();
        ConditionFactory::init();

        $this->recipeManager = new RecipeManager($this->getDataFolder()."recipes/");

        $this->loaded = true;
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

    public function getRecipeManager(): RecipeManager {
        return $this->recipeManager;
    }
}