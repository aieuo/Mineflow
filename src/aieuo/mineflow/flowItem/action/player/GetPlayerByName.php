<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\player;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\PlayerVariable;
use pocketmine\player\Player;
use pocketmine\Server;
use SOFe\AwaitGenerator\Await;

class GetPlayerByName extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    public function __construct(
        private string $playerName = "",
        private string $resultName = "player"
    ) {
        parent::__construct(self::GET_PLAYER, FlowItemCategory::PLAYER);
    }

    public function getDetailDefaultReplaces(): array {
        return ["name", "result"];
    }

    public function getDetailReplaces(): array {
        return [$this->getPlayerName(), $this->getResultName()];
    }

    public function setPlayerName(string $name): void {
        $this->playerName = $name;
    }

    public function getPlayerName(): string {
        return $this->playerName;
    }

    public function setResultName(string $name): void {
        $this->resultName = $name;
    }

    public function getResultName(): string {
        return $this->resultName;
    }

    public function isDataValid(): bool {
        return $this->getPlayerName() !== "" and !empty($this->getResultName());
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $name = $source->replaceVariables($this->getPlayerName());
        $resultName = $source->replaceVariables($this->getResultName());

        $player = Server::getInstance()->getPlayerExact($name);
        if (!($player instanceof Player)) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.getPlayer.player.notFound"));
        }

        $result = new PlayerVariable($player, $player->getName());
        $source->addVariable($resultName, $result);

        yield Await::ALL;
        return $this->getResultName();
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            new ExampleInput("@action.getPlayer.form.target", "aieuo", $this->getPlayerName(), true),
            new ExampleInput("@action.form.resultVariableName", "player", $this->getResultName(), true),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->setPlayerName($content[0]);
        $this->setResultName($content[1]);
    }

    public function serializeContents(): array {
        return [$this->getPlayerName(), $this->getResultName()];
    }

    public function getAddingVariables(): array {
        return [
            $this->getResultName() => new DummyVariable(PlayerVariable::class, $this->getPlayerName())
        ];
    }
}
