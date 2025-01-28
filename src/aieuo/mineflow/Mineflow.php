<?php
declare(strict_types=1);


namespace aieuo\mineflow;

use aieuo\mineflow\addon\AddonManager;
use aieuo\mineflow\command\CommandManager;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemFactory;
use aieuo\mineflow\flowItem\FlowItemPermission;
use aieuo\mineflow\recipe\argument\RecipeArgument;
use aieuo\mineflow\recipe\RecipeManager;
use aieuo\mineflow\trigger\event\EventManager;
use aieuo\mineflow\trigger\Triggers;
use aieuo\mineflow\utils\BlockStateIdToLegacyIdMap;
use aieuo\mineflow\utils\FormManager;
use aieuo\mineflow\utils\ItemStateIdToLegacyIdMap;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Logger;
use aieuo\mineflow\utils\PlayerConfig;
use aieuo\mineflow\variable\VariableHelper;
use JetBrains\PhpStorm\Deprecated;
use pocketmine\Server;
use pocketmine\utils\Config;
use Symfony\Component\Filesystem\Path;
use function in_array;

class Mineflow {

    private static string $pluginVersion;

    private static CommandManager $commandManager;
    private static FormManager $formManager;
    private static EventManager $eventManager;
    private static AddonManager $addonManager;
    private static RecipeManager $recipeManager;

    private static VariableHelper $variableHelper;

    private static Config $config;
    private static PlayerConfig $playerSettings;

    private static ItemStateIdToLegacyIdMap $itemStateIdToLegacyIdMap;
    private static BlockStateIdToLegacyIdMap $blockStateIdToLegacyIdMap;

    private static bool $enabledRecipeErrorInConsole = true;
    private static ?\DateTimeZone $timeZone = null;
    private static bool $debug = false;

    public static function init(Main $main): void {
        self::$pluginVersion = $main->getDescription()->getVersion();

        self::$itemStateIdToLegacyIdMap = new ItemStateIdToLegacyIdMap();
        self::$blockStateIdToLegacyIdMap = new BlockStateIdToLegacyIdMap();

        FlowItemCategory::registerDefaults();
        FlowItemPermission::registerDefaults();
        Language::init();
        Triggers::init();
        FlowItemFactory::init();
        RecipeArgument::init();

        self::$config = new Config(Path::join($main->getDataFolder(), "config.yml"));
        self::$playerSettings = new PlayerConfig(Path::join($main->getDataFolder(), "player.yml"));

        self::$commandManager = new CommandManager($main, new Config(Path::join($main->getDataFolder(), "commands.yml")));
        self::$formManager = new FormManager(new Config($main->getDataFolder()."forms.json"));
        self::$eventManager = new EventManager(new Config($main->getDataFolder()."events.yml"));
        self::$recipeManager = new RecipeManager($main->getDataFolder()."recipes/");
        self::$addonManager = new AddonManager($main->getDataFolder()."addons/");

        self::$variableHelper = new VariableHelper(
            new Config(Path::join($main->getDataFolder(), "variables.json")),
            new Config(Path::join($main->getDataFolder(), "variable_custom_data.json")),
        );
        self::$variableHelper->initVariableProperties();
    }

    public static function getPluginVersion(): string {
        return self::$pluginVersion;
    }

    public static function loadConfig(): void {
        $serverLanguage = Server::getInstance()->getLanguage()->getLang();
        $config = self::$config;

        $config->setDefaults([
            "language" => in_array($serverLanguage, Language::getAvailableLanguages(), true) ? $serverLanguage : "eng",
            "show_recipe_errors_in_console" => true,
            "time_trigger_timezone" => ""
        ]);
        $config->save();

        Logger::setEnabledRecipeErrorInConsole($config->get("show_recipe_errors_in_console", true));
        if (!empty($timezone = $config->get("time_trigger_timezone"))) {
            self::$timeZone = new \DateTimeZone($timezone);
        }

        self::$debug = $config->get("debug", false);

        Language::setLanguage($config->get("language", "eng"));
    }

    public static function getConfig(): Config {
        return self::$config;
    }

    public static function getPlayerSettings(): PlayerConfig {
        return self::$playerSettings;
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

    public static function getAddonManager(): AddonManager {
        return self::$addonManager;
    }

    public static function getRecipeManager(): RecipeManager {
        return self::$recipeManager;
    }

    public static function getVariableHelper(): VariableHelper {
        return self::$variableHelper;
    }

    public static function getItemStateIdToLegacyIdMap(): ItemStateIdToLegacyIdMap {
        return self::$itemStateIdToLegacyIdMap;
    }

    public static function getBlockStateIdToLegacyIdMap(): BlockStateIdToLegacyIdMap {
        return self::$blockStateIdToLegacyIdMap;
    }

    public static function getTimeZone(): ?\DateTimeZone {
        return self::$timeZone;
    }

    #[Deprecated(replacement: "aieuo\\mineflow\\utils\\Logger::isEnabledRecipeErrorInConsole()")]
    public static function isEnabledRecipeErrorInConsole(): bool {
        return Logger::isEnabledRecipeErrorInConsole();
    }

    #[Deprecated(replacement: "aieuo\\mineflow\\utils\\Logger::setEnabledRecipeErrorInConsole(%parametersList%)")]
    public static function setEnabledRecipeErrorInConsole(bool $enabledRecipeErrorInConsole): void {
        Logger::setEnabledRecipeErrorInConsole($enabledRecipeErrorInConsole);
    }

    public static function isDebug(): bool {
        return self::$debug;
    }

    public static function setDebug(bool $debug): void {
        self::$debug = $debug;
    }
}