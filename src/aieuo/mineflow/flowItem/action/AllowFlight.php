<?php
declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\ExampleInput;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Toggle;

class AllowFlight extends Action implements PlayerFlowItem {
    use PlayerFlowItemTrait;

    protected $id = self::ALLOW_FLIGHT;

    protected $name = "action.allowFlight.name";
    protected $detail = "action.allowFlight.detail";
    protected $detailDefaultReplace = ["player", "allow"];

    protected $category = Category::PLAYER;

    protected $targetRequired = Recipe::TARGET_REQUIRED_PLAYER;
    protected $permission = self::PERMISSION_LEVEL_1;

    /** @var bool */
    private $allow;

    public function __construct(string $player = "target", string $allow = "true") {
        $this->setPlayerVariableName($player);
        $this->allow = $allow === "true";
    }

    public function setAllow(bool $allow): void {
        $this->allow = $allow;
    }

    public function isAllow(): bool {
        return $this->allow;
    }

    public function isDataValid(): bool {
        return $this->getPlayerVariableName() !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getPlayerVariableName(), ["action.allowFlight.".($this->isAllow() ? "allow" : "notAllow")]]);
    }

    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        $player = $this->getPlayer($origin);
        $this->throwIfInvalidPlayer($player);

        $player->setAllowFlight($this->isAllow());
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new ExampleInput("@flowItem.form.target.player", "target", $default[1] ?? $this->getPlayerVariableName(), true),
                new Toggle("@action.allowFlight.form.allow", $default[2] ?? $this->isAllow()),
                new CancelToggle()
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        return ["contents" => [$data[1], $data[2]], "cancel" => $data[3], "errors" => []];
    }

    public function loadSaveData(array $content): Action {
        if (!isset($content[1])) throw new \OutOfBoundsException();
        $this->setPlayerVariableName($content[0]);
        $this->setAllow($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getPlayerVariableName(), $this->isAllow()];
    }
}