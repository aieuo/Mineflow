<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\PlayerVariableDropdown;
use pocketmine\network\mcpe\protocol\ToastRequestPacket;
use SOFe\AwaitGenerator\Await;

class SendToast extends FlowItem implements PlayerFlowItem {
    use PlayerFlowItemTrait;
    use ActionNameWithMineflowLanguage;

    public function __construct(
        private string $player = "",
        private string $title = "",
        private string $body = ""
    ) {
        parent::__construct(self::SEND_TOAST, FlowItemCategory::PLAYER_MESSAGE);

        $this->setPlayerVariableName($player);
    }

    public function getDetailDefaultReplaces(): array {
        return ["player", "title", "body"];
    }

    public function getDetailReplaces(): array {
        return [$this->getPlayerVariableName(), $this->getTitle(), $this->getBody()];
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

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $title = $source->replaceVariables($this->getTitle());
        $body = $source->replaceVariables($this->getBody());
        $player = $this->getOnlinePlayer($source);

        $player->getNetworkSession()->sendDataPacket(ToastRequestPacket::create($title, $body));

        yield Await::ALL;
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
