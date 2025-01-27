<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\internal;

use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\libs\_057384fe9e664697\SOFe\AwaitGenerator\Await;
use function implode;

class AddSpecificLanguageMapping extends SimpleAction {

    public function __construct(string $language = "", string $key = "", string $message = "") {
        parent::__construct(self::ADD_SPECIFIC_LANGUAGE_MAPPING, FlowItemCategory::INTERNAL);

        $languages = implode(", ", Language::getAvailableLanguages());

        $this->setArguments([
            StringArgument::create("language", $language, Language::get("action.addSpecificLanguageMapping.form.language", [$languages]))->example("eng"),
            StringArgument::create("key", $key, "@action.addLanguageMappings.form.key")->example("mineflow.action.aieuo"),
            StringArgument::create("message", $message)->example("Hello"),
        ]);
    }

    public function getLanguage(): StringArgument {
        return $this->getArgument("language");
    }

    public function getKey(): StringArgument {
        return $this->getArgument("key");
    }

    public function getMessage(): StringArgument {
        return $this->getArgument("message");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $language = $this->getLanguage()->getString($source);
        $key = $this->getKey()->getString($source);
        $message = $this->getMessage()->getString($source);

        Language::add([$key => $message], $language);

        yield Await::ALL;
    }
}