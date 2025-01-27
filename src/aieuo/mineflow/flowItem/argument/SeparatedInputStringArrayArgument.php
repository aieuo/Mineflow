<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\argument;

use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\utils\Language;
use function array_filter;
use function array_map;
use function array_merge;
use function array_pop;
use function explode;
use function is_string;
use function str_replace;

class SeparatedInputStringArrayArgument extends StringArrayArgument {

    public static function create(string $name, string|array $value = "", string $description = ""): static {
        return new static(name: $name, value: $value, editValuesDescription: $description);
    }

    /**
     * @param string $name
     * @param string|string[] $value
     * @param string $newValuesDescription
     * @param \Closure|string $editValuesDescription
     * @param string $example
     * @param bool $optional
     * @param string $separator
     */
    public function __construct(
        string                  $name,
        string|array            $value = [],
        private string          $newValuesDescription = "",
        private \Closure|string $editValuesDescription = "",
        string                  $example = "",
        bool                    $optional = false,
        string                  $separator = ",",
    ) {
        parent::__construct($name, $value, "", $example, $optional, $separator);
    }

    public function newValuesDescription(string $description): static {
        $this->newValuesDescription = $description;
        return $this;
    }

    public function editValuesDescription(\Closure|string $description): static {
        $this->editValuesDescription = $description;
        return $this;
    }

    public function createFormElements(array $variables): array {
        $elements = [];
        foreach ($this->getRawArray() as $i => $value) {
            if (is_string($this->editValuesDescription)) {
                $desc = str_replace("{%0}", (string)$i, Language::replace($this->editValuesDescription));
            } else {
                $desc = ($this->editValuesDescription)($i);
            }

            $elements[] = new ExampleInput($desc, $this->getExample(), $value);
        }
        $elements[] = new ExampleInput($this->newValuesDescription, $this->getExample());

        return $elements;
    }

    /**
     * @param string[] $data
     * @return void
     */
    public function handleFormResponse(mixed ...$data): void {
        $add = array_filter(array_map("trim", explode($this->getSeparator(), array_pop($data))));

        $values = array_filter($data, fn(string $str) => $str !== "");
        $values = array_merge($values, $add);

        $this->value($values);
    }
}