<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\flowItem\placeholder\EntityPlaceholder;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use pocketmine\math\Vector3;
use SOFe\AwaitGenerator\Await;
use function array_merge;

class Motion extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private string $x = "0";
    private string $y = "0";
    private string $z = "0";
    private EntityPlaceholder $entity;

    public function __construct(string $entity = "", string $x = "0", string $y = "0", string $z = "0") {
        parent::__construct(self::MOTION, FlowItemCategory::ENTITY);

        $this->entity = new EntityPlaceholder("entity", $entity);
        $this->setPosition($x, $y, $z);
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->entity->getName(), "x", "y", "z"];
    }

    public function getDetailReplaces(): array {
        return array_merge([$this->entity->get()], $this->getPosition());
    }

    public function getEntity(): EntityPlaceholder {
        return $this->entity;
    }

    public function setPosition(string $x, string $y, string $z): void {
        $this->x = $x;
        $this->y = $y;
        $this->z = $z;
    }

    public function getPosition(): array {
        return [$this->x, $this->y, $this->z];
    }

    public function isDataValid(): bool {
        return $this->entity->isNotEmpty() and $this->x !== "" and $this->y !== "" and $this->z !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $motions = array_map(fn($value) => $this->getFloat($source->replaceVariables($value)), $this->getPosition());
        $entity = $this->entity->getOnlineEntity($source);

        $motion = new Vector3($motions[0], $motions[1], $motions[2]);
        $entity->setMotion($motion);

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
           $this->entity->createFormElement($variables),
            new ExampleNumberInput("@action.motion.form.x", "2", $this->x, true),
            new ExampleNumberInput("@action.motion.form.y", "3", $this->y, true),
            new ExampleNumberInput("@action.motion.form.z", "4", $this->z, true),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->entity->set($content[0]);
        $this->setPosition($content[1], $content[2], $content[3]);
    }

    public function serializeContents(): array {
        return array_merge([$this->entity->get()], $this->getPosition());
    }
}
