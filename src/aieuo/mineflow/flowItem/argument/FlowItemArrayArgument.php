<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\argument;

use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemContainer;
use Traversable;
use function array_splice;
use function array_values;

class FlowItemArrayArgument extends FlowItemArgument implements FlowItemContainer, \IteratorAggregate {

    /**
     * @param string $name
     * @param FlowItem[] $items
     * @param string $description
     * @return static
     */
    public static function create(string $name, array $items = [], string $description = ""): static {
        return new static(name: $name, value: $items, description: $description);
    }

    /**
     * @param string $name
     * @param FlowItem[] $value
     * @param string $description
     * @param string $type
     */
    public function __construct(
        string         $name,
        private array  $value = [],
        string         $description = "",
        private string $type = "action",
    ) {
        parent::__construct($name, $description);
    }

    /**
     * @param FlowItem[] $value
     * @return $this
     */
    public function value(array $value): self {
        $this->value = $value;
        return $this;
    }

    public function addItem(FlowItem $item): void {
        $this->value[] = $item;
    }

    /**
     * @param FlowItem[] $items
     * @return void
     */
    public function setItems(array $items): void {
        $this->value($items);
    }

    public function pushItem(int $index, FlowItem $action): void {
        array_splice($this->value, $index, 0, [$action]);
    }

    public function removeItem(int $index): void {
        unset($this->value[$index]);
        $this->value = array_values($this->value);
    }

    public function getItem(int $index): ?FlowItem {
        return $this->value[$index] ?? null;
    }

    public function getItems(): array {
        return $this->value;
    }

    /**
     * @return FlowItem[]
     */
    public function getItemsFlatten(): array {
        $flat = [];
        foreach ($this->getItems() as $item) {
            $flat[] = $item;
            foreach ($item->getArguments() as $argument) {
                if ($argument instanceof FlowItemArrayArgument) {
                    foreach ($argument->getItemsFlatten() as $item2) {
                        $flat[] = $item2;
                    }
                }
            }
        }
        return $flat;
    }

    public function type(string $type): self {
        $this->type = $type;
        return $this;
    }

    public function getContainerItemType(): string {
        return $this->type;
    }

    public function isValid(): bool {
        return true;
    }

    public function jsonSerialize(): array {
        return $this->value;
    }

    public function load(mixed $value): void {
        $items = [];
        foreach ($value as $content) {
            $item = FlowItem::loadEachSaveData($content);
            $items[] = $item;
        }
        $this->value($items);
    }

    public function getIterator(): Traversable {
        foreach ($this->getItems() as $key => $value) {
            yield $key => $value;
        }
    }

    public function __toString(): string {
        $details = [];
        foreach ($this->getItems() as $item) {
            $details[] = $item->getShortDetail();
        }
        return implode("\n", $details);
    }

    public function __clone(): void {
        $items = [];
        foreach ($this->value as $item) {
            $items[] = clone $item;
        }
        $this->value = $items;
    }
}