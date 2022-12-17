<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\item;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\base\ItemFlowItem;
use aieuo\mineflow\flowItem\base\ItemFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\EditFormResponseProcessor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\ItemVariableDropdown;
use SOFe\AwaitGenerator\Await;
use function array_filter;
use function array_map;
use function explode;
use function implode;

class SetItemLore extends FlowItem implements ItemFlowItem {
    use ItemFlowItemTrait;
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    private array $lore;

    public function __construct(string $item = "", string $lore = "") {
        parent::__construct(self::SET_ITEM_LORE, FlowItemCategory::ITEM);

        $this->setItemVariableName($item);
        $this->lore = array_filter(array_map("trim", explode(";", $lore)), fn(string $t) => $t !== "");
    }

    public function getDetailDefaultReplaces(): array {
        return ["item", "lore"];
    }

    public function getDetailReplaces(): array {
        return [$this->getItemVariableName(), implode(";", $this->getLore())];
    }

    public function setLore(array $lore): void {
        $this->lore = $lore;
    }

    public function getLore(): array {
        return $this->lore;
    }

    public function isDataValid(): bool {
        return $this->getItemVariableName() !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $item = $this->getItem($source);

        $lore = array_map(fn(string $lore) => $source->replaceVariables($lore), $this->getLore());

        $item->setLore($lore);

        yield Await::ALL;
        return $this->getItemVariableName();
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            new ItemVariableDropdown($variables, $this->getItemVariableName()),
            new ExampleInput("@action.setLore.form.lore", "1;aiueo;abc", implode(";", $this->getLore()), false),
        ])->response(function (EditFormResponseProcessor $response) {
            $response->preprocessAt(1, function ($value) {
                return array_filter(array_map("trim", explode(";", $value)), fn(string $t) => $t !== "");
            });
        });
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setItemVariableName($content[0]);
        $this->setLore($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getItemVariableName(), $this->getLore()];
    }
}
