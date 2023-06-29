<?php
declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use SOFe\AwaitGenerator\Await;

class TransferServer extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private PlayerArgument $player;
    private StringArgument $ip;
    private NumberArgument $port;

    public function __construct(string $player = "", string $ip = "", int $port = 19132) {
        parent::__construct(self::TRANSFER_SERVER, FlowItemCategory::PLAYER);

        $this->player = new PlayerArgument("player", $player);
        $this->ip = new StringArgument("ip", $ip, example: "aieuo.tokyo");
        $this->port = new NumberArgument("port", $port, example: "19132", min: 1, max: 65535);
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->player->getName(), "ip", "port"];
    }

    public function getDetailReplaces(): array {
        return [$this->player->get(), $this->ip->get(), $this->getPort()];
    }

    public function isDataValid(): bool {
        return $this->player->get() !== "" and $this->ip->isValid() and $this->port->isValid();
    }

    public function getPlayer(): PlayerArgument {
        return $this->player;
    }

    public function getIp(): StringArgument {
        return $this->ip;
    }

    public function getPort(): NumberArgument {
        return $this->port;
    }

    public function onExecute(FlowItemExecutor $source): \Generator {
        $ip = $this->ip->getString($source);
        $port = $this->port->getInt($source);

        $player = $this->player->getOnlinePlayer($source);
        $player->transfer($ip, $port);
        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->player->createFormElement($variables),
            $this->ip->createFormElement($variables),
            $this->port->createFormElement($variables),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->player->set($content[0]);
        $this->ip->set($content[1]);
        $this->port->set($content[2]);
    }

    public function serializeContents(): array {
        return [$this->player->get(), $this->ip->get(), $this->port->get()];
    }
}
