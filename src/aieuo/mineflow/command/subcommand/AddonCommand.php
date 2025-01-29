<?php
declare(strict_types=1);

namespace aieuo\mineflow\command\subcommand;

use aieuo\mineflow\exception\MineflowException;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Utils;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use aieuo\mineflow\libs\_ac618486ac522f0b\SOFe\AwaitGenerator\Await;
use function count;

class AddonCommand extends MineflowSubcommand {
    public function execute(CommandSender $sender, array $args): void {
        if (!isset($args[0])) {
            $sender->sendMessage(Language::get("command.addon.usage"));
            return;
        }

        $manager = Mineflow::getAddonManager();
        switch ($args[0]) {
            case "list":
                $list = [];
                foreach ($manager->getAddons() as $addon) {
                    $list[] = TextFormat::GREEN.$addon->getName()." v".$addon->getVersion().TextFormat::WHITE;
                }
                $sender->sendMessage(Language::get("command.addon.list", [count($list), implode(", ", $list)]));
                break;
            case "reload":
                Await::f2c(function () use($manager, $sender) {
                    try {
                        $reloaded = yield from $manager->reloadAddons();
                    } catch (MineflowException|\Exception $e) {
                        $sender->sendMessage($e->getMessage());
                        return;
                    }
                    $sender->sendMessage(Language::get("command.addon.reload.success", [$reloaded, $reloaded > 1 ? "s" : ""]));
                });
                break;
            case "load":
                if (!isset($args[1])) {
                    $sender->sendMessage(Language::get("command.addon.load.usage", ["unload"]));
                    return;
                }

                $name = Utils::getValidFileName($args[1]);
                if (!$manager->existsFile($name)) {
                    $sender->sendMessage(TextFormat::YELLOW.Language::get("command.addon.load.not.exists", [$name.".json"]));
                    return;
                }

                if ($manager->getAddonByFilename($name) !== null) {
                    $sender->sendMessage(TextFormat::YELLOW.Language::get("command.addon.load.already.loaded", [$name.".json"]));
                    return;
                }

                Await::f2c(function () use($manager, $sender, $name) {
                    try {
                        $addon = yield from $manager->preloadAddon($manager->getDirectory().$name.".json");
                        $manager->loadAddon($addon);
                    } catch (MineflowException|\Exception $e) {
                        $sender->sendMessage(TextFormat::YELLOW.$e->getMessage());
                        return;
                    }
                    $sender->sendMessage(Language::get("command.addon.load.success", [$addon->getName()." v".$addon->getVersion()]));
                });
                break;
            case "unload":
                if (!isset($args[1])) {
                    $sender->sendMessage(Language::get("command.addon.load.usage", ["unload"]));
                    return;
                }

                $addon = $manager->getAddonByName($args[1]);
                if ($addon === null) {
                    $sender->sendMessage(TextFormat::YELLOW.Language::get("command.addon.unload.not.loaded", [$args[1]]));
                    return;
                }

                $manager->unloadAddon($addon);
                $sender->sendMessage(Language::get("command.addon.unload.success", [$addon->getName()." v".$addon->getVersion()]));
                break;
        }
    }
}