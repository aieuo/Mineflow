<?php

declare(strict_types=1);


namespace aieuo\mineflow\flowItem\form;

use aieuo\mineflow\exception\FlowItemLoadException;
use aieuo\mineflow\exception\InvalidFormValueException;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\Main;
use aieuo\mineflow\utils\Language;
use pocketmine\player\Player;
use SOFe\AwaitGenerator\Await;
use function array_pop;
use function array_shift;

class SimpleEditForm {

    public function __construct(
        FlowItem           $item,
        private CustomForm $form,
        private \Closure   $processor,
        private \Closure   $loader,
    ) {
    }

    public function show(Player $player): \Generator {
        return yield from Await::promise(function ($resolve) use ($player) {
            $this->form->onReceiveWithoutPlayer(function (array $data) use ($resolve) {
                array_shift($data);
                if (array_pop($data)) {
                    $resolve(FlowItem::EDIT_CANCELED);
                    return;
                }

                try {
                    $data = ($this->processor)($data);
                } catch (InvalidFormValueException $e) {
                    $e->setIndex($e->getIndex() + 1);
                    throw $e;
                }

                try {
                    ($this->loader)($data);
                } catch (FlowItemLoadException|\ErrorException $e) {
                    $player->sendMessage(Language::get("action.error.recipe"));
                    Main::getInstance()->getLogger()->logException($e);
                    $resolve(FlowItem::EDIT_CLOSE);
                    return;
                }

                $resolve(FlowItem::EDIT_SUCCESS);
            })->show($player);
        });
    }
}
