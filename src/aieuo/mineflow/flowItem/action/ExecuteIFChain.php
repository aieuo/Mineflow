<?php /** @noinspection PhpUndefinedNamespaceInspection */

/** @noinspection PhpUndefinedClassInspection */

namespace aieuo\mineflow\flowItem\action;

use aieuo\ip\IFPlugin;
use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\ExampleInput;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\Language;
use pocketmine\event\Event;
use pocketmine\Server;

class ExecuteIFChain extends FlowItem implements PlayerFlowItem {
    use PlayerFlowItemTrait;

    protected $id = self::EXECUTE_IF_CHAIN;

    protected $name = "action.executeIFChain.name";
    protected $detail = "action.executeIFChain.detail";
    protected $detailDefaultReplace = ["chain", "player"];

    protected $category = Category::PLUGIN;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;

    protected $permission = self::PERMISSION_LEVEL_1;

    /** @var string */
    private $chainName;

    /** @var string[] */
    private $args = [];

    public function __construct(string $chain = "", string $player = "target") {
        $this->setPlayerVariableName($player);
        $this->chainName = $chain;
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

    public function execute(Recipe $origin) {
        $this->throwIfCannotExecute();

        $name = $origin->replaceVariables($this->getChainName());

        $player = $this->getPlayer($origin);
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
        if ($origin->getEvent() instanceof Event) $options["event"] = $origin->getEvent();

        $manager->executeIfMatchCondition(
            $player,
            $data["if"],
            $data["match"],
            $data["else"],
            $options
        );
        yield true;
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new ExampleInput("@action.executeIFChain.form.name", "aieuo", $default[1] ?? $this->getChainName(), true),
                new ExampleInput("@flowItem.form.target.player", "target", $default[2] ?? $this->getPlayerVariableName(), true),
                new CancelToggle()
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        return ["contents" => [$data[1], $data[2]], "cancel" => $data[3], "errors" => []];
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