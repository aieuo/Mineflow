<?php
declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\base\EntityFlowItem;
use aieuo\mineflow\flowItem\base\EntityFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\mineflow\EntityVariableDropdown;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\utils\Language;

class SetInvisible extends FlowItem implements EntityFlowItem {
    use EntityFlowItemTrait;
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    public function __construct(string $entity = "", private bool $invisible = true) {
        parent::__construct(self::SET_INVISIBLE, FlowItemCategory::ENTITY);

        $this->setEntityVariableName($entity);
    }

    public function getDetailDefaultReplaces(): array {
        return ["entity", "invisible"];
    }

    public function getDetailReplaces(): array {
        return [
            $this->getEntityVariableName(),
            Language::get("action.setInvisible.".($this->isInvisible() ? "visible" : "invisible")),
        ];
    }

    public function setInvisible(bool $invisible): void {
        $this->invisible = $invisible;
    }

    public function isInvisible(): bool {
        return $this->invisible;
    }

    public function isDataValid(): bool {
        return $this->getEntityVariableName() !== "";
    }

    public function onExecute(FlowItemExecutor $source): \Generator {
        $entity = $this->getEntity($source);
        $entity->setInvisible($this->isInvisible());
        yield true;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            new EntityVariableDropdown($variables, $this->getEntityVariableName()),
            new Toggle("@action.setInvisible.form.invisible", $this->isInvisible()),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->setEntityVariableName($content[0]);
        $this->setInvisible($content[1]);
    }

    public function serializeContents(): array {
        return [$this->getEntityVariableName(), $this->isInvisible()];
    }
}
