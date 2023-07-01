<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\internal;

use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\argument\StringArrayArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\StringVariable;
use SOFe\AwaitGenerator\Await;
use function implode;

class GetLanguage extends SimpleAction {

    protected string $returnValueType = self::RETURN_VARIABLE_VALUE;

    private StringArgument $language;
    private StringArgument $key;
    private StringArrayArgument $parameters;
    private StringArgument $resultName;

    public function __construct(string $language = "", string $key = "", array $parameters = [], string $resultName = "message") {
        parent::__construct(self::GET_LANGUAGE_MESSAGE, FlowItemCategory::INTERNAL);

        $languages = implode(", ", Language::getAvailableLanguages());

        $this->setArguments([
            $this->language = new StringArgument("language", $language, Language::get("action.addSpecificLanguageMapping.form.language", [$languages]), example: "eng"),
            $this->key = new StringArgument("key", $key, "@action.addLanguageMappings.form.key", example: "mineflow.action.aieuo"),
            $this->parameters = new StringArrayArgument("parameters", $parameters, example: "aieuo, 123"),
            $this->resultName = new StringArgument("result", $resultName, "@action.form.resultVariableName", example: "message"),
        ]);
    }

    public function getLanguage(): StringArgument {
        return $this->language;
    }

    public function getKey(): StringArgument {
        return $this->key;
    }

    public function getParameters(): StringArrayArgument {
        return $this->parameters;
    }

    public function getResultName(): StringArgument {
        return $this->resultName;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $language = $this->language->getString($source);
        $key = $this->key->getString($source);
        $parameters = $this->parameters->getArray($source);
        $resultName = $this->resultName->getString($source);

        $variable = new StringVariable(Language::get($key, $parameters, $language));
        $source->addVariable($resultName, $variable);

        yield Await::ALL;
    }

    public function getAddingVariables(): array {
        return [
            $this->resultName->get() => new DummyVariable(StringVariable::class)
        ];
    }
}
