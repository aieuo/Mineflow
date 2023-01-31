<?php /** @noinspection PhpUndefinedNamespaceInspection */

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\plugin;

use aieuo\ip\IFPlugin;
use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\FlowItemPermission;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\PlayerVariableDropdown;
use aieuo\mineflow\utils\Language;
use pocketmine\event\Event;
use pocketmine\Server;
use SOFe\AwaitGenerator\Await;

class ExecuteIFChain extends FlowItem implements PlayerFlowItem {
    use PlayerFlowItemTrait;
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    /** @var string[] */
    private array $args = [];

    public function __construct(private string $chainName = "", string $player = "") {
        parent::__construct(self::EXECUTE_IF_CHAIN, FlowItemCategory::PLUGIN_IF_PLUGIN);
        $this->setPermissions([FlowItemPermission::LOOP]);

        $this->setPlayerVariableName($player);
    }

    public function getDetailDefaultReplaces(): array {
        return ["chain", "player"];
    }

    public function getDetailReplaces(): array {
        return [$this->getChainName(), $this->getPlayerVariableName()];
    }

    public function setChainName(string $name): void {
        $this->chainName = $name;
    }

    public function getChainName(): string {
        return $this->chainName;
    }

    public function isDataValid(): bool {
        return $this->getChainName() !== "" and $this->getPlayerVariableName() !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $name = $source->replaceVariables($this->getChainName());

        $player = $this->getOnlinePlayer($source);
        if (Server::getInstance()->getPluginManager()->getPlugin("if") === null) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.otherPlugin.notFound", ["if"]));
        }

        $manager = IFPlugin::getInstance()->getChainManager();
        if (!$manager->exists($this->getChainName())) {
            throw new InvalidFlowValueException($this->getName(), \aieuo\ip\utils\Language::get("process.cooperation.notFound"));
        }

        $data = $manager->get($name);
        $options = ["player" => $player];
        if ($source->getEvent() instanceof Event) $options["event"] = $source->getEvent();

        $manager->executeIfMatchCondition(
            $player,
            $data["if"],
            $data["match"],
            $data["else"],
            $options
        );

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            new ExampleInput("@action.executeIFChain.form.name", "aieuo", $this->getChainName(), true),
            new PlayerVariableDropdown($variables, $this->getPlayerVariableName()),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->setChainName($content[0]);
        $this->setPlayerVariableName($content[1]);
    }

    public function serializeContents(): array {
        return [$this->getChainName(), $this->getPlayerVariableName()];
    }
}
