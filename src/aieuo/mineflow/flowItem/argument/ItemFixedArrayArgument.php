<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\argument;

use aieuo\mineflow\exception\InvalidPlaceholderValueException;
use aieuo\mineflow\flowItem\argument\attribute\CustomFormEditorArgument;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ItemVariableDropdown;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\object\ItemVariable;
use pocketmine\item\Item;
use function str_replace;

class ItemFixedArrayArgument extends FlowItemArgument implements CustomFormEditorArgument {

    public static function create(string $name, array $value = [], string $description = ""): static {
        return new static(name: $name, value: $value, description: $description);
    }

    /**
     * @param string $name
     * @param string[] $value
     * @param string $description
     * @param int $count
     * @param \Closure(int $index, string $description): string|null $descriptionFormatter
     * @param bool $optional
     */
    public function __construct(
        string                $name,
        private array         $value = [],
        string                $description = "",
        private int           $count = 1,
        private \Closure|null $descriptionFormatter = null,
        private bool          $optional = false,
    ) {
        parent::__construct($name, $description);
    }

    public function value(array $value): self {
        $this->value = $value;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getVariableNameArray(): array {
        return $this->value;
    }

    public function getVariableNameAt(int $index): string {
        return $this->value[$index] ?? "";
    }

    /**
     * @param FlowItemExecutor $executor
     * @return Item[]
     * @throws InvalidPlaceholderValueException
     */
    public function getItemArray(FlowItemExecutor $executor): array {
        $items = [];
        foreach ($this->getVariableNameArray() as $name) {
            $items[] = $this->getItem($executor, $name);
        }
        return $items;
    }

    public function getItemAt(FlowItemExecutor $executor, int $index): Item {
        return $this->getItem($executor, $this->getVariableNameAt($index));
    }

    /**
     * @throws InvalidPlaceholderValueException
     */
    private function getItem(FlowItemExecutor $executor, string $variableName): Item {
        $player = $executor->replaceVariables($variableName);

        $variable = $executor->getVariable($player);
        if ($variable instanceof ItemVariable) {
            return $variable->getValue();
        }

        throw new InvalidPlaceholderValueException(Language::get("action.target.not.valid", [["action.target.require.item"], $variableName]));
    }

    public function count(int $count): self {
        $this->count = $count;
        return $this;
    }

    public function getCount(): int {
        return $this->count;
    }

    public function format(?callable $formatter): self {
        $this->descriptionFormatter = $formatter;
        return $this;
    }

    public function getDescriptionFormatter(): ?\Closure {
        return $this->descriptionFormatter;
    }

    public function optional(): static {
        $this->optional = true;
        return $this;
    }

    public function required(): static {
        $this->optional = false;
        return $this;
    }

    public function isOptional(): bool {
        return $this->optional;
    }

    public function isValid(): bool {
        if (count($this->value) !== $this->count) return false;
        if ($this->optional) return true;

        return !in_array("", $this->value, true);
    }

    public function createFormElements(array $variables): array {
        $elements = [];
        for ($i = 0; $i < $this->count; $i++) {
            $elements[] = new ItemVariableDropdown(
                $variables,
                $this->getVariableNameAt($i),
                $this->descriptionFormatter === null
                    ? str_replace("{%0}", (string)$i, $this->getDescription())
                    : ($this->descriptionFormatter)($i, $this->getDescription()),
                $this->isOptional(),
            );
        }

        return $elements;
    }

    /**
     * @param string[] $data
     * @return void
     */
    public function handleFormResponse(mixed ...$data): void {
        $this->value($data);
    }

    public function jsonSerialize(): array {
        return $this->getVariableNameArray();
    }

    public function load(mixed $value): void {
        $this->value($value);
    }

    public function __toString(): string {
        return implode(", ", $this->getVariableNameArray());
    }
}