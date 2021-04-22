<?php
declare(strict_types=1);

namespace aieuo\mineflow;

use aieuo\mineflow\command\CommandManager;
use aieuo\mineflow\command\MineflowCommand;
use aieuo\mineflow\economy\Economy;
use aieuo\mineflow\entity\EntityManager;
use aieuo\mineflow\event\ServerStartEvent;
use aieuo\mineflow\flowItem\FlowItemFactory;
use aieuo\mineflow\recipe\RecipeManager;
use aieuo\mineflow\trigger\event\EventManager;
use aieuo\mineflow\trigger\time\CheckTimeTriggerTask;
use aieuo\mineflow\trigger\Triggers;
use aieuo\mineflow\utils\FormManager;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\PlayerConfig;
use aieuo\mineflow\variable\VariableHelper;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class Main extends PluginBase {

    /** @var Main */
    private static $instance;
    /** @var string */
    private static $pluginVersion;

    /** @var Config */
    private $config;
    /** @var PlayerConfig */
    private $playerSettings;

    /** @var bool */
    private $loaded = false;

    /** @var RecipeManager */
    private static $recipeManager;
    /** @var CommandManager */
    private static $commandManager;
    /** @var EventManager */
    private static $eventManager;
    /** @var FormManager */
    private static $formManager;

    /** @var VariableHelper */
    private static $variableHelper;


    public static function getInstance(): self {
        return self::$instance;
    }

    /** @noinspection ReturnTypeCanBeDeclaredInspection */
    public function onEnable() {
        self::$instance = $this;
        self::$pluginVersion = $this->getDescription()->getVersion();

        $serverLanguage = $this->getServer()->getLanguage()->getLang();
        $this->config = new Config($this->getDataFolder()."config.yml", Config::YAML, [
            "language" => in_array($serverLanguage, Language::getAvailableLanguages(), true) ? $serverLanguage : "eng",
        ]);
        $this->config->save();

        Language::setLanguage($this->config->get("language", "eng"));
        foreach (Language::getAvailableLanguages() as $language) {
            Language::loadBaseMessage($language);
        }

        $this->playerSettings = new PlayerConfig($this->getDataFolder()."player.yml", Config::YAML);

        $this->getServer()->getCommandMap()->register($this->getName(), new MineflowCommand);

        (new Economy($this))->loadPlugin();

        EntityManager::init();
        Triggers::init();
        FlowItemFactory::init();


        self::$commandManager = new CommandManager($this, new Config($this->getDataFolder()."commands.yml", Config::YAML));
        self::$eventManager = new EventManager(new Config($this->getDataFolder()."events.yml"));
        self::$formManager = new FormManager(new Config($this->getDataFolder()."forms.json", Config::JSON));

        self::$variableHelper = new VariableHelper(new Config($this->getDataFolder()."variables.json", Config::JSON));

        self::$recipeManager = new RecipeManager($this->getDataFolder()."recipes/");
        self::$recipeManager->loadRecipes();

        (new EventListener())->registerEvents();

        if (!file_exists($this->getDataFolder()."imports/")) @mkdir($this->getDataFolder()."imports/", 0777, true);

        CheckTimeTriggerTask::start($this);

        $this->loaded = true;
        (new ServerStartEvent($this))->call();
    }

    /** @noinspection ReturnTypeCanBeDeclaredInspection */
    public function onDisable() {
        if (!$this->loaded) return;
        self::$recipeManager->saveAll();
        self::$formManager->saveAll();
        self::$variableHelper->saveAll();
    }

    public function getConfig(): Config {
        return $this->config;
    }

    public function getPlayerSettings(): PlayerConfig {
        return $this->playerSettings;
    }

    public static function getRecipeManager(): RecipeManager {
        return self::$recipeManager;
    }

    public static function getCommandManager(): CommandManager {
        return self::$commandManager;
    }

    public static function getFormManager(): FormManager {
        return self::$formManager;
    }

    public static function getEventManager(): EventManager {
        return self::$eventManager;
    }

    public static function getVariableHelper(): VariableHelper {
        return self::$variableHelper;
    }

    public static function getPluginVersion(): string {
        return self::$pluginVersion;
    }
}