<?php

declare(strict_types=1);


namespace aieuo\mineflow\flowItem\form\page\custom;

use aieuo\mineflow\exception\InvalidFormValueException;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\form\page\EditPage;
use aieuo\mineflow\formAPI\CustomForm;
use pocketmine\player\Player;
use SOFe\AwaitGenerator\Await;
use function array_combine;
use function array_flip;
use function array_intersect_key;
use function array_pop;
use function array_shift;
use function array_slice;
use function array_values;
use function count;
use function spl_object_id;

class CustomFormEditPage extends EditPage {

    /**
     * @param CustomForm $form
     * @param \Closure $processor
     * @param CustomFormResponseHandler[] $handlers
     */
    public function __construct(
        private readonly CustomForm $form,
        private readonly \Closure   $processor,
        private readonly array      $handlers,
    ) {
    }

    public function show(Player $player): \Generator {
        $elements = $this->form->getContents();
        return yield from Await::promise(function ($resolve) use ($player, $elements) {
            $this->form->onReceiveWithoutPlayer(function (array $data) use ($resolve, $player, $elements) {
                array_shift($data);
                if (array_pop($data)) {
                    $resolve(FlowItem::EDIT_CANCELED);
                    return;
                }

                $elements = array_slice($elements, 1, count($elements) - 2);
                $elementIdResponseMap = array_combine(array_map(fn($e) => spl_object_id($e), $elements), $data);

                try {
                    foreach ($this->handlers as $handler) {
                        $response = array_values(array_intersect_key($elementIdResponseMap, array_flip($handler->getElementIds())));
                        ($handler->getHandler())(...$response);
                    }

                    $data = ($this->processor)($data, $elements);
                } catch (InvalidFormValueException $e) {
                    $e->setIndex($e->getIndex() + 1);
                    throw $e;
                }

                $resolve(FlowItem::EDIT_SUCCESS);
            })->show($player);
        });
    }
}
