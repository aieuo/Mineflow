<?php

namespace aieuo\mineflow\formAPI\element\mineflow\button;

use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\utils\ButtonImage;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\ui\customForm\CustomFormForm;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\object\PlayerVariable;
use pocketmine\player\Player;
use function array_merge;

class FormButton extends Button {

    protected string $type = self::TYPE_FORM;
    private string $formName;

    public bool $skipIfCallOnClick = false;

    public function __construct(string $formName, string $text = null, ?callable $onClick = null, ?ButtonImage $image = null) {
        $this->formName = $formName;
        parent::__construct($text ?? $formName, $onClick ?? function(Player $player) use($formName) {
            $manager = Mineflow::getFormManager();
            $form = $manager->getForm($formName) ?? Mineflow::getAddonManager()->getForm($formName);
            if ($form === null) {
                $player->sendMessage(Language::get("action.sendForm.notFound", [$formName]));
                return;
            }

            $variables = array_merge(DefaultVariables::getServerVariables(), ["target" => new PlayerVariable($player)]);

            $form = clone $form;
            $form->replaceVariablesFromExecutor(new FlowItemExecutor([], $player, $variables));
            $form->onReceive([new CustomFormForm(), "onReceive"])
                ->onClose([new CustomFormForm(), "onClose"])
                ->addArgs($form)
                ->show($player);
        }, $image);
    }

    public function setFormName(string $formName): self {
        $this->formName = $formName;
        return $this;
    }

    public function getFormName(): string {
        return $this->formName;
    }

    public function __toString(): string {
        return Language::get("form.form.formMenu.list.".$this->getType(), [$this->getText(), $this->getFormName()]);
    }

    public function jsonSerialize(): array {
        return [
            "text" => Language::replace($this->text),
            "id" => $this->getUUID(),
            "image" => $this->getImage(),
            "mineflow" => [
                "form" => $this->formName,
                "type" => $this->getType(),
            ],
        ];
    }

    public static function fromSerializedArray(array $data): ?self {
        if (!isset($data["text"]) or !isset($data["mineflow"]["form"])) return null;

        $button = new FormButton($data["mineflow"]["form"], $data["text"]);
        if (!empty($data["image"])) {
            $button->setImage(new ButtonImage($data["image"]["data"], $data["image"]["type"]));
        }

        return $button->uuid($data["id"] ?? "");
    }
}