<?php

namespace aieuo\mineflow\recipe\template;

use aieuo\mineflow\exception\InvalidFormValueException;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\recipe\Recipe;
use pocketmine\player\Player;
use function array_pop;

abstract class RecipeTemplate {

    final public function __construct(
        private string $recipeName,
        private string $recipeGroup,
    ) {
    }

    abstract public static function getName(): string;

    public function getRecipeName(): string {
        return $this->recipeName;
    }

    public function getRecipeGroup(): string {
        return $this->recipeGroup;
    }

    /**
     * @throws InvalidFormValueException
     */
    abstract public function getSettingFormPart(): ?RecipeTemplateSettingFormPart;

    abstract public function build(): Recipe;

    public function createRecipe(Player $player, callable $onComplete): void {
        $formPart = $this->getSettingFormPart();
        if ($formPart === null) {
            $onComplete($this->build());
            return;
        }

        $form = new CustomForm(static::getName());
        $form->addContents($formPart->getElements());
        $form->addContent(new CancelToggle(fn() => $onComplete(null)));
        $form->addMessages($formPart->getMessages());
        $form->onReceive(function (Player $player, array $data) use($onComplete, $form, $formPart) {
            array_pop($data);
            if ($formPart->getOnReceive() === null) {
                $onComplete($this->build());
                return;
            }

            try {
                ($formPart->getOnReceive())($player, fn(bool $result) => $onComplete($result ? $this->build() : null));
            } catch (InvalidFormValueException $e) {
                $form->resend([[$e->getMessage(), $e->getIndex()]]);
            }
        });
        $form->onClose(fn() => $onComplete(null));
        $form->show($player);
    }
}