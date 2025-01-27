<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\internal;

use aieuo\mineflow\flowItem\argument\LanguageMappingArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\SimpleAction;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\libs\_057384fe9e664697\SOFe\AwaitGenerator\Await;

class AddLanguageMappings extends SimpleAction {

    public function __construct(string $key = "", array $mappings = []) {
        parent::__construct(self::ADD_LANGUAGE_MAPPINGS, FlowItemCategory::INTERNAL);

        $this->setArguments([
            StringArgument::create("key", $key)->example("mineflow.action.aieuo"),
            LanguageMappingArgument::create("messages", $mappings),
        ]);
    }

    public function getKey(): StringArgument {
        return $this->getArgument("key");
    }

    public function getMappings(): LanguageMappingArgument {
        return $this->getArgument("messages");
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $key = $this->getKey()->getString($source);
        foreach ($this->getMappings()->getArray() as $language => $message) {
            $message = $source->replaceVariables($message);
            if (empty($message)) continue;

            Language::add([$key => $message], $language);
        }

        yield Await::ALL;
    }
}