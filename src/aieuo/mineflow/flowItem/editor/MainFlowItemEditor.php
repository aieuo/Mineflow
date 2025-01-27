<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\editor;

use aieuo\mineflow\flowItem\argument\attribute\CustomFormEditorArgument;
use aieuo\mineflow\flowItem\argument\FlowItemArgument;
use aieuo\mineflow\flowItem\FlowItem;

class MainFlowItemEditor extends CustomFormFlowItemEditor {

    /**
     * @param FlowItem $flowItem
     * @param (FlowItemArgument|CustomFormEditorArgument)[] $arguments
     * @param string $buttonText
     * @param (\Closure(array $data): void)|null $formResponseValidator
     */
    public function __construct(
        FlowItem      $flowItem,
        array         $arguments = null,
        string        $buttonText = "@form.edit",
        \Closure|null $formResponseValidator = null,
    ) {
        parent::__construct($flowItem, $arguments, $buttonText, $formResponseValidator, true);
    }
}