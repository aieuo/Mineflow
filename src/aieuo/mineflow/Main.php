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
use aieuo\mineflow\utils\FormManager;
use aieuo\mineflow\utils\PlayerConfig;
use aieuo\mineflow\variable\VariableHelper;
use JetBrains\PhpStorm\Deprecated;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class Main extends PluginBase {

    private static Main $instance;

    private bool $loaded = false;

    public static function getInstance(): self {
        return self::$instance;
    }

    protected function onLoad(): void {
        self::$instance = $this;

        Mineflow::init($this);
    }

    public function onEnable(): void {
        Mineflow::loadConfig();

        (new Economy($this))->loadPlugin();

        EntityManager::init();

        Mineflow::getCommandManager()->init();

        Mineflow::getEventManager()->addDefaultTriggers();

        Mineflow::getVariableHelper()->loadVariables();

        Mineflow::getRecipeManager()->loadRecipes();
        Mineflow::getRecipeManager()->addTemplates();

        (new EventListener())->registerEvents();
        $this->getServer()->getCommandMap()->register($this->getName(), new MineflowCommand);

        if (!file_exists($this->getDataFolder()."imports/")) @mkdir($this->getDataFolder()."imports/", 0777, true);

        CheckTimeTriggerTask::start($this);

        $this->loaded = true;
        (new ServerStartEvent($this))->call();
    }

    public function onDisable(): void {
        if (!$this->loaded) return;

        Mineflow::getRecipeManager()->saveAll();
        Mineflow::getFormManager()->saveAll();
        Mineflow::getVariableHelper()->saveAll();
    }

    #[Deprecated(replacement: "Mineflow::getConfig()")]
    public function getConfig(): Config {
        return Mineflow::getConfig();
    }

    #[Deprecated(replacement: "Mineflow::getPlayerSettings()")]
    public function getPlayerSettings(): PlayerConfig {
        return Mineflow::getPlayerSettings();
    }

    #[Deprecated(replacement: "Mineflow::getRecipeManager()")]
    public static function getRecipeManager(): RecipeManager {
        return Mineflow::getRecipeManager();
    }

    #[Deprecated(replacement: "Mineflow::getCommandManager()")]
    public static function getCommandManager(): CommandManager {
        return Mineflow::getCommandManager();
    }

    #[Deprecated(replacement: "Mineflow::getFormManager()")]
    public static function getFormManager(): FormManager {
        return Mineflow::getFormManager();
    }

    #[Deprecated(replacement: "Mineflow::getEventManager()")]
    public static function getEventManager(): EventManager {
        return Mineflow::getEventManager();
    }

    #[Deprecated(replacement: "Mineflow::getVariableHelper()")]
    public static function getVariableHelper(): VariableHelper {
        return Mineflow::getVariableHelper();
    }

    #[Deprecated(replacement: "Mineflow::getPluginVersion()")]
    public static function getPluginVersion(): string {
        return Mineflow::getPluginVersion();
    }

    #[Deprecated(replacement: "Mineflow::isEnabledRecipeErrorInConsole()")]
    public function isEnabledRecipeErrorInConsole(): bool {
        return Mineflow::isEnabledRecipeErrorInConsole();
    }

    #[Deprecated(replacement: "Mineflow::getTimeTriggerTimeZone()")]
    public function getTimeTriggerTimeZone(): ?\DateTimeZone {
        return Mineflow::getTimeTriggerTimeZone();
    }

    #[Deprecated(replacement: "Mineflow::setEnabledRecipeErrorInConsole(%parameter0%)")]
    public function setEnabledRecipeErrorInConsole(bool $enabledRecipeErrorInConsole): void {
        Mineflow::setEnabledRecipeErrorInConsole($enabledRecipeErrorInConsole);
    }
}
