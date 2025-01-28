<?php

namespace aieuo\mineflow\command\subcommand;

use aieuo\mineflow\Mineflow;
use aieuo\mineflow\utils\Language;
use pocketmine\command\CommandSender;

class LanguageCommand extends MineflowSubcommand {
    public function execute(CommandSender $sender, array $args): void {
        if (!isset($args[0])) {
            $sender->sendMessage(Language::get("command.language.usage"));
            return;
        }

        if (!Language::isAvailableLanguage($args[0])) {
            $sender->sendMessage(Language::get("command.language.notfound", [$args[0], implode(", ", Language::getAvailableLanguages())]));
            return;
        }
        Language::setLanguage($args[0]);

        $config = Mineflow::getConfig();
        $config->set("language", $args[0]);
        $config->save();

        $sender->sendMessage(Language::get("language.selected", [Language::get("language.name")]));
    }
}