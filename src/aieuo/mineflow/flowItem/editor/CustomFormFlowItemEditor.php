<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\editor;

use aieuo\mineflow\exception\InvalidFormValueException;
use aieuo\mineflow\flowItem\argument\attribute\CustomFormEditorArgument;
use aieuo\mineflow\flowItem\argument\FlowItemArgument;
use aieuo\mineflow\flowItem\editor\form\custom\CustomFormElementHandler;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\Label;
use pocketmine\player\Player;
use aieuo\mineflow\libs\_1195f54ac7f1c3fe\SOFe\AwaitGenerator\Await;
use function array_filter;
use function array_pop;
use function array_shift;
use function array_slice;
use function count;

class CustomFormFlowItemEditor extends FlowItemEditor {

    /** @var FlowItemArgument[] */
    private readonly array $arguments;

    /**
     * @param FlowItem $flowItem
     * @param (FlowItemArgument|CustomFormEditorArgument)[] $arguments
     * @param string $buttonText
     * @param (\Closure(array $data): void)|null $formResponseValidator
     * @param bool $primary
     */
    public function __construct(
        private readonly FlowItem      $flowItem,
        array                          $arguments = null,
        string                         $buttonText = "@form.edit",
        private readonly \Closure|null $formResponseValidator = null,
        bool                           $primary = false,
    ) {
        parent::__construct($buttonText, $primary);

        $this->arguments = $arguments ?? array_filter($this->flowItem->getArguments(), function (FlowItemArgument $arg) {
            return $arg instanceof CustomFormEditorArgument;
        });
    }

    public function getFlowItem(): FlowItem {
        return $this->flowItem;
    }

    public function getArguments(): array {
        return $this->arguments;
    }

    public function edit(Player $player, array $variables, bool $isNew): \Generator {
        $item = $this->getFlowItem();

        $elements = [];
        $handlers = [];
        foreach ($this->getArguments() as $argument) {
            $argumentElements = $argument->createFormElements($variables);
            $handler = $argument->handleFormResponse(...);

            $handlers[] = new CustomFormElementHandler(count($elements), count($argumentElements), $handler);

            foreach ($argumentElements as $element) {
                $elements[] = $element;
            }
        }

        return yield from Await::promise(function ($resolve) use($player, $item, $elements, $handlers) {
            (new CustomForm($item->getName()))
                ->addContent(new Label($item->getDescription()))
                ->addContents($elements)
                ->addContent(new CancelToggle())
                ->onClose(fn() => $resolve(FlowItemEditor::EDIT_CLOSE))
                ->onReceiveWithoutPlayer(function (array $data) use($resolve, $handlers) {
                    array_shift($data);
                    if (array_pop($data)) {
                        $resolve(FlowItemEditor::EDIT_CANCELED);
                        return;
                    }

                    $this->validateFormResponse($data);

                    foreach ($handlers as $handler) {
                        ($handler->getHandler())(...array_slice($data, $handler->getStartIndex(), $handler->getElementCount()));
                    }

                    $resolve(FlowItemEditor::EDIT_SUCCESS);
                })->show($player);
        });
    }

    /**
     * @param array $data
     * @return void
     * @throws InvalidFormValueException
     */
    public function validateFormResponse(array $data): void {
        if ($this->formResponseValidator !== null) {
            ($this->formResponseValidator)($data);
        }
    }
}