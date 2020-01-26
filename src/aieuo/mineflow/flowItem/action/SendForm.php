<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\formAPI\ModalForm;
use aieuo\mineflow\trigger\Trigger;
use aieuo\mineflow\trigger\TriggerHolder;
use pocketmine\entity\Entity;
use aieuo\mineflow\utils\Logger;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\Main;
use aieuo\mineflow\formAPI\element\Toggle;
use pocketmine\Player;

class SendForm extends Action {

    protected $id = self::SEND_FORM;

    protected $name = "action.sendForm.name";
    protected $detail = "action.sendForm.detail";
    protected $detailDefaultReplace = ["name"];

    protected $category = Categories::CATEGORY_ACTION_FORM;

    protected $targetRequired = Recipe::TARGET_REQUIRED_PLAYER;

    /** @var string */
    private $formName;

    public function __construct(string $name = "") {
        $this->formName = $name;
    }

    public function setFormName(string $formName) {
        $this->formName = $formName;
    }

    public function getFormName(): string {
        return $this->formName;
    }

    public function isDataValid(): bool {
        return !empty($this->formName);
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getFormName()]);
    }

    public function execute(?Entity $target, Recipe $origin): ?bool {
        if (!$this->canExecute($target)) return null;

        $name = $origin->replaceVariables($this->getFormName());
        $manager = Main::getInstance()->getFormManager();
        $form = $manager->getForm($name);
        if ($form === null) {
            Logger::warning(Language::get("action.sendForm.notFound", [$this->getName()]), $target);
            return null;
        }
        $form = clone $form;
        /** @var Player $target */
        $form->onReceive([$this, "onReceive"])->addArgs($form)->show($target);
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
        $variables = Main::getInstance()->getFormManager()->getFormDataVariable($form, $data);
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
                new Input("@action.sendForm.form.name", Language::get("form.example", ["aieuo"]), $default[1] ?? $this->getFormName()),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $errors = [];
        $name = $data[1];
        if ($name === "") {
            $errors[] = ["@form.insufficient", 1];
        }
        return ["status" => empty($errors), "contents" => [$name], "cancel" => $data[2], "errors" => $errors];
    }

    public function loadSaveData(array $content): ?Action {
        if (!isset($content[0])) return null;
        $this->setFormName($content[0]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getFormName()];
    }
}