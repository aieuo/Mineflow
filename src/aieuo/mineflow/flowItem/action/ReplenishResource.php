<?php /** @noinspection PhpUndefinedClassInspection */

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\PositionFlowItem;
use aieuo\mineflow\flowItem\base\PositionFlowItemTrait;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\ReplenishResourcesAPI;
use pocketmine\Server;


class ReplenishResource extends Action implements PositionFlowItem {
    use PositionFlowItemTrait;

    protected $id = self::REPLENISH_RESOURCE;

    protected $name = "action.replenishResource.name";
    protected $detail = "action.replenishResource.detail";
    protected $detailDefaultReplace = ["position"];

    protected $category = Category::PLUGIN;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;

    public function __construct(string $position = "pos") {
        $this->setPositionVariableName($position);
    }

    public function isDataValid(): bool {
        return $this->getPositionVariableName() !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getPositionVariableName()]);
    }

    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        $position = $this->getPosition($origin);
        $this->throwIfInvalidPosition($position);

        if (Server::getInstance()->getPluginManager()->getPlugin("ReplenishResources") === null) {
            throw new \UnexpectedValueException(Language::get("flowItem.error", [$this->getName(), Language::get("action.otherPlugin.notFound", ["ReplenishResources"])]));
        }
        $api = ReplenishResourcesAPI::getInstance();
        $api->replenish($position);
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new ExampleInput("@flowItem.form.target.position", "pos", $default[1] ?? $this->getPositionVariableName()),
                new CancelToggle()
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        if ($data[1] === "") $data[1] = "pos";
        return ["contents" => [$data[1]], "cancel" => $data[2], "errors" => []];
    }

    public function loadSaveData(array $content): Action {
        if (!isset($content[0])) throw new \OutOfBoundsException();
        $this->setPositionVariableName($content[0]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getPositionVariableName()];
    }
}