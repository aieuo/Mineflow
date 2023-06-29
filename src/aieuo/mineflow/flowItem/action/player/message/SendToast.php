<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player\message;

use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use pocketmine\network\mcpe\protocol\ToastRequestPacket;
use SOFe\AwaitGenerator\Await;

class SendToast extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private PlayerArgument $player;
    private StringArgument $title;
    private StringArgument $body;

    public function __construct(string $player = "", string $title = "", string $body = "") {
        parent::__construct(self::SEND_TOAST, FlowItemCategory::PLAYER_MESSAGE);

        $this->player = new PlayerArgument("player", $player);
        $this->title = new StringArgument("title", $title, example: "aieuo", optional: true);
        $this->body = new StringArgument("subtitle", $body, example: "aieuo", optional: true);
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->player->getName(), "title", "body"];
    }

    public function getDetailReplaces(): array {
        return [$this->player->get(), $this->title->get(), $this->body->get()];
    }

    public function getTitle(): StringArgument {
        return $this->title;
    }

    public function getBody(): StringArgument {
        return $this->body;
    }

    public function isDataValid(): bool {
        return $this->player->get() !== "";
    }

    public function getPlayer(): PlayerArgument {
        return $this->player;
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $title = $this->title->getString($source);
        $body = $this->body->getString($source);
        $player = $this->player->getOnlinePlayer($source);

        $player->getNetworkSession()->sendDataPacket(ToastRequestPacket::create($title, $body));

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->player->createFormElement($variables),
            $this->title->createFormElement($variables),
            $this->body->createFormElement($variables),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->player->set($content[0]);
        $this->title->set($content[1]);
        $this->body->set($content[2]);
    }

    public function serializeContents(): array {
        return [$this->player->get(), $this->title->get(), $this->body->get()];
    }
}
