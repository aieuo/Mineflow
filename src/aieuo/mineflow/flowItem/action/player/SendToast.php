<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\PlayerVariableDropdown;
use aieuo\mineflow\utils\Language;
use pocketmine\network\mcpe\protocol\ToastRequestPacket;

class SendToast extends FlowItem implements PlayerFlowItem {
    use PlayerFlowItemTrait;

    protected string $id = self::SEND_TOAST;

    protected string $name = "action.sendToast.name";
    protected string $detail = "action.sendToast.detail";
    protected array $detailDefaultReplace = ["player", "title", "body"];

    protected string $category = FlowItemCategory::PLAYER;

    public function __construct(
        private string $player = "",
        private string $title = "",
        private string $body = ""
    ) {
        $this->setPlayerVariableName($player);
    }

    public function setTitle(string $title): void {
        $this->title = $title;
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function setBody(string $body): void {
        $this->body = $body;
    }

    public function getBody(): string {
        return $this->body;
    }

    public function isDataValid(): bool {
        return $this->getPlayerVariableName() !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getPlayerVariableName(), $this->getTitle(), $this->getBody()]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $title = $source->replaceVariables($this->getTitle());
        $body = $source->replaceVariables($this->getBody());

        $player = $this->getPlayer($source);
        $this->throwIfInvalidPlayer($player);

        $player->getNetworkSession()->sendDataPacket(ToastRequestPacket::create($title, $body));
        yield true;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new PlayerVariableDropdown($variables, $this->getPlayerVariableName()),
            new ExampleInput("@action.sendToast.form.title", "aieuo", $this->getTitle()),
            new ExampleInput("@action.sendToast.form.subtitle", "aieuo", $this->getBody()),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setPlayerVariableName($content[0]);
        $this->setTitle($content[1]);
        $this->setBody($content[2]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getPlayerVariableName(), $this->getTitle(), $this->getBody()];
    }
}
