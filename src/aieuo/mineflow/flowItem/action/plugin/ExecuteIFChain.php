<?php /** @noinspection PhpUndefinedNamespaceInspection */

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\plugin;

use aieuo\ip\IFPlugin;
use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\formAPI\element\mineflow\PlayerVariableDropdown;
use aieuo\mineflow\utils\Language;
use pocketmine\event\Event;
use pocketmine\Server;

class ExecuteIFChain extends FlowItem implements PlayerFlowItem {
    use PlayerFlowItemTrait;

    protected string $name = "action.executeIFChain.name";
    protected string $detail = "action.executeIFChain.detail";
    protected array $detailDefaultReplace = ["chain", "player"];

    /** @var string[] */
    private array $args = [];

    public function __construct(private string $chainName = "", string $player = "") {
        parent::__construct(self::EXECUTE_IF_CHAIN, FlowItemCategory::PLUGIN);

        $this->setPlayerVariableName($player);
    }

    public function getPermissions(): array {
        return [self::PERMISSION_LOOP];
    }

    public function setChainName(string $name): self {
        $this->chainName = $name;
        return $this;
    }

    public function getChainName(): string {
        return $this->chainName;
    }

    public function isDataValid(): bool {
        return $this->getChainName() !== "" and $this->getPlayerVariableName() !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getChainName(), $this->getPlayerVariableName()]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $name = $source->replaceVariables($this->getChainName());

        $player = $this->getPlayer($source);
        $this->throwIfInvalidPlayer($player);

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
        yield true;
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ExampleInput("@action.executeIFChain.form.name", "aieuo", $this->getChainName(), true),
            new PlayerVariableDropdown($variables, $this->getPlayerVariableName()),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setChainName($content[0]);
        $this->setPlayerVariableName($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getChainName(), $this->getPlayerVariableName()];
    }
}