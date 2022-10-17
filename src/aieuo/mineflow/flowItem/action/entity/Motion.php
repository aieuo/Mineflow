<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\base\EntityFlowItem;
use aieuo\mineflow\flowItem\base\EntityFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\EntityVariableDropdown;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use pocketmine\math\Vector3;
use SOFe\AwaitGenerator\Await;
use function array_merge;

class Motion extends FlowItem implements EntityFlowItem {
    use EntityFlowItemTrait;
    use ActionNameWithMineflowLanguage;

    private string $x = "0";
    private string $y = "0";
    private string $z = "0";

    public function __construct(string $entity = "", string $x = "0", string $y = "0", string $z = "0") {
        parent::__construct(self::MOTION, FlowItemCategory::ENTITY);

        $this->setEntityVariableName($entity);
        $this->setPosition($x, $y, $z);
    }

    public function getDetailDefaultReplaces(): array {
        return ["entity", "x", "y", "z"];
    }

    public function getDetailReplaces(): array {
        return array_merge([$this->getEntityVariableName()], $this->getPosition());
    }

    public function setPosition(string $x, string $y, string $z): self {
        $this->x = $x;
        $this->y = $y;
        $this->z = $z;
        return $this;
    }

    public function getPosition(): array {
        return [$this->x, $this->y, $this->z];
    }

    public function isDataValid(): bool {
        return $this->getEntityVariableName() !== "" and $this->x !== "" and $this->y !== "" and $this->z !== "";
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $motions = array_map(fn($value) => $this->getFloat($source->replaceVariables($value)), $this->getPosition());
        $entity = $this->getOnlineEntity($source);

        $motion = new Vector3($motions[0], $motions[1], $motions[2]);
        $entity->setMotion($motion);

        yield Await::ALL;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new EntityVariableDropdown($variables, $this->getEntityVariableName()),
            new ExampleNumberInput("@action.motion.form.x", "2", $this->x, true),
            new ExampleNumberInput("@action.motion.form.y", "3", $this->y, true),
            new ExampleNumberInput("@action.motion.form.z", "4", $this->z, true),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setEntityVariableName($content[0]);
        $this->setPosition($content[1], $content[2], $content[3]);
        return $this;
    }

    public function serializeContents(): array {
        return array_merge([$this->getEntityVariableName()], $this->getPosition());
    }
}
