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

class TransferServer extends FlowItem implements PlayerFlowItem {
    use PlayerFlowItemTrait;

    protected string $id = self::TRANSFER_SERVER;

    protected string $name = "action.transfer.name";
    protected string $detail = "action.transfer.detail";
    protected array $detailDefaultReplace = ["player", "ip", "port"];

    protected string $category = FlowItemCategory::PLAYER;

    public function __construct(string $player = "", private string $ip = "", private string $port = "19132") {
        $this->setPlayerVariableName($player);
    }

    public function setIp(string $ip): void {
        $this->ip = $ip;
    }

    public function getIp(): string {
        return $this->ip;
    }

    public function setPort(string $port): void {
        $this->port = $port;
    }

    public function getPort(): string {
        return $this->port;
    }

    public function isDataValid(): bool {
        return $this->getPlayerVariableName() !== "" and $this->ip !== "" and $this->port !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getPlayerVariableName(), $this->getIp(), $this->getPort()]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $ip = $source->replaceVariables($this->getIp());
        $port = $source->replaceVariables($this->getPort());
        $this->throwIfInvalidNumber($port, 1, 65535);

        $player = $this->getPlayer($source);
        $this->throwIfInvalidPlayer($player);

        $player->transfer($ip, (int)$port);
        yield true;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new PlayerVariableDropdown($variables, $this->getPlayerVariableName()),
            new ExampleInput("@action.transfer.form.ip", "aieuo.tokyo", $this->getIp()),
            new ExampleInput("@action.transfer.form.port", "19132", $this->getIp()),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setPlayerVariableName($content[0]);
        $this->setIp($content[1]);
        $this->setPort($content[2]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getPlayerVariableName(), $this->getIp(), $this->getPort()];
    }
}
