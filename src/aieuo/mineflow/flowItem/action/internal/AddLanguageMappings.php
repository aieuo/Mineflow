<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\internal;

use aieuo\mineflow\exception\InvalidFormValueException;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\page\custom\CustomFormResponseProcessor;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\utils\Language;
use SOFe\AwaitGenerator\Await;
use function array_shift;
use function count;
use function implode;

class AddLanguageMappings extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private StringArgument $key;

    public function __construct(string $key = "", private array $mappings = []) {
        parent::__construct(self::ADD_LANGUAGE_MAPPINGS, FlowItemCategory::INTERNAL);

        $this->key = StringArgument::create("key", $key)->example("mineflow.action.aieuo");
    }

    public function getDetailDefaultReplaces(): array {
        return ["key", "messages"];
    }

    public function getDetailReplaces(): array {
        $messages = [];
        foreach ($this->getMappings() as $language => $message) {
            if (empty($message)) continue;
            $messages[] = $language.": ".$message;
        }
        return [(string)$this->key, implode("\n§7-§f ", $messages)];
    }

    public function getKey(): StringArgument {
        return $this->key;
    }

    public function getMappings(): array {
        return $this->mappings;
    }

    public function setMappings(array $mappings): void {
        $this->mappings = $mappings;
    }

    public function isDataValid(): bool {
        return $this->key->isValid() and count($this->mappings) > 0;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $key = $this->getKey()->getString($source);
        foreach ($this->getMappings() as $language => $message) {
            $message = $source->replaceVariables($message);
            if (empty($message)) continue;

            Language::add([$key => $message], $language);
        }

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $elements = [
            $this->key->createFormElements($variables)[0],
        ];
        $mappings = $this->getMappings();
        foreach (Language::getAvailableLanguages() as $name) {
            $elements[] = new ExampleInput(Language::get("action.addLanguageMappings.form.message", [$name]), "Hello", $mappings[$name] ?? "");
        }

        $builder->elements($elements);
        $builder->response(function (CustomFormResponseProcessor $response) {
            $response->preprocess(function (array $data) {
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
            });
        });
    }

    public function loadSaveData(array $content): void {
        $this->key->value($content[0]);
        $this->setMappings($content[1]);
    }

    public function serializeContents(): array {
        return [$this->getKey(), $this->getMappings()];
    }

    public function __clone(): void {
        $this->key = clone $this->key;
    }
}
