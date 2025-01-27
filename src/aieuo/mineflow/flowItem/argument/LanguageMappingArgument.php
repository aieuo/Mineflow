<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\argument;

use aieuo\mineflow\exception\InvalidFormValueException;
use aieuo\mineflow\flowItem\argument\attribute\CustomFormEditorArgument;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\utils\Language;
use function count;
use function implode;
use function str_replace;

class LanguageMappingArgument extends FlowItemArgument implements CustomFormEditorArgument {

    public static function create(string $name, array $value = [], string $description = ""): static {
        return new static(name: $name, value: $value, description: $description);
    }

    /**
     * @param string $name
     * @param array<string, string> $value
     * @param string $description
     */
    public function __construct(
        string        $name,
        private array $value = [],
        string        $description = "@action.addLanguageMappings.form.message",
    ) {
        parent::__construct($name, $description);
    }

    public function value(array $value): self {
        $this->value = $value;
        return $this;
    }

    public function getArray(): array {
        return $this->value;
    }

    public function isValid(): bool {
        return count($this->getArray()) > 0;
    }

    public function createFormElements(array $variables): array {
        $elements = [];
        foreach (Language::getAvailableLanguages() as $name) {
            $elements[] = new ExampleInput(str_replace("{%0}", $name, Language::replace($this->getDescription())), "Hello", $this->getArray()[$name] ?? "");
        }

        return $elements;
    }

    /**
     * @param string[] $data
     * @return void
     */
    public function handleFormResponse(mixed ...$data): void {
        $languages = Language::getAvailableLanguages();
        $mapping = [];
        foreach ($data as $key => $value) {
            if (empty($value)) continue;
            $mapping[$languages[$key]] = $value;
        }

        if (count($mapping) === 0) {
            throw new InvalidFormValueException("@action.addLanguageMappings.empty", 1);
        }
        $this->value($mapping);
    }

    public function jsonSerialize(): array {
        return $this->getArray();
    }

    public function load(mixed $value): void {
        $this->value($value);
    }

    public function __toString(): string {
        $messages = [];
        foreach ($this->getArray() as $language => $message) {
            if (empty($message)) continue;
            $messages[] = $language.": ".$message;
        }
        return implode("\n§7-§f ", $messages);
    }
}