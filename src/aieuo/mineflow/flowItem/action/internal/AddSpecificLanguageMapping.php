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

    private StringArgument $language;
    private StringArgument $key;
    private StringArgument $message;

    public function __construct(string $language = "", string $key = "", string $message = "") {
        parent::__construct(self::ADD_SPECIFIC_LANGUAGE_MAPPING, FlowItemCategory::INTERNAL);

        $languages = implode(", ", Language::getAvailableLanguages());

        $this->setArguments([
            $this->language = new StringArgument("language", $language, Language::get("action.addSpecificLanguageMapping.form.language", [$languages]), example: "eng"),
            $this->key = new StringArgument("key", $key, "@action.addLanguageMappings.form.key", example: "mineflow.action.aieuo"),
            $this->message = new StringArgument("message", $message, example: "Hello"),
        ]);
    }

    public function getKey(): StringArgument {
        return $this->key;
    }

    public function getMessage(): StringArgument {
        return $this->message;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $language = $this->language->getString($source);
        $key = $this->key->getString($source);
        $message = $this->message->getString($source);

        Language::add([$key => $message], $language);

        yield Await::ALL;
    }
}
