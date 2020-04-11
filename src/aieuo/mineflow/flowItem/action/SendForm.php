<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\formAPI\ModalForm;
use aieuo\mineflow\trigger\Trigger;
use aieuo\mineflow\trigger\TriggerHolder;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\Main;
use aieuo\mineflow\formAPI\element\Toggle;
use pocketmine\Player;

class SendForm extends Action implements PlayerFlowItem {
    use PlayerFlowItemTrait;

    protected $id = self::SEND_FORM;

    protected $name = "action.sendForm.name";
    protected $detail = "action.sendForm.detail";
    protected $detailDefaultReplace = ["player", "form"];

    protected $category = Categories::CATEGORY_ACTION_FORM;

    protected $targetRequired = Recipe::TARGET_REQUIRED_PLAYER;
    protected $returnValueType = self::RETURN_NONE;

    /** @var string */
    private $formName;

    public function __construct(string $playerName = "target", string $formName = "") {
        $this->playerVariableName = $playerName;
        $this->formName = $formName;
    }

    public function setFormName(string $formName) {
        $this->formName = $formName;
    }

    public function getFormName(): string {
        return $this->formName;
    }

    public function isDataValid(): bool {
        return $this->formName !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getPlayerVariableName(), $this->getFormName()]);
    }

    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        $name = $origin->replaceVariables($this->getFormName());
        $manager = Main::getFormManager();
        $form = $manager->getForm($name);
        if ($form === null) {
            throw new \UnexpectedValueException(Language::get("action.sendForm.notFound", [$this->getName()]));
        }

        $player = $this->getPlayer($origin);
        $this->throwIfInvalidPlayer($player);

        $form = clone $form;
        $form->onReceive([$this, "onReceive"])->addArgs($form)->show($player);
        return true;
    }

    public function onReceive(Player $player, $data, Form $form) {
        $holder = TriggerHolder::getInstance();
        $trigger = new Trigger(Trigger::TYPE_FORM, $form->getName());
        if ($data === null) {
            $trigger->setKey($form->getName().";close");
            if ($holder->existsRecipeByTrigger($trigger)) {
                $recipes = $holder->getRecipes($trigger);
                $recipes->executeAll($player);
            }
            return;
        }
        $variables = Main::getFormManager()->getFormDataVariable($form, $data);
        if ($holder->existsRecipeByTrigger($trigger)) {
            $recipes = $holder->getRecipes($trigger);
            $recipes->executeAll($player, $variables);
        }
        switch ($form) {
            case $form instanceof ModalForm:
                /** @var bool $data */
                $trigger->setKey($form->getName().";".($data ? "1" : "2"));
                if ($holder->existsRecipeByTrigger($trigger)) {
                    $recipes = $holder->getRecipes($trigger);
                    $recipes->executeAll($player, $variables);
                }
                break;
            case $form instanceof ListForm:
                /** @var int $data */
                $button = $form->getButton($data);
                $trigger->setKey($form->getName().";".$button->getUUId());
                if ($holder->existsRecipeByTrigger($trigger)) {
                    $recipes = $holder->getRecipes($trigger);
                    $recipes->executeAll($player, $variables);
                }
                break;
        }
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@flowItem.form.target.player", Language::get("form.example", ["target"]), $default[1] ?? $this->getPlayerVariableName()),
                new Input("@action.sendForm.form.name", Language::get("form.example", ["aieuo"]), $default[2] ?? $this->getFormName()),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $errors = [];
        if ($data[1] === "") $data[1] = "target";
        if ($data[2] === "") {
            $errors[] = ["@form.insufficient", 2];
        }
        return ["status" => empty($errors), "contents" => [$data[1], $data[2]], "cancel" => $data[3], "errors" => $errors];
    }

    public function loadSaveData(array $content): Action {
        if (!isset($content[1])) throw new \OutOfBoundsException();

        $this->setPlayerVariableName($content[0]);
        $this->setFormName($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getPlayerVariableName(), $this->getFormName()];
    }
}