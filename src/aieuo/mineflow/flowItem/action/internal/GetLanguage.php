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
use aieuo\mineflow\libs\_1195f54ac7f1c3fe\SOFe\AwaitGenerator\Await;
use function implode;

class GetLanguage extends SimpleAction {

    protected string $returnValueType = self::RETURN_VARIABLE_VALUE;

    public function __construct(string $language = "", string $key = "", array $parameters = [], string $resultName = "message") {
        parent::__construct(self::GET_LANGUAGE_MESSAGE, FlowItemCategory::INTERNAL);

        $languages = implode(", ", Language::getAvailableLanguages());

        $this->setArguments([
            StringArgument::create("language", $language, Language::get("action.addSpecificLanguageMapping.form.language", [$languages]))->example("eng"),
            StringArgument::create("key", $key, "@action.addLanguageMappings.form.key")->example("mineflow.action.aieuo"),
            StringArrayArgument::create("parameters", $parameters)->optional()->example("aieuo, 123"),
            StringArgument::create("result", $resultName, "@action.form.resultVariableName")->example("message"),
        ]);
    }

    public function getLanguage(): StringArgument {
        return $this->getArgument("language");
    }

    public function getKey(): StringArgument {
        return $this->getArgument("key");
    }

    public function getParameters(): StringArrayArgument {
        return $this->getArgument("parameters");
    }

    public function getResultName(): StringArgument {
        return $this->getArgument("result");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $language = $this->getLanguage()->getString($source);
        $key = $this->getKey()->getString($source);
        $parameters = $this->getParameters()->getArray($source);
        $resultName = $this->getResultName()->getString($source);

        $variable = new StringVariable(Language::get($key, $parameters, $language));
        $source->addVariable($resultName, $variable);

        yield Await::ALL;
    }

    public function getAddingVariables(): array {
        return [
            (string)$this->getResultName() => new DummyVariable(StringVariable::class)
        ];
    }
}