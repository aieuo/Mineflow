<?php
declare(strict_types=1);

namespace aieuo\mineflow\ui;

use aieuo\mineflow\flowItem\FlowItemContainer;
use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\element\GeneratorButton;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\ui\controller\FlowItemFormController;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Session;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use function count;

class FlowItemContainerForm {

    public function sendActionList(Player $player, FlowItemContainer $container, array $messages = []): \Generator {
        $actions = $container->getItems();

        $buttons = [
            new Button("@form.back"),
            new Button("@{$container->getContainerItemType()}.add")
        ];
        foreach ($actions as $action) {
            $buttons[] = new Button(trim(TextFormat::clean($action->getShortDetail())));
        }

        $name = FlowItemFormController::getParentContainerName($player);
        $form = new ListForm(Language::get("form.{$container->getContainerItemType()}Container.list.title", [$name]));
        $form->addButtons($buttons);
        $form->addMessages($messages);

        $data = yield from $form->showAwait($player);

        if ($data === 0) {
            return;
        }

        Session::getSession($player)->set("action_list_clicked", null);
        if ($data === 1) {
            yield from (new FlowItemForm)->selectActionCategory($player, $container);
            return;
        }

        $data -= 2;
        $action = $actions[$data];
        Session::getSession($player)->set("action_list_clicked", $action);

        yield from FlowItemFormController::beginEditingItemAsync($player, $container, $action);
    }

    public function sendMoveAction(Player $player, FlowItemContainer $container, int $selected, array $messages = [], int $count = 0): \Generator {
        $actions = $container->getItems();
        $selectedAction = $actions[$selected];

        $buttons = [
            new GeneratorButton("@form.back", fn() => yield from (new FlowItemForm)->sendAddedItemMenu($player, $container, $actions[$selected], [$count === 0 ? "@form.cancelled" : "@form.moved"])),
        ];

        $parent = FlowItemFormController::getParentContainerOf($player, $container);
        if ($parent !== null) {
            $buttons[] = new GeneratorButton("@action.move.outside", function (Player $player) use($parent, $container, $selected, $count) {
                $tmp = $container->getItem($selected);
                if ($tmp !== null) {
                    $container->removeItem($selected);
                    $parent->addItem($tmp);
                    FlowItemFormController::leaveContainer($player);
                    yield from $this->sendMoveAction($player, $parent, count($parent->getItems()) - 1, ["@form.moved"], ++ $count);
                }
            });
        }

        $i = 0;
        foreach ($actions as $i => $action) {
            if ($i !== $selected and $i !== $selected + 1) {
                $buttons[] = new GeneratorButton("@form.move.to.here", fn() => yield from $this->moveContent($player, $container, $actions, $selected, $i, $count));
            }

            $color = ($i === $selected ? TextFormat::AQUA : "");
            $buttons[] = new GeneratorButton($color.trim(TextFormat::clean($action->getShortDetail())), function (Player $player) use($i, $action, $container, $selected, $count) {
                $containerArg = $action->getFlowItemContainer($container->getContainerItemType());
                if ($i === $selected or $containerArg === null) {
                    yield from $this->sendMoveAction($player, $container, $selected, ["@form.move.target.invalid"], $count);
                } else {
                    $tmp = $container->getItem($selected);
                    if ($tmp !== null) {
                        $container->removeItem($selected);
                        $containerArg->addItem($tmp);
                        FlowItemFormController::enterContainer($player, $containerArg);
                        yield from $this->sendMoveAction($player, $containerArg, count($containerArg->getItems()) - 1, ["@form.moved"], ++ $count);
                    }
                }
            });
        }
        if ($selected !== count($actions) - 1) {
            $buttons[] = new GeneratorButton("@form.move.to.here", fn() => yield from $this->moveContent($player, $container, $actions, $selected, $i + 1, $count));
        }

        $name = FlowItemFormController::getParentContainerName($player);
        $selected = yield from (new ListForm(Language::get("form.{$container->getContainerItemType()}Container.move.title", [$name, $selectedAction->getName()])))
            ->setContent("@form.{$container->getContainerItemType()}Container.move.content")
            ->addButtons($buttons)
            ->addMessages($messages)
            ->showAwait($player);
        $button = $buttons[$selected];

        yield from $button->getGenerator($player);
    }

    public function moveContent(Player $player, FlowItemContainer $container, array $actions, int $from, int $to, int $count): \Generator {
        $actions = $this->getMovedContents($actions, $from, $to);
        $container->setItems($actions);
        yield from $this->sendMoveAction($player, $container, $from < $to ? $to - 1 : $to, ["@form.moved"], ++ $count);
    }

    public function getMovedContents(array $contents, int $from, int $to): array {
        $move = $contents[$from];
        if ($from < $to) $to--;
        unset($contents[$from]);
        $newContents = [];
        foreach (array_values($contents) as $i => $action) {
            if ($i === $to) $newContents[] = $move;
            $newContents[] = $action;
        }
        if (count($contents) === count($newContents)) $newContents[] = $move;
        return $newContents;
    }
}