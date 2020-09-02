<?php

namespace aieuo\mineflow\flowItem\condition;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\ExampleInput;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\recipe\Recipe;

class HasPermission extends FlowItem implements Condition, PlayerFlowItem {
    use PlayerFlowItemTrait;

    protected $id = self::HAS_PERMISSION;

    protected $name = "condition.hasPermission.name";
    protected $detail = "condition.hasPermission.detail";
    protected $detailDefaultReplace = ["player", "permission"];

    protected $category = Category::PLAYER;

    protected $targetRequired = Recipe::TARGET_REQUIRED_PLAYER;

    /** @var string */
    private $playerPermission;

    public function __construct(string $player = "target", string $permission = "") {
        $this->setPlayerVariableName($player);
        $this->playerPermission = $permission;
    }

    public function setPlayerPermission(string $playerPermission): void {
        $this->playerPermission = $playerPermission;
    }

    public function getPlayerPermission(): string {
        return $this->playerPermission;
    }

    public function isDataValid(): bool {
        return $this->getPlayerVariableName() !== "" and $this->getPlayerPermission() !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getPlayerVariableName(), $this->getPlayerPermission()]);
    }

    public function execute(Recipe $origin) {
        $this->throwIfCannotExecute();

        $player = $this->getPlayer($origin);
        $this->throwIfInvalidPlayer($player);

        $permission = $this->getPlayerPermission();

        yield true;
        return $player->hasPermission($permission);
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new ExampleInput("@flowItem.form.target.player", "target", $default[1] ?? $this->getPlayerVariableName(), true),
                new ExampleInput("@condition.hasPermission.form.permission", "mineflow.customcommand.op", $default[2] ?? $this->getPlayerPermission(), true),
                new CancelToggle()
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        return ["contents" => [$data[1], $data[2]], "cancel" => $data[3], "errors" => []];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setPlayerVariableName($content[0]);
        $this->setPlayerPermission($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getPlayerVariableName(), $this->getPlayerPermission()];
    }
}