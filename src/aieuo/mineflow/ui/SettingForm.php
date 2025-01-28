<?php

namespace aieuo\mineflow\ui;

use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\element\Dropdown;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\trigger\event\EventTrigger;
use aieuo\mineflow\utils\Language;
use pocketmine\player\Player;
use pocketmine\Server;

class SettingForm {
    public function sendMenu(Player $player, array $messages = []): void {
        (new ListForm("@mineflow.settings"))
            ->addButtons([
                new Button("@form.back"),
                new Button("@setting.language"),
                new Button("@setting.event"),
            ])->onReceive(function (Player $player, int $data) {
                switch ($data) {
                    case 0:
                        (new HomeForm)->sendMenu($player);
                        break;
                    case 1:
                        $this->selectLanguageForm($player);
                        break;
                    case 2:
                        $this->sendEventListForm($player);
                        break;
                }
            })->addMessages($messages)->show($player);
    }

    public function selectLanguageForm(Player $player): void {
        $languages = Language::getAvailableLanguages();
        (new CustomForm("@setting.language"))
            ->setContents([
                new Dropdown("@setting.language", $languages, array_search(Language::getLanguage(), $languages, true)),
            ])->onReceive(function (Player $player, array $data) use ($languages) {
                $language = $languages[$data[0]];
                Server::getInstance()->dispatchCommand($player, "mineflow language ".$language);
            })->show($player);
    }

    public function sendEventListForm(Player $player): void {
        $events = Mineflow::getEventManager()->getEvents();
        $contents = [];
        foreach ($events as $name => $enabled) {
            $contents[] = new Toggle((string)EventTrigger::get($name), $enabled);
        }
        (new CustomForm("@setting.event"))
            ->setContents($contents)
            ->onReceive(function (Player $player, array $data) use ($events) {
                $count = 0;
                $eventManager = Mineflow::getEventManager();
                foreach ($events as $name => $enabled) {
                    $trigger = $eventManager->getTrigger($name);
                    if ($trigger === null) continue;

                    if ($data[$count] and !$enabled) {
                        $eventManager->setTriggerEnabled($trigger);
                        $eventManager->getSetting()->set($name, true);
                    } elseif (!$data[$count] and $enabled) {
                        $eventManager->setTriggerDisabled($trigger);
                        $eventManager->getSetting()->set($name, false);
                    }
                    $count++;
                }
                $this->sendMenu($player, ["@form.changed"]);
            })->show($player);
    }
}