<?php
declare(strict_types=1);

namespace aieuo\mineflow;

use aieuo\mineflow\command\MineflowCommand;
use aieuo\mineflow\entity\EntityManager;
use aieuo\mineflow\event\ServerStartEvent;
use aieuo\mineflow\trigger\time\CheckTimeTriggerTask;
use pocketmine\plugin\PluginBase;
use Symfony\Component\Filesystem\Path;

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

        EntityManager::init();

        Mineflow::getCommandManager()->init();

        Mineflow::getEventManager()->addDefaultTriggers();

        Mineflow::getVariableHelper()->loadVariables();

        Mineflow::getAddonManager()->loadAddons();
        Mineflow::getRecipeManager()->loadRecipes();
        Mineflow::getRecipeManager()->addTemplates();

        (new EventListener())->registerEvents();
        $this->getServer()->getCommandMap()->register($this->getName(), new MineflowCommand);

        if (!file_exists(Path::join($this->getDataFolder(), "imports"))) @mkdir(Path::join($this->getDataFolder(), "imports"), 0777, true);

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
}