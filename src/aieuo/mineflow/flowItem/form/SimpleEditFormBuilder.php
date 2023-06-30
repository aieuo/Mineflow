<?php

declare(strict_types=1);


namespace aieuo\mineflow\flowItem\form;

use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\form\page\builder\CustomFormEditPageBuilder;
use aieuo\mineflow\flowItem\form\page\builder\EditPageBuilder;

class SimpleEditFormBuilder extends CustomFormEditPageBuilder {

    /**
     * @param FlowItem $item
     * @param bool $isNew
     * @param SimpleEditFormPageManager $pageManager
     */
    public function __construct(
        private readonly FlowItem                  $item,
        private readonly bool                      $isNew = true,
        private readonly SimpleEditFormPageManager $pageManager = new SimpleEditFormPageManager(),
    ) {
        parent::__construct($this);

        if ($this->pageManager->count() === 0) {
            $this->pageManager->add($this);
        }
    }

    /**
     * @param int $number
     * @param callable(EditPageBuilder $builder): void $callback
     * @param EditPageBuilder|null $newPageInstance
     * @return SimpleEditFormBuilder
     */
    public function page(int $number, callable $callback, EditPageBuilder $newPageInstance = null): self {
        $page = $this->pageManager->get($number);
        if ($page === null) {
            $page = $newPageInstance ?? new CustomFormEditPageBuilder($this);
            $this->pageManager->add($page);
        }

        $callback($page);
        return $this;
    }

    public function customFormPage(int $number, callable $callback): self {
        $page = $this->pageManager->get($number);
        if ($page !== null and !($page instanceof CustomFormEditPageBuilder)) {
            throw new \InvalidArgumentException("Flowitem edit page #".$number." is not a CustomFormPage");
        }

        return $this->page($number, $callback, new CustomFormEditPageBuilder($this));
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

    public function getItem(): FlowItem {
        return $this->item;
    }

    public function getPageManager(): SimpleEditFormPageManager {
        return $this->pageManager;
    }
}
