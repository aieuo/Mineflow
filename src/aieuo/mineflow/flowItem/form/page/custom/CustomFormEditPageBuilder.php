<?php

declare(strict_types=1);


namespace aieuo\mineflow\flowItem\form\page\custom;

use aieuo\mineflow\flowItem\form\page\EditPage;
use aieuo\mineflow\flowItem\form\page\EditPageBuilder;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\Element;
use aieuo\mineflow\formAPI\element\Label;
use function array_unshift;
use function array_values;

class CustomFormEditPageBuilder extends EditPageBuilder {

    private CustomFormResponseProcessor $responseProcessor;

    /** @var CustomFormResponseHandler[] */
    private array $responseHandlers = [];

    protected string $title = "";
    protected string $description = "";
    protected array $elements = [];

    public function __construct(SimpleEditFormBuilder $builder) {
        parent::__construct($builder);

        $item = $this->builder->getItem();
        $this->title = $item->getName();
        $this->description = $item->getDescription();

        $this->responseProcessor = new CustomFormResponseProcessor();
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
        $this->responseHandlers = [];
        return $this;
    }

    /**
     * @param Element[] $elements
     * @param callable(mixed ...$values): void|null $handler
     * @return $this
     */
    public function elements(array $elements, callable $handler = null): self {
        $this->elements = array_merge($this->elements, $elements);

        if ($handler !== null) {
            $this->handler($elements, $handler);
        }
        return $this;
    }

    /**
     * @param Element $element
     * @param callable(mixed ...$values): void|null $handler
     * @return $this
     */
    public function element(Element $element, callable $handler = null): self {
        $this->elements[] = $element;

        if ($handler !== null) {
            $this->handler([$element], $handler);
        }
        return $this;
    }

    /**
     * @param Element[] $elements
     * @param callable(mixed ...$values): void|null $handler
     * @return $this
     */
    public function setElements(array $elements, callable $handler = null): self {
        $this->elements = $elements;
        $this->responseHandlers = [];

        if ($handler !== null) {
            $this->handler($elements, $handler);
        }
        return $this;
    }

    /**
     * @param Element $element
     * @param callable(mixed ...$values): void|null $handler
     * @return $this
     */
    public function appendElement(Element $element, callable $handler = null): self {
        $this->element($element, $handler);
        return $this;
    }

    /**
     * @param Element $element
     * @param callable(mixed ...$values): void|null $handler
     * @return $this
     */
    public function prependElement(Element $element, callable $handler = null): self {
        array_unshift($this->elements, $element);
        $this->elements = array_values($this->elements);

        if ($handler !== null) {
            $this->handler([$element], $handler);
        }
        return $this;
    }

    /**
     * @param Element[] $elements
     * @param callable(mixed ...$values): void $handler
     * @return $this
     */
    public function handler(array $elements, callable $handler): self {
        $this->responseHandlers[] = new CustomFormResponseHandler($elements, $handler);
        return $this;
    }

    /**
     * @param callable(CustomFormResponseProcessor $response): void $callback
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
        return new CustomFormEditPage(
            $this->buildForm(),
            $this->responseProcessor->build(),
            $this->responseHandlers,
        );
    }
}
