<?php

namespace aieuo\mineflow\flowItem\action\internal;

use aieuo\mineflow\exception\InvalidFormValueException;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\utils\Language;
use function array_shift;
use function count;

class AddLanguageMappings extends FlowItem {

    protected string $id = self::ADD_LANGUAGE_MAPPINGS;

    protected string $name = "action.addLanguageMappings.name";
    protected string $detail = "action.addLanguageMappings.detail";
    protected array $detailDefaultReplace = ["key", "messages"];

    protected string $category = FlowItemCategory::INTERNAL;

    public function __construct(
        private string $key = "",
        private array $mappings = [],
    ) {
    }

    public function getKey(): string {
        return $this->key;
    }

    public function setKey(string $key): void {
        $this->key = $key;
    }

    public function getMappings(): array {
        return $this->mappings;
    }

    public function setMappings(array $mappings): void {
        $this->mappings = $mappings;
    }

    public function isDataValid(): bool {
        return $this->getKey() !== "" and count($this->mappings) > 0;
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();

        $messages = [];
        foreach ($this->getMappings() as $language => $message) {
            if (empty($message)) continue;
            $messages[] = $language.": ".$message;
        }
        return Language::get($this->detail, [$this->getKey(), implode("\n§7-§f ", $messages)]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $key = $source->replaceVariables($this->getKey());
        foreach ($this->getMappings() as $language => $message) {
            $message = $source->replaceVariables($message);
            if (empty($message)) continue;

            Language::add([$key => $message], $language);
        }

        yield true;
    }

    public function getEditFormElements(array $variables): array {
        $elements = [
            new ExampleInput("@action.addLanguageMappings.form.key", "mineflow.action.aieuo", $this->getKey(), true),
        ];
        $mappings = $this->getMappings();
        foreach (Language::getAvailableLanguages() as $name) {
            $elements[] = new ExampleInput(Language::get("action.addLanguageMappings.form.message", [$name]), "Hello", $mappings[$name] ?? "");
        }
        return $elements;
    }

    public function parseFromFormData(array $data): array {
        $messageKey = array_shift($data);

        $languages = Language::getAvailableLanguages();
        $mapping = [];
        foreach ($data as $key => $value) {
            if (empty($value)) continue;
            $mapping[$languages[$key]] = $value;
        }

        if (count($mapping) === 0) {
            throw new InvalidFormValueException("@action.addLanguageMappings.empty", 1);
        }

        return [$messageKey, $mapping];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setKey($content[0]);
        $this->setMappings($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getKey(), $this->getMappings()];
    }

    public function allowDirectCall(): bool {
        return false;
    }
}
