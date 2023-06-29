<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\internal;

use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\utils\Language;
use SOFe\AwaitGenerator\Await;
use function implode;

class AddSpecificLanguageMapping extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private StringArgument $key;
    private StringArgument $message;

    public function __construct(private string $language = "", string $key = "", string $message = "") {
        parent::__construct(self::ADD_SPECIFIC_LANGUAGE_MAPPING, FlowItemCategory::INTERNAL);

        $this->key = new StringArgument("key", $key, "@action.addLanguageMappings.form.key", example: "mineflow.action.aieuo");
        $this->message = new StringArgument("message", $message, example: "Hello");
    }

    public function getDetailDefaultReplaces(): array {
        return ["language", "key", "message"];
    }

    public function getDetailReplaces(): array {
        return [$this->getLanguage(), $this->key->get(), $this->message->get()];
    }

    public function getLanguage(): string {
        return $this->language;
    }

    public function setLanguage(string $language): void {
        $this->language = $language;
    }

    public function getKey(): StringArgument {
        return $this->key;
    }

    public function getMessage(): StringArgument {
        return $this->message;
    }

    public function isDataValid(): bool {
        return $this->getLanguage() !== "" and $this->key->isValid() and $this->message->isValid();
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $language = $source->replaceVariables($this->getLanguage());
        $key = $this->key->getString($source);
        $message = $this->message->getString($source);

        Language::add([$key => $message], $language);

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $languages = implode(", ", Language::getAvailableLanguages());
        $builder->elements([
            new ExampleInput(Language::get("action.addSpecificLanguageMapping.form.language", [$languages]), "eng", $this->getLanguage(), true),
            $this->key->createFormElement($variables),
            $this->message->createFormElement($variables),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->setLanguage($content[0]);
        $this->key->set($content[1]);
        $this->message->set($content[2]);
    }

    public function serializeContents(): array {
        return [$this->getLanguage(), $this->key->get(), $this->message->get()];
    }
}
