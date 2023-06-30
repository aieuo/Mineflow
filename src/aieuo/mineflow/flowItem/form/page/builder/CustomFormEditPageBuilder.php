<?php

declare(strict_types=1);


namespace aieuo\mineflow\flowItem\form\page\builder;

use aieuo\mineflow\flowItem\form\EditFormResponseProcessor;
use aieuo\mineflow\flowItem\form\page\CustomFormEditPage;
use aieuo\mineflow\flowItem\form\page\EditPage;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\Element;
use aieuo\mineflow\formAPI\element\Label;
use function array_unshift;
use function array_values;

class CustomFormEditPageBuilder extends EditPageBuilder {

    private EditFormResponseProcessor $responseProcessor;


    protected string $title = "";
    protected string $description = "";
    protected array $elements = [];

    public function __construct(SimpleEditFormBuilder $builder,) {
        parent::__construct($builder);

        $item = $this->builder->getItem();
        $this->title = $item->getName();
        $this->description = $item->getDescription();

        $this->responseProcessor = new EditFormResponseProcessor($item->loadSaveData(...));
    }

    public function title(string $title): self {
        $this->title = $title;
        return $this;
    }

    public function description(string $description): self {
        $this->description = $description;
        return $this;
    }

    public function clear(): self {
        $this->elements = [];
        return $this;
    }

    /**
     * @param Element[] $elements
     * @return CustomFormEditPageBuilder
     */
    public function elements(array $elements): self {
        $this->elements = array_merge($this->elements, $elements);
        return $this;
    }

    /**
     * @param Element $element
     * @param callable(mixed $value): mixed|null $responseProcessor
     * @return $this
     */
    public function element(Element $element, callable $responseProcessor = null): self {
        $this->elements[] = $element;
        if ($responseProcessor !== null) {
            $this->responseProcessor->addSubProcessor($element, $responseProcessor);
        }
        return $this;
    }

    /**
     * @param Element[] $elements
     * @return CustomFormEditPageBuilder
     */
    public function setElements(array $elements): self {
        $this->elements = $elements;
        return $this;
    }

    public function appendElement(Element $element): self {
        $this->elements[] = $element;
        return $this;
    }

    public function prependElement(Element $element): self {
        array_unshift($this->elements, $element);
        $this->elements = array_values($this->elements);
        return $this;
    }

    /**
     * @param callable(EditFormResponseProcessor $response): void $callback
     * @return CustomFormEditPageBuilder
     */
    public function response(callable $callback): self {
        $callback($this->responseProcessor);
        return $this;
    }

    public function isCreating(): bool {
        return $this->builder->isCreating();
    }

    public function isEditing(): bool {
        return $this->builder->isEditing();
    }

    private function buildForm(): CustomForm {
        return (new CustomForm($this->title))
            ->addContent(new Label($this->description))
            ->addContents($this->elements)
            ->addContent(new CancelToggle());
    }

    public function build(): EditPage {
        return new CustomFormEditPage($this->buildForm(), $this->responseProcessor->build(), $this->responseProcessor->getLoader());
    }
}
