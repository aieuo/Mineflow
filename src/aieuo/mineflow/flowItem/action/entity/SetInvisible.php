<?php
declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\flowItem\argument\EntityArgument;
use aieuo\mineflow\formAPI\element\Toggle;
use aieuo\mineflow\utils\Language;
use SOFe\AwaitGenerator\Await;

class SetInvisible extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private EntityArgument $entity;

    public function __construct(string $entity = "", private bool $invisible = true) {
        parent::__construct(self::SET_INVISIBLE, FlowItemCategory::ENTITY);

        $this->entity = new EntityArgument("entity", $entity);
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->entity->getName(), "invisible"];
    }

    public function getDetailReplaces(): array {
        return [
            $this->entity->get(),
            Language::get("action.setInvisible.".($this->isInvisible() ? "visible" : "invisible")),
        ];
    }

    public function getEntity(): EntityArgument {
        return $this->entity;
    }

    public function setInvisible(bool $invisible): void {
        $this->invisible = $invisible;
    }

    public function isInvisible(): bool {
        return $this->invisible;
    }

    public function isDataValid(): bool {
        return $this->entity->isNotEmpty();
    }

    public function onExecute(FlowItemExecutor $source): \Generator {
        $entity = $this->entity->getEntity($source);
        $entity->setInvisible($this->isInvisible());
        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
           $this->entity->createFormElement($variables),
            new Toggle("@action.setInvisible.form.invisible", $this->isInvisible()),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->entity->set($content[0]);
        $this->setInvisible($content[1]);
    }

    public function serializeContents(): array {
        return [$this->entity->get(), $this->isInvisible()];
    }
}
