<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\internal;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\EditFormResponseProcessor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\StringVariable;
use SOFe\AwaitGenerator\Await;
use function array_map;
use function explode;
use function implode;
use function trim;

class GetLanguage extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    protected string $returnValueType = self::RETURN_VARIABLE_VALUE;

    public function __construct(
        private string $language = "",
        private string $key = "",
        private array  $parameters = [],
        private string $resultName = "message"
    ) {
        parent::__construct(self::GET_LANGUAGE_MESSAGE, FlowItemCategory::INTERNAL);
    }

    public function getDetailDefaultReplaces(): array {
        return ["language", "key", "parameters", "result"];
    }

    public function getDetailReplaces(): array {
        $parameters = implode(", ", $this->getParameters());
        return [$this->getLanguage(), $this->getKey(), $parameters, $this->getResultName()];
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

    public function getParameters(): array {
        return $this->parameters;
    }

    public function setParameters(array $parameters): void {
        $this->parameters = $parameters;
    }

    public function getResultName(): string {
        return $this->resultName;
    }

    public function setResultName(string $resultName): void {
        $this->resultName = $resultName;
    }

    public function isDataValid(): bool {
        return $this->getLanguage() !== "" and $this->getKey() !== "" and $this->getResultName() !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $language = $source->replaceVariables($this->getLanguage());
        $key = $source->replaceVariables($this->getKey());
        $parameters = array_map(fn($parameter) => $source->replaceVariables($parameter), $this->getParameters());
        $resultName = $source->replaceVariables($this->getResultName());

        $variable = new StringVariable(Language::get($key, $parameters, $language));
        $source->addVariable($resultName, $variable);

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $languages = implode(", ", Language::getAvailableLanguages());
        $builder->elements([
            new ExampleInput(Language::get("action.addSpecificLanguageMapping.form.language", [$languages]), "eng", $this->getLanguage(), true),
            new ExampleInput("@action.addLanguageMappings.form.key", "mineflow.action.aieuo", $this->getKey(), true),
            new ExampleInput("@action.getLanguageMessage.form.parameters", "aieuo, 123", implode(", ", $this->getParameters())),
            new ExampleInput("@action.form.resultVariableName", "message", $this->getResultName(), true),
        ])->response(function (EditFormResponseProcessor $response) {
            $response->preprocessAt(3, function ($value) {
                return array_map(fn($parameter) => trim($parameter), explode(",", $value));
            });
        });
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setLanguage($content[0]);
        $this->setKey($content[1]);
        $this->setParameters($content[2]);
        $this->setResultName($content[3]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getLanguage(), $this->getKey(), $this->getParameters(), $this->getResultName()];
    }

    public function getAddingVariables(): array {
        return [
            $this->getResultName() => new DummyVariable(StringVariable::class)
        ];
    }
}
