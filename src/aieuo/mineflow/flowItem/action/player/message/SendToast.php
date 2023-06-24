<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player\message;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\flowItem\placeholder\PlayerPlaceholder;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use pocketmine\network\mcpe\protocol\ToastRequestPacket;
use SOFe\AwaitGenerator\Await;

class SendToast extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private PlayerPlaceholder $player;

    public function __construct(
        string $player = "",
        private string $title = "",
        private string $body = ""
    ) {
        parent::__construct(self::SEND_TOAST, FlowItemCategory::PLAYER_MESSAGE);

        $this->player = new PlayerPlaceholder("player", $player);
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->player->getName(), "title", "body"];
    }

    public function getDetailReplaces(): array {
        return [$this->player->get(), $this->getTitle(), $this->getBody()];
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
        return $this->player->get() !== "";
    }

    public function getPlayer(): PlayerPlaceholder {
        return $this->player;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $title = $source->replaceVariables($this->getTitle());
        $body = $source->replaceVariables($this->getBody());
        $player = $this->player->getOnlinePlayer($source);

        $player->getNetworkSession()->sendDataPacket(ToastRequestPacket::create($title, $body));

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->player->createFormElement($variables),
            new ExampleInput("@action.sendToast.form.title", "aieuo", $this->getTitle()),
            new ExampleInput("@action.sendToast.form.subtitle", "aieuo", $this->getBody()),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->player->set($content[0]);
        $this->setTitle($content[1]);
        $this->setBody($content[2]);
    }

    public function serializeContents(): array {
        return [$this->player->get(), $this->getTitle(), $this->getBody()];
    }
}
