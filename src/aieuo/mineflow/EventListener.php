<?php /** @noinspection PhpUnused */

namespace aieuo\mineflow;

use aieuo\mineflow\event\EntityAttackEvent;
use aieuo\mineflow\flowItem\action\SetSitting;
use aieuo\mineflow\trigger\block\BlockTrigger;
use aieuo\mineflow\trigger\command\CommandTrigger;
use aieuo\mineflow\trigger\Trigger;
use aieuo\mineflow\trigger\TriggerHolder;
use aieuo\mineflow\trigger\TriggerTypes;
use aieuo\mineflow\ui\TriggerForm;
use aieuo\mineflow\utils\Session;
use pocketmine\command\Command;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\CommandEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\InteractPacket;
use pocketmine\Player;
use pocketmine\Server;

class EventListener implements Listener {

    public function registerEvents(): void {
        Server::getInstance()->getPluginManager()->registerEvents($this, Main::getInstance());
    }

    public function onJoin(PlayerJoinEvent $event): void {
        Session::createSession($event->getPlayer());
    }

    public function onQuit(PlayerQuitEvent $event): void {
        Session::destroySession($event->getPlayer());
    }

    public function onInteract(PlayerInteractEvent $event): void {
        if ($event->getAction() !== PlayerInteractEvent::RIGHT_CLICK_BLOCK and $event->getAction() !== PlayerInteractEvent::RIGHT_CLICK_AIR) return;

        $player = $event->getPlayer();
        $block = $event->getBlock();
        $session = Session::getSession($player);
        $holder = TriggerHolder::getInstance();
        $position = $block->x.",".$block->y.",".$block->z.",".$block->level->getFolderName();

        if ($player->isOp() and $session->exists("blockTriggerAction")) {
            switch ($session->get("blockTriggerAction")) {
                case "add":
                    $recipe = $session->get("blockTriggerRecipe");
                    $trigger = new BlockTrigger($position);
                    if ($recipe->existsTrigger($trigger)) {
                        (new TriggerForm)->sendAddedTriggerMenu($player, $recipe, $trigger, ["@trigger.alreadyExists"]);
                        return;
                    }
                    $recipe->addTrigger($trigger);
                    (new TriggerForm)->sendAddedTriggerMenu($player, $recipe, $trigger, ["@trigger.add.success"]);
                    break;
            }
            $session->remove("blockTriggerAction");
            return;
        }
        if ($holder->existsRecipeByString(TriggerTypes::BLOCK, $position)) {
            $trigger = new BlockTrigger($position);
            $recipes = $holder->getRecipes($trigger);
            $variables = $trigger->getVariables($block);
            $recipes->executeAll($player, $variables, $event);
        }
    }

    public function command(CommandEvent $event): void {
        $sender = $event->getSender();
        if (!($sender instanceof Player)) return;
        if ($event->isCancelled()) return;

        $cmd = $event->getCommand();
        $holder = TriggerHolder::getInstance();
        $commands = explode(" ", $cmd);

        $count = count($commands);
        $origin = $commands[0];
        $command = Server::getInstance()->getCommandMap()->getCommand($origin);
        if (!($command instanceof Command) or !$command->testPermissionSilent($sender)) return;

        for ($i = 0; $i < $count; $i++) {
            $command = implode(" ", $commands);
            if ($holder->existsRecipeByString(TriggerTypes::COMMAND, $origin, $command)) {
                $trigger = new CommandTrigger($origin, $command);
                $recipes = $holder->getRecipes($trigger);
                $variables = $trigger->getVariables($event->getCommand());
                $recipes->executeAll($sender, $variables, $event);
                break;
            }
            array_pop($commands);
        }
    }

    public function onDeath(PlayerDeathEvent $event): void {
        $player = $event->getPlayer();
        if ($player instanceof Player) SetSitting::leave($player);
    }

    public function onLevelChange(EntityLevelChangeEvent $event): void {
        $player = $event->getEntity();
        if ($player instanceof Player) SetSitting::leave($player);
    }

    public function receive(DataPacketReceiveEvent $event): void {
        $pk = $event->getPacket();
        $player = $event->getPlayer();
        if (($pk instanceof InteractPacket) and $pk->action === InteractPacket::ACTION_LEAVE_VEHICLE) {
            SetSitting::leave($player);
        }
    }

    public function teleport(EntityTeleportEvent $event): void {
        $player = $event->getEntity();
        if ($player instanceof Player) SetSitting::leave($player);
    }

    public function onEntityDamageByEntity(EntityDamageByEntityEvent $event): void {
        if ($event instanceof EntityAttackEvent) return;
        (new EntityAttackEvent($event->getDamager(), $event->getEntity(), $event->getCause(), $event->getBaseDamage(), $event->getModifiers(), $event->getKnockBack()))->call();
    }
}