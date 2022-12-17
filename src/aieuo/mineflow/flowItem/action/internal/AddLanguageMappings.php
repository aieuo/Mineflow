<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\internal;

use aieuo\mineflow\exception\InvalidFormValueException;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\EditFormResponseProcessor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
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

    public function __construct(
        private string $key = "",
        private array  $mappings = [],
    ) {
        parent::__construct(self::ADD_LANGUAGE_MAPPINGS, FlowItemCategory::INTERNAL);
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
        return [$this->getKey(), implode("\n§7-§f ", $messages)];
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

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $key = $source->replaceVariables($this->getKey());
        foreach ($this->getMappings() as $language => $message) {
            $message = $source->replaceVariables($message);
            if (empty($message)) continue;

            Language::add([$key => $message], $language);
        }

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $elements = [
            new ExampleInput("@action.addLanguageMappings.form.key", "mineflow.action.aieuo", $this->getKey(), true),
        ];
        $mappings = $this->getMappings();
        foreach (Language::getAvailableLanguages() as $name) {
            $elements[] = new ExampleInput(Language::get("action.addLanguageMappings.form.message", [$name]), "Hello", $mappings[$name] ?? "");
        }

        $builder->elements($elements);
        $builder->response(function (EditFormResponseProcessor $response) {
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
