<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\internal;

use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;
use SOFe\AwaitGenerator\Await;
use function implode;

class AddSpecificLanguageMapping extends SimpleAction {

    public function __construct(string $language = "", string $key = "", string $message = "") {
        parent::__construct(self::ADD_SPECIFIC_LANGUAGE_MAPPING, FlowItemCategory::INTERNAL);

        $languages = implode(", ", Language::getAvailableLanguages());

        $this->setArguments([
            new StringArgument("language", $language, Language::get("action.addSpecificLanguageMapping.form.language", [$languages]), example: "eng"),
            new StringArgument("key", $key, "@action.addLanguageMappings.form.key", example: "mineflow.action.aieuo"),
            new StringArgument("message", $message, example: "Hello"),
        ]);
    }

    public function getLanguage(): StringArgument {
        return $this->getArguments()[0];
    }

    public function getKey(): StringArgument {
        return $this->getArguments()[1];
    }

    public function getMessage(): StringArgument {
        return $this->getArguments()[2];
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $language = $this->getLanguage()->getString($source);
        $key = $this->getKey()->getString($source);
        $message = $this->getMessage()->getString($source);

        Language::add([$key => $message], $language);

        yield Await::ALL;
    }
}
