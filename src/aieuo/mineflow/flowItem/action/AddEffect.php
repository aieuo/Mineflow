<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\EntityFlowItem;
use aieuo\mineflow\flowItem\base\EntityFlowItemTrait;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\Main;
use aieuo\mineflow\formAPI\element\Toggle;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\entity\Living;

class AddEffect extends Action implements EntityFlowItem {
    use EntityFlowItemTrait;

    protected $id = self::ADD_EFFECT;

    protected $name = "action.addEffect.name";
    protected $detail = "action.addEffect.detail";
    protected $detailDefaultReplace = ["entity", "id", "power", "time"];

    protected $category = Category::ENTITY;

    protected $targetRequired = Recipe::TARGET_REQUIRED_ENTITY;

    /** @var string */
    private $effectId;
    /** @var string */
    private $power;
    /** @var string */
    private $time;

    /** @var bool */
    private $visible = false;

    public function __construct(string $entity = "target", string $id = "", string $time = "", string $power = "1") {
        $this->setEntityVariableName($entity);
        $this->effectId = $id;
        $this->time = $time;
        $this->power = $power;
    }

    public function setEffectId(string $effectId) {
        $this->effectId = $effectId;
    }

    public function getEffectId(): string {
        return $this->effectId;
    }

    public function setPower(string $power): void {
        $this->power = $power;
    }

    public function getPower(): string {
        return $this->power;
    }

    public function setTime(string $time): void {
        $this->time = $time;
    }

    public function getTime(): string {
        return $this->time;
    }

    public function isDataValid(): bool {
        return $this->getEntityVariableName() !== "" and $this->effectId !== "" and $this->power !== "" and $this->time !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getEntityVariableName(), $this->getEffectId(), $this->getPower(), $this->getTime()]);
    }

    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        $effectId = $origin->replaceVariables($this->getEffectId());
        $time = $origin->replaceVariables($this->getTime());
        $power = $origin->replaceVariables($this->getPower());

        $effect = Effect::getEffectByName($effectId);
        if ($effect === null) $effect = Effect::getEffect($effectId);
        if ($effect === null) throw new \UnexpectedValueException(Language::get("action.effect.notFound"));
        $this->throwIfInvalidNumber($time);
        $this->throwIfInvalidNumber($power);

        $entity = $this->getEntity($origin);
        $this->throwIfInvalidEntity($entity);

        if ($entity instanceof Living) {
            $entity->addEffect(new EffectInstance($effect, (int)$time * 20, (int)$power - 1, false));
        }
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@flowItem.form.target.entity", Language::get("form.example", ["target"]), $default[1] ?? $this->getEntityVariableName()),
                new Input("@action.addEffect.form.effect", Language::get("form.example", ["1"]), $default[2] ?? $this->getEffectId()),
                new Input("@action.addEffect.form.time", Language::get("form.example", ["300"]), $default[3] ?? $this->getTime()),
                new Input("@action.addEffect.form.power", Language::get("form.example", ["1"]), $default[4] ?? $this->getPower()),
                new Toggle("@action.addEffect.form.visible", $default[5] ?? $this->visible),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $errors = [];
        if ($data[1] === "") $data[1] = "target";
        if ($data[2] === "") $errors[] = ["@form.insufficient", 2];
        if ($data[3] === "") $data[3] = "300";
        if ($data[4] === "") $data[4] = "1";
        $containsVariable = Main::getVariableHelper()->containsVariable($data[4]);
        if (!$containsVariable and !is_numeric($data[4])) {
            $errors[] = ["@flowItem.error.notNumber", 4];
        }
        return ["contents" => [$data[1], $data[2], $data[3], $data[4], $data[5]], "cancel" => $data[6], "errors" => $errors];
    }

    public function loadSaveData(array $content): Action {
        if (!isset($content[4])) throw new \OutOfBoundsException();
        $this->setEntityVariableName($content[0]);
        $this->setEffectId($content[1]);
        $this->setTime($content[2]);
        $this->setPower($content[3]);
        $this->visible = $content[4];
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getEntityVariableName(), $this->getEffectId(), $this->getTime(), $this->getPower(), $this->visible];
    }
}