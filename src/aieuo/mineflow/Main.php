<?php
declare(strict_types=1);

namespace aieuo\mineflow;

use aieuo\mineflow\command\CommandManager;
use aieuo\mineflow\command\MineflowCommand;
use aieuo\mineflow\economy\Economy;
use aieuo\mineflow\entity\EntityManager;
use aieuo\mineflow\event\ServerStartEvent;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemFactory;
use aieuo\mineflow\recipe\argument\RecipeArgument;
use aieuo\mineflow\recipe\RecipeManager;
use aieuo\mineflow\trigger\event\EventManager;
use aieuo\mineflow\trigger\time\CheckTimeTriggerTask;
use aieuo\mineflow\trigger\Triggers;
use aieuo\mineflow\utils\FormManager;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\PlayerConfig;
use aieuo\mineflow\variable\VariableHelper;
use JetBrains\PhpStorm\Deprecated;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class Main extends PluginBase {

    private static Main $instance;
    private static string $pluginVersion;

    private Config $config;
    private PlayerConfig $playerSettings;

    private bool $loaded = false;
    private bool $enabledRecipeErrorInConsole;
    private ?\DateTimeZone $timeTriggerTimeZone = null;

    private static RecipeManager $recipeManager;
    private static CommandManager $commandManager;
    private static EventManager $eventManager;
    private static FormManager $formManager;

    private static VariableHelper $variableHelper;

    public static function getInstance(): self {
        return self::$instance;
    }

    protected function onLoad(): void {
        self::$instance = $this;

        Mineflow::init($this);

        RecipeArgument::init();

        self::$variableHelper = new VariableHelper(new Config($this->getDataFolder()."variables.json", Config::JSON));

        self::$variableHelper->initVariableProperties();

        FlowItemCategory::registerDefaults();
        Language::init();
    }

    public function onEnable(): void {
        self::$pluginVersion = $this->getDescription()->getVersion();

        $serverLanguage = $this->getServer()->getLanguage()->getLang();
        $this->config = new Config($this->getDataFolder()."config.yml", Config::YAML, [
            "language" => in_array($serverLanguage, Language::getAvailableLanguages(), true) ? $serverLanguage : "eng",
            "show_recipe_errors_in_console" => true,
            "time_trigger_timezone" => ""
        ]);
        $this->config->save();

        $this->enabledRecipeErrorInConsole = $this->config->get("show_recipe_errors_in_console", true);
        if (!empty($timezone = $this->config->get("time_trigger_timezone"))) {
            $this->timeTriggerTimeZone = new \DateTimeZone($timezone);
        }

        Language::setLanguage($this->config->get("language", "eng"));

        $this->playerSettings = new PlayerConfig($this->getDataFolder()."player.yml", Config::YAML);

        $this->getServer()->getCommandMap()->register($this->getName(), new MineflowCommand);

        (new Economy($this))->loadPlugin();

        EntityManager::init();
        Triggers::init();
        FlowItemFactory::init();

        self::$commandManager = new CommandManager($this, new Config($this->getDataFolder()."commands.yml", Config::YAML));
        self::$eventManager = new EventManager(new Config($this->getDataFolder()."events.yml"));
        self::$formManager = new FormManager(new Config($this->getDataFolder()."forms.json", Config::JSON));
        self::$variableHelper->loadVariables();

        self::$recipeManager = new RecipeManager($this->getDataFolder()."recipes/", $this->getDataFolder()."addon/");
        self::$recipeManager->loadAddons();
        self::$recipeManager->loadRecipes();
        self::$recipeManager->addTemplates();

        (new EventListener())->registerEvents();

        if (!file_exists($this->getDataFolder()."imports/")) @mkdir($this->getDataFolder()."imports/", 0777, true);

        CheckTimeTriggerTask::start($this);

        $this->loaded = true;
        (new ServerStartEvent($this))->call();
    }

    public function onDisable(): void {
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

    #[Deprecated("Mineflow::getPluginVersion()")]
    public static function getPluginVersion(): string {
        return self::$pluginVersion;
    }

    public function isEnabledRecipeErrorInConsole(): bool {
        return $this->enabledRecipeErrorInConsole;
    }

    public function getTimeTriggerTimeZone(): ?\DateTimeZone {
        return $this->timeTriggerTimeZone;
    }

    public function setEnabledRecipeErrorInConsole(bool $enabledRecipeErrorInConsole): void {
        $this->enabledRecipeErrorInConsole = $enabledRecipeErrorInConsole;
        $this->config->set("show_recipe_errors_in_console", $enabledRecipeErrorInConsole);
    }
}
