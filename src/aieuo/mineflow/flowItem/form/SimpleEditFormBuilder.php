<?php

declare(strict_types=1);


namespace aieuo\mineflow\flowItem\form;

use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\Element;
use aieuo\mineflow\formAPI\element\Label;
use function array_unshift;
use function array_values;

class SimpleEditFormBuilder {

    private EditFormResponseProcessor $responseProcessor;
    private SimpleEditFormPageManager $pageManager;

    /**
     * @param FlowItem $item
     * @param array $elements
     * @param bool $isNew
     * @param SimpleEditFormPageManager|null $pageManager
     */
    public function __construct(
        private FlowItem          $item,
        private array             $elements = [],
        private bool              $isNew = true,
        SimpleEditFormPageManager $pageManager = null,
    ) {
        $this->responseProcessor = new EditFormResponseProcessor(\Closure::fromCallable([$item, "loadSaveData"]));

        $this->pageManager = $pageManager ?? new SimpleEditFormPageManager();
        if ($this->pageManager->count() === 0) {
            $this->pageManager->add(0, $this);
        }
    }

    /**
     * @param Element[] $elements
     * @return $this
     */
    public function elements(array $elements): void {
        $this->elements = array_merge($this->elements, $elements);
    }

    /**
     * @param Element[] $elements
     * @return $this
     */
    public function setElements(array $elements): void {
        $this->elements = $elements;
    }

    public function appendElement(Element $element): void {
        $this->elements[] = $element;
    }

    public function prependElement(Element $element): void {
        array_unshift($this->elements, $element);
        $this->elements = array_values($this->elements);
    }

    /**
     * @param callable(EditFormResponseProcessor $response): void $callback
     * @return $this
     */
    public function response(callable $callback): void {
        $callback($this->responseProcessor);
    }

    /**
     * @param int $number
     * @param callable(SimpleEditFormBuilder $builder): void $callback
     * @return void
     */
    public function page(int $number, callable $callback): void {
        $page = $this->pageManager->get($number);
        if ($page === null) {
            $page = new self($this->item, isNew: $this->isNew, pageManager: $this->pageManager);
            $this->pageManager->add($number, $page);
        }

        $callback($page);
    }

    /**
     * @return self[]
     */
    public function getPages(): array {
        return $this->pageManager->all();
    }

    public function isCreating(): bool {
        return $this->isNew;
    }

    public function isEditing(): bool {
        return !$this->isNew;
    }

    private function buildForm(): CustomForm {
        return (new CustomForm($this->item->getName()))
            ->addContent(new Label($this->item->getDescription()))
            ->addContents($this->elements)
            ->addContent(new CancelToggle());
    }

    public function build(): SimpleEditForm {
        return new SimpleEditForm($this->item, $this->buildForm(), $this->responseProcessor->build(), $this->responseProcessor->getLoader());
    }
}
