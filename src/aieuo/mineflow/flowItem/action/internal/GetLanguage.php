<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\internal;

use aieuo\mineflow\flowItem\argument\StringArgument;
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

    private StringArgument $key;
    private StringArgument $resultName;

    public function __construct(
        private string $language = "",
        string         $key = "",
        private array  $parameters = [],
        string         $resultName = "message"
    ) {
        parent::__construct(self::GET_LANGUAGE_MESSAGE, FlowItemCategory::INTERNAL);

        $this->key = new StringArgument("key", $key, "@action.addLanguageMappings.form.key", example: "mineflow.action.aieuo");
        $this->resultName = new StringArgument("result", $resultName, "@action.form.resultVariableName", example: "message");
    }

    public function getDetailDefaultReplaces(): array {
        return ["language", "key", "parameters", "result"];
    }

    public function getDetailReplaces(): array {
        $parameters = implode(", ", $this->parameters);
        return [$this->getLanguage(), $this->key->get(), $parameters, $this->resultName->get()];
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

    public function getParameters(): array {
        return $this->parameters;
    }

    public function setParameters(array $parameters): void {
        $this->parameters = $parameters;
    }

    public function getResultName(): StringArgument {
        return $this->resultName;
    }

    public function isDataValid(): bool {
        return $this->getLanguage() !== "" and $this->key->isNotEmpty() and $this->resultName->isNotEmpty();
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $language = $source->replaceVariables($this->getLanguage());
        $key = $this->key->getString($source);
        $parameters = array_map(fn($parameter) => $source->replaceVariables($parameter), $this->parameters);
        $resultName = $this->resultName->getString($source);

        $variable = new StringVariable(Language::get($key, $parameters, $language));
        $source->addVariable($resultName, $variable);

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $languages = implode(", ", Language::getAvailableLanguages());
        $builder->elements([
            new ExampleInput(Language::get("action.addSpecificLanguageMapping.form.language", [$languages]), "eng", $this->getLanguage(), true),
            $this->key->createFormElement($variables),
            new ExampleInput("@action.getLanguageMessage.form.parameters", "aieuo, 123", implode(", ", $this->parameters)),
            $this->resultName->createFormElement($variables),
        ])->response(function (EditFormResponseProcessor $response) {
            $response->preprocessAt(3, function ($value) {
                return array_map(fn($parameter) => trim($parameter), explode(",", $value));
            });
        });
    }

    public function loadSaveData(array $content): void {
        $this->setLanguage($content[0]);
        $this->key->set($content[1]);
        $this->setParameters($content[2]);
        $this->resultName->set($content[3]);
    }

    public function serializeContents(): array {
        return [$this->getLanguage(), $this->key->get(), $this->parameters, $this->resultName->get()];
    }

    public function getAddingVariables(): array {
        return [
            $this->resultName->get() => new DummyVariable(StringVariable::class)
        ];
    }
}
