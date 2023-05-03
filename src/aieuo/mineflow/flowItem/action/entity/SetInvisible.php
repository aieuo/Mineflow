<?php
declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\flowItem\base\EntityFlowItem;
use aieuo\mineflow\flowItem\base\EntityFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\EntityVariableDropdown;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\utils\Language;

class SetInvisible extends FlowItem implements EntityFlowItem {
    use EntityFlowItemTrait;

    protected string $id = self::SET_INVISIBLE;

    protected string $name = "action.setInvisible.name";
    protected string $detail = "action.setInvisible.detail";
    protected array $detailDefaultReplace = ["entity", "invisible"];

    protected string $category = FlowItemCategory::ENTITY;

    public function __construct(string $entity = "", private bool $invisible = true) {
        $this->setEntityVariableName($entity);
    }

    public function setInvisible(bool $invisible): void {
        $this->invisible = $invisible;
    }

    public function isInvisible(): bool {
        return $this->invisible;
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [
            $this->getEntityVariableName(),
            Language::get("action.setInvisible.".($this->isInvisible() ? "visible" : "invisible")),
        ]);
    }

    public function isDataValid(): bool {
        return $this->getEntityVariableName() !== "";
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $entity = $this->getEntity($source);
        $this->throwIfInvalidEntity($entity);

        $entity->setInvisible($this->isInvisible());
        yield true;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new EntityVariableDropdown($variables, $this->getEntityVariableName()),
            new Toggle("@action.setInvisible.form.invisible", $this->isInvisible()),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setEntityVariableName($content[0]);
        $this->setInvisible($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getEntityVariableName(), $this->isInvisible()];
    }
}
