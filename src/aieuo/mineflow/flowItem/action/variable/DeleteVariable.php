<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\variable;

use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\EditFormResponseProcessor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\Mineflow;
use SOFe\AwaitGenerator\Await;

class DeleteVariable extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private StringArgument $variableName;

    public function __construct(
        string $variableName = "",
        private bool   $isLocal = true
    ) {
        parent::__construct(self::DELETE_VARIABLE, FlowItemCategory::VARIABLE);

        $this->variableName = new StringArgument("name", $variableName, "@action.variable.form.name", example: "aieuo");
    }

    public function getDetailDefaultReplaces(): array {
        return ["name", "scope"];
    }

    public function getDetailReplaces(): array {
        return [$this->variableName->get(), $this->isLocal ? "local" : "global"];
    }

    public function getVariableName(): StringArgument {
        return $this->variableName;
    }

    public function isDataValid(): bool {
        return $this->variableName->isEmpty();
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $name = $this->variableName->getString($source);
        if ($this->isLocal) {
            $source->removeVariable($name);
        } else {
            Mineflow::getVariableHelper()->delete($name);
        }

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->variableName->createFormElement($variables),
            new Toggle("@action.variable.form.global", !$this->isLocal),
        ])->response(function (EditFormResponseProcessor $response) {
            $response->logicalNOT(1);
        });
    }

    public function loadSaveData(array $content): void {
        $this->variableName->set($content[0]);
        $this->isLocal = $content[1];
    }

    public function serializeContents(): array {
        return [$this->variableName->get(), $this->isLocal];
    }
}
