<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\internal;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\utils\Language;
use SOFe\AwaitGenerator\Await;

class AddSpecificLanguageMapping extends FlowItem {
    use ActionNameWithMineflowLanguage;

    public function __construct(
        private string $language = "",
        private string $key = "",
        private string $message = ""
    ) {
        parent::__construct(self::ADD_SPECIFIC_LANGUAGE_MAPPING, FlowItemCategory::INTERNAL);
    }

    public function getDetailDefaultReplaces(): array {
        return ["language", "key", "message"];
    }

    public function getDetailReplaces(): array {
        return [$this->getLanguage(), $this->getKey(), $this->getMessage()];
    }

    public function getLanguage(): string {
        return $this->language;
    }

    public function setLanguage(string $language): void {
        $this->language = $language;
    }

    public function getKey(): string {
        return $this->key;
    }

    public function setKey(string $key): void {
        $this->key = $key;
    }

    public function getMessage(): string {
        return $this->message;
    }

    public function setMessage(string $message): void {
        $this->message = $message;
    }

    public function isDataValid(): bool {
        return $this->getLanguage() !== "" and $this->getKey() !== "" and $this->getMessage() !== "";
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $language = $source->replaceVariables($this->getLanguage());
        $key = $source->replaceVariables($this->getKey());
        $message = $source->replaceVariables($this->getMessage());

        Language::add([$key => $message], $language);

        yield Await::ALL;
    }

    public function getEditFormElements(array $variables): array {
        $languages = implode(", ", Language::getAvailableLanguages());
        return [
            new ExampleInput(Language::get("action.addSpecificLanguageMapping.form.language", [$languages]), "eng", $this->getLanguage(), true),
            new ExampleInput("@action.addLanguageMappings.form.key", "mineflow.action.aieuo", $this->getKey(), true),
            new ExampleInput("@action.addSpecificLanguageMapping.form.message", "Hello", $this->getMessage(), true),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setLanguage($content[0]);
        $this->setKey($content[1]);
        $this->setMessage($content[2]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getLanguage(), $this->getKey(), $this->getMessage()];
    }
}
