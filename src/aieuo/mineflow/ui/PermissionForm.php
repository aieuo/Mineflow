<?php

declare(strict_types=1);

namespace aieuo\mineflow\ui;

use aieuo\mineflow\flowItem\FlowItemPermission;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\element\StringResponseDropdown;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\utils\Language;
use pocketmine\player\Player;
use pocketmine\Server;
use function array_map;
use function array_pop;
use function in_array;

class PermissionForm {

    public function sendSelectPlayer(Player $player): void {
        $players = array_values(array_map(fn(Player $p) => $p->getName(), Server::getInstance()->getOnlinePlayers()));
        (new CustomForm("@permission.form.selectPlayer.title"))
            ->addContents([
                new StringResponseDropdown("@permission.form.selectPlayer.dropdown", $players, $player->getName(), result: $target),
                new Input("@form.element.variableDropdown.inputManually", result: $target2)
            ])->onReceive(function () use ($player, &$target, &$target2) {
                $this->sendEditPermission($player, $target2 !== "" ? $target2 : $target);
            })->show($player);
    }

    public function sendEditPermission(Player $player, string $target, array $messages = []): void {
        $config = Mineflow::getPlayerSettings();
        $permissions = $config->getPlayerActionPermissions($target);

        $allPermissions = FlowItemPermission::all();
        $contents = [];
        foreach ($allPermissions as $permission) {
            $contents[] = new Toggle("@permission.".$permission, in_array($permission, $permissions, true));
        }
        $contents[] = new CancelToggle(fn() => $this->sendSelectPlayer($player));

        (new CustomForm(Language::get("permission.form.edit.title", [$target])))
            ->addContents($contents)
            ->onReceiveWithoutPlayer(function (array $data) use($player, $target, $config, $permissions, $allPermissions) {
                array_pop($data);
                foreach ($data as $i => $checked) {
                    $permission = $allPermissions[$i];
                    $hasPermission = in_array($permission, $permissions, true);
                    if ($hasPermission and !$checked) {
                        $config->removePlayerActionPermission($target, $permission);
                    } elseif (!$hasPermission and $checked) {
                        $config->addPlayerActionPermission($target, $permission);
                    }
                }
                $config->save();

                $this->sendEditPermission($player, $target, ["@form.changed"]);
            })->addMessages($messages)->show($player);
    }

}