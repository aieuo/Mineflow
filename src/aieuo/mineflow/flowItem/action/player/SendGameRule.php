<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\flowItem\argument\PlayerArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\Toggle;
use pocketmine\network\mcpe\protocol\GameRulesChangedPacket;
use pocketmine\network\mcpe\protocol\types\BoolGameRule;
use SOFe\AwaitGenerator\Await;

class SendGameRule extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private PlayerArgument $player;
    private StringArgument $gamerule;

    public function __construct(string $player = "", string $gamerule = "", private bool $value = true) {
        parent::__construct(self::SEND_BOOL_GAMERULE, FlowItemCategory::PLAYER);

        $this->player = new PlayerArgument("player", $player);
        $this->gamerule = new StringArgument("gamerule", $gamerule, "@action.setGamerule.form.gamerule", example: "showcoordinates");
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->player->getName(), "gamerule", "value"];
    }

    public function getDetailReplaces(): array {
        return [$this->player->get(), $this->gamerule->get(), $this->getValue() ? "true" : "false"];
    }

    public function getGamerule(): StringArgument {
        return $this->gamerule;
    }

    public function setValue(bool $value): void {
        $this->value = $value;
    }

    public function getValue(): bool {
        return $this->value;
    }

    public function isDataValid(): bool {
        return $this->player->get() !== "" and $this->gamerule->isValid();
    }

    public function getPlayer(): PlayerArgument {
        return $this->player;
    }

    public function onExecute(FlowItemExecutor $source): \Generator {
        $gamerule = $this->gamerule->getString($source);
        $player = $this->player->getOnlinePlayer($source);

        $pk = GameRulesChangedPacket::create([
            $gamerule => new BoolGameRule($this->getValue(), true),
        ]);
        $player->getNetworkSession()->sendDataPacket($pk);
        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->player->createFormElement($variables),
            $this->gamerule->createFormElement($variables),
            new Toggle("@action.setGamerule.form.value", $this->getValue()),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->player->set($content[0]);
        $this->gamerule->set($content[1]);
        $this->setValue($content[2]);
    }

    public function serializeContents(): array {
        return [$this->player->get(), $this->gamerule->get(), $this->getValue()];
    }
}
