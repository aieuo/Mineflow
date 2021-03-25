<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem;

use aieuo\mineflow\economy\Economy;
use aieuo\mineflow\flowItem\action\block\CreateBlockVariable;
use aieuo\mineflow\flowItem\action\command\Command;
use aieuo\mineflow\flowItem\action\command\CommandConsole;
use aieuo\mineflow\flowItem\action\common\DoNothing;
use aieuo\mineflow\flowItem\action\common\GetDate;
use aieuo\mineflow\flowItem\action\common\SendMessageToConsole;
use aieuo\mineflow\flowItem\action\entity\AddDamage;
use aieuo\mineflow\flowItem\action\entity\AddEffect;
use aieuo\mineflow\flowItem\action\entity\CreateHumanEntity;
use aieuo\mineflow\flowItem\action\entity\GetEntity;
use aieuo\mineflow\flowItem\action\entity\LookAt;
use aieuo\mineflow\flowItem\action\entity\Motion;
use aieuo\mineflow\flowItem\action\entity\MoveTo;
use aieuo\mineflow\flowItem\action\entity\SetHealth;
use aieuo\mineflow\flowItem\action\entity\SetImmobile;
use aieuo\mineflow\flowItem\action\entity\SetMaxHealth;
use aieuo\mineflow\flowItem\action\entity\SetNameTag;
use aieuo\mineflow\flowItem\action\entity\SetPitch;
use aieuo\mineflow\flowItem\action\entity\SetScale;
use aieuo\mineflow\flowItem\action\entity\SetYaw;
use aieuo\mineflow\flowItem\action\entity\Teleport;
use aieuo\mineflow\flowItem\action\entity\UnsetImmobile;
use aieuo\mineflow\flowItem\action\event\EventCancel;
use aieuo\mineflow\flowItem\action\form\SendForm;
use aieuo\mineflow\flowItem\action\form\SendInputForm;
use aieuo\mineflow\flowItem\action\form\SendMenuForm;
use aieuo\mineflow\flowItem\action\inventory\AddItem;
use aieuo\mineflow\flowItem\action\inventory\ClearInventory;
use aieuo\mineflow\flowItem\action\inventory\EquipArmor;
use aieuo\mineflow\flowItem\action\inventory\RemoveItem;
use aieuo\mineflow\flowItem\action\inventory\RemoveItemAll;
use aieuo\mineflow\flowItem\action\inventory\SetItem;
use aieuo\mineflow\flowItem\action\inventory\SetItemInHand;
use aieuo\mineflow\flowItem\action\item\AddEnchantment;
use aieuo\mineflow\flowItem\action\item\CreateItemVariable;
use aieuo\mineflow\flowItem\action\item\RegisterCraftingRecipe;
use aieuo\mineflow\flowItem\action\item\SetItemCount;
use aieuo\mineflow\flowItem\action\item\SetItemDamage;
use aieuo\mineflow\flowItem\action\item\SetItemLore;
use aieuo\mineflow\flowItem\action\item\SetItemName;
use aieuo\mineflow\flowItem\action\math\Calculate;
use aieuo\mineflow\flowItem\action\math\Calculate2;
use aieuo\mineflow\flowItem\action\math\CalculateReversePolishNotation;
use aieuo\mineflow\flowItem\action\math\FourArithmeticOperations;
use aieuo\mineflow\flowItem\action\math\GenerateRandomNumber;
use aieuo\mineflow\flowItem\action\math\GetE;
use aieuo\mineflow\flowItem\action\math\GetPi;
use aieuo\mineflow\flowItem\action\player\AddPermission;
use aieuo\mineflow\flowItem\action\player\AddXpLevel;
use aieuo\mineflow\flowItem\action\player\AddXpProgress;
use aieuo\mineflow\flowItem\action\player\AllowClimbWalls;
use aieuo\mineflow\flowItem\action\player\AllowFlight;
use aieuo\mineflow\flowItem\action\player\BroadcastMessage;
use aieuo\mineflow\flowItem\action\player\GetArmorInventoryContents;
use aieuo\mineflow\flowItem\action\player\GetInventoryContents;
use aieuo\mineflow\flowItem\action\player\GetPlayerByName;
use aieuo\mineflow\flowItem\action\player\GetTargetBlock;
use aieuo\mineflow\flowItem\action\player\HideScoreboard;
use aieuo\mineflow\flowItem\action\player\Kick;
use aieuo\mineflow\flowItem\action\player\PlaySound;
use aieuo\mineflow\flowItem\action\player\RemoveBossbar;
use aieuo\mineflow\flowItem\action\player\RemovePermission;
use aieuo\mineflow\flowItem\action\player\SendMessage;
use aieuo\mineflow\flowItem\action\player\SendMessageToOp;
use aieuo\mineflow\flowItem\action\player\SendPopup;
use aieuo\mineflow\flowItem\action\player\SendTip;
use aieuo\mineflow\flowItem\action\player\SendTitle;
use aieuo\mineflow\flowItem\action\player\SetFood;
use aieuo\mineflow\flowItem\action\player\SetGamemode;
use aieuo\mineflow\flowItem\action\player\SetSitting;
use aieuo\mineflow\flowItem\action\player\SetSleeping;
use aieuo\mineflow\flowItem\action\player\ShowBossbar;
use aieuo\mineflow\flowItem\action\plugin\AddMoney;
use aieuo\mineflow\flowItem\action\plugin\ExecuteIFChain;
use aieuo\mineflow\flowItem\action\plugin\GetMoney;
use aieuo\mineflow\flowItem\action\plugin\SetMoney;
use aieuo\mineflow\flowItem\action\plugin\TakeMoney;
use aieuo\mineflow\flowItem\action\scoreboard\CreateScoreboardVariable;
use aieuo\mineflow\flowItem\action\scoreboard\DecrementScoreboardScore;
use aieuo\mineflow\flowItem\action\scoreboard\IncrementScoreboardScore;
use aieuo\mineflow\flowItem\action\scoreboard\RemoveScoreboardScore;
use aieuo\mineflow\flowItem\action\scoreboard\RemoveScoreboardScoreName;
use aieuo\mineflow\flowItem\action\scoreboard\SetScoreboardScore;
use aieuo\mineflow\flowItem\action\scoreboard\SetScoreboardScoreName;
use aieuo\mineflow\flowItem\action\scoreboard\ShowScoreboard;
use aieuo\mineflow\flowItem\action\script\CallRecipe;
use aieuo\mineflow\flowItem\action\script\CreateConfigVariable;
use aieuo\mineflow\flowItem\action\script\ElseAction;
use aieuo\mineflow\flowItem\action\script\ElseifAction;
use aieuo\mineflow\flowItem\action\script\ExecuteRecipe;
use aieuo\mineflow\flowItem\action\script\ExecuteRecipeWithEntity;
use aieuo\mineflow\flowItem\action\script\ExitRecipe;
use aieuo\mineflow\flowItem\action\script\ForAction;
use aieuo\mineflow\flowItem\action\script\ForeachAction;
use aieuo\mineflow\flowItem\action\script\ForeachPosition;
use aieuo\mineflow\flowItem\action\script\IFAction;
use aieuo\mineflow\flowItem\action\script\RemoveConfigData;
use aieuo\mineflow\flowItem\action\script\RepeatAction;
use aieuo\mineflow\flowItem\action\script\SaveConfigFile;
use aieuo\mineflow\flowItem\action\script\SaveData;
use aieuo\mineflow\flowItem\action\script\SetConfigData;
use aieuo\mineflow\flowItem\action\script\Wait;
use aieuo\mineflow\flowItem\action\script\WhileTaskAction;
use aieuo\mineflow\flowItem\action\string\EditString;
use aieuo\mineflow\flowItem\action\string\StringLength;
use aieuo\mineflow\flowItem\action\variable\AddListVariable;
use aieuo\mineflow\flowItem\action\variable\AddMapVariable;
use aieuo\mineflow\flowItem\action\variable\AddVariable;
use aieuo\mineflow\flowItem\action\variable\CountListVariable;
use aieuo\mineflow\flowItem\action\variable\CreateListVariable;
use aieuo\mineflow\flowItem\action\variable\CreateMapVariable;
use aieuo\mineflow\flowItem\action\variable\CreateMapVariableFromJson;
use aieuo\mineflow\flowItem\action\variable\DeleteListVariableContent;
use aieuo\mineflow\flowItem\action\variable\DeleteVariable;
use aieuo\mineflow\flowItem\action\variable\GetVariableNested;
use aieuo\mineflow\flowItem\action\variable\JoinListVariableToString;
use aieuo\mineflow\flowItem\action\world\AddParticle;
use aieuo\mineflow\flowItem\action\world\CreatePositionVariable;
use aieuo\mineflow\flowItem\action\world\DropItem;
use aieuo\mineflow\flowItem\action\world\GenerateRandomPosition;
use aieuo\mineflow\flowItem\action\world\GetBlock;
use aieuo\mineflow\flowItem\action\world\GetDistance;
use aieuo\mineflow\flowItem\action\world\GetEntitySidePosition;
use aieuo\mineflow\flowItem\action\world\PlaySoundAt;
use aieuo\mineflow\flowItem\action\world\PositionVariableAddition;
use aieuo\mineflow\flowItem\action\world\SetBlock;
use aieuo\mineflow\flowItem\condition\AndScript;
use aieuo\mineflow\flowItem\condition\CanAddItem;
use aieuo\mineflow\flowItem\condition\CheckNothing;
use aieuo\mineflow\flowItem\condition\ComparisonNumber;
use aieuo\mineflow\flowItem\condition\ComparisonString;
use aieuo\mineflow\flowItem\condition\Condition;
use aieuo\mineflow\flowItem\condition\ExistsConfigData;
use aieuo\mineflow\flowItem\condition\ExistsConfigFile;
use aieuo\mineflow\flowItem\condition\ExistsItem;
use aieuo\mineflow\flowItem\condition\ExistsListVariableKey;
use aieuo\mineflow\flowItem\condition\ExistsVariable;
use aieuo\mineflow\flowItem\condition\Gamemode;
use aieuo\mineflow\flowItem\condition\HasPermission;
use aieuo\mineflow\flowItem\condition\InArea;
use aieuo\mineflow\flowItem\condition\InHand;
use aieuo\mineflow\flowItem\condition\IsActiveEntity;
use aieuo\mineflow\flowItem\condition\IsActiveEntityVariable;
use aieuo\mineflow\flowItem\condition\IsCreature;
use aieuo\mineflow\flowItem\condition\IsCreatureVariable;
use aieuo\mineflow\flowItem\condition\IsFlying;
use aieuo\mineflow\flowItem\condition\IsOp;
use aieuo\mineflow\flowItem\condition\IsPlayer;
use aieuo\mineflow\flowItem\condition\IsPlayerOnline;
use aieuo\mineflow\flowItem\condition\IsPlayerOnlineByName;
use aieuo\mineflow\flowItem\condition\IsPlayerVariable;
use aieuo\mineflow\flowItem\condition\IsSneaking;
use aieuo\mineflow\flowItem\condition\LessMoney;
use aieuo\mineflow\flowItem\condition\NandScript;
use aieuo\mineflow\flowItem\condition\NorScript;
use aieuo\mineflow\flowItem\condition\NotScript;
use aieuo\mineflow\flowItem\condition\ORScript;
use aieuo\mineflow\flowItem\condition\OverMoney;
use aieuo\mineflow\flowItem\condition\RandomNumber;
use aieuo\mineflow\flowItem\condition\RemoveItemCondition;
use aieuo\mineflow\flowItem\condition\TakeMoneyCondition;
use pocketmine\Server;

class FlowItemFactory {

    /** @var FlowItem[] */
    private static $list = [];
    private static $aliases = [];

    public static function init(): void {
        /* actions */
        self::register(new DoNothing);
        self::register(new EventCancel);
        self::register(new GetDate);
        self::register(new RegisterCraftingRecipe);
        self::register(new SendMessageToConsole);
        /* message */
        self::register(new SendMessage);
        self::register(new SendTip);
        self::register(new SendPopup);
        self::register(new BroadcastMessage);
        self::register(new SendMessageToOp);
        self::register(new SendTitle);
        /* entity */
        self::register(new SetNameTag);
        self::register(new GetEntity);
        self::register(new Teleport);
        self::register(new Motion);
        self::register(new MoveTo);
        self::register(new SetYaw);
        self::register(new SetPitch);
        self::register(new LookAt);
        self::register(new AddDamage);
        self::register(new SetImmobile);
        self::register(new UnsetImmobile);
        self::register(new SetHealth);
        self::register(new SetMaxHealth);
        self::register(new SetScale);
        self::register(new AddEffect);
        self::register(new CreateHumanEntity);
        /* player */
        self::register(new GetPlayerByName);
        self::register(new SetSleeping);
        self::register(new SetSitting);
        self::register(new Kick);
        self::register(new SetFood);
        self::register(new SetGamemode);
        self::register(new ShowBossbar);
        self::register(new RemoveBossbar);
        self::register(new ShowScoreboard);
        self::register(new HideScoreboard);
        self::register(new PlaySound);
        self::register(new AddPermission);
        self::register(new RemovePermission);
        self::register(new AddXpProgress);
        self::register(new AddXpLevel);
        self::register(new GetTargetBlock);
        self::register(new AllowFlight);
        self::register(new AllowClimbWalls);
        /* item */
        self::register(new CreateItemVariable);
        self::register(new AddItem);
        self::register(new SetItemInHand);
        self::register(new RemoveItem);
        self::register(new RemoveItemAll);
        self::register(new SetItemDamage);
        self::register(new SetItemCount);
        self::register(new SetItemName);
        self::register(new SetItemLore);
        self::register(new AddEnchantment);
        self::register(new EquipArmor);
        self::register(new SetItem);
        self::register(new ClearInventory);
        self::register(new GetInventoryContents);
        self::register(new GetArmorInventoryContents);
        /* script */
        self::register(new IFAction);
        self::register(new ElseifAction);
        self::register(new ElseAction);
        self::register(new RepeatAction);
        self::register(new ForAction);
        self::register(new ForeachAction);
        self::register(new ForeachPosition);
        self::register(new WhileTaskAction);
        self::register(new Wait);
        self::register(new CallRecipe);
        self::register(new ExecuteRecipe);
        self::register(new ExecuteRecipeWithEntity);
        self::register(new SaveData);
        self::register(new CreateConfigVariable);
        self::register(new SetConfigData);
        self::register(new RemoveConfigData);
        self::register(new SaveConfigFile);
        self::register(new ExitRecipe);
        /* calculation */
        self::register(new FourArithmeticOperations);
        self::register(new Calculate);
        self::register(new Calculate2);
        self::register(new GetPi);
        self::register(new GetE);
        self::register(new GenerateRandomNumber);
        self::register(new CalculateReversePolishNotation);
        /* string */
        self::register(new EditString);
        self::register(new StringLength);
        /* variable */
        self::register(new AddVariable);
        self::register(new DeleteVariable);
        self::register(new CreateListVariable);
        self::register(new AddListVariable);
        self::register(new CreateMapVariable);
        self::register(new AddMapVariable);
        self::register(new CreateMapVariableFromJson);
        self::register(new DeleteListVariableContent);
        self::register(new CreatePositionVariable);
        self::register(new GetVariableNested);
        self::register(new CountListVariable);
        self::register(new JoinListVariableToString);
        /* form */
        self::register(new SendForm);
        self::register(new SendInputForm);
        self::register(new SendMenuForm);
        /* command */
        self::register(new Command);
        self::register(new CommandConsole);
        /* block */
        self::register(new CreateBlockVariable);
        /* level */
        self::register(new SetBlock);
        self::register(new GetBlock);
        self::register(new AddParticle);
        self::register(new PlaySoundAt);
        self::register(new DropItem);
        self::register(new GetDistance);
        self::register(new GetEntitySidePosition);
        self::register(new GenerateRandomPosition);
        self::register(new PositionVariableAddition);
        /* scoreboard */
        self::register(new CreateScoreboardVariable);
        self::register(new SetScoreboardScore);
        self::register(new SetScoreboardScoreName);
        self::register(new IncrementScoreboardScore);
        self::register(new DecrementScoreboardScore);
        self::register(new RemoveScoreboardScore);
        self::register(new RemoveScoreboardScoreName);
        /* other plugins */
        if (Economy::isPluginLoaded()) {
            self::register(new AddMoney);
            self::register(new TakeMoney);
            self::register(new SetMoney);
            self::register(new GetMoney);
        }
        if (Server::getInstance()->getPluginManager()->getPlugin("if") !== null) {
            self::register(new ExecuteIFChain);
        }


        /** conditions */
        /* common */
        self::register(new CheckNothing);
        self::register(new IsOp);
        self::register(new IsSneaking);
        self::register(new IsFlying);
        self::register(new RandomNumber);
        /* money */
        self::register(new OverMoney);
        self::register(new LessMoney);
        self::register(new TakeMoneyCondition);
        /* item */
        self::register(new InHand);
        self::register(new ExistsItem);
        self::register(new CanAddItem);
        self::register(new RemoveItemCondition);
        /* script */
        self::register(new ComparisonNumber);
        self::register(new ComparisonString);
        self::register(new AndScript);
        self::register(new ORScript);
        self::register(new NotScript);
        self::register(new NorScript);
        self::register(new NandScript);
        self::register(new ExistsConfigFile);
        self::register(new ExistsConfigData);
        /* entity */
        self::register(new IsActiveEntity);
        self::register(new IsPlayer);
        self::register(new IsCreature);
        self::register(new IsActiveEntityVariable);
        self::register(new IsPlayerVariable);
        self::register(new IsCreatureVariable);
        self::register(new InArea);
        /* player */
        self::register(new Gamemode);
        self::register(new HasPermission);
        self::register(new IsPlayerOnline);
        self::register(new IsPlayerOnlineByName);
        /* variable */
        self::register(new ExistsVariable);
        self::register(new ExistsListVariableKey);


        self::registerAliases();
    }

    public static function registerAliases(): void {
        self::registerAlias(new class extends FourArithmeticOperations {
            protected $id = self::FOUR_ARITHMETIC_OPERATIONS_ADD;
            public function __construct(string $value1 = "", string $value2 = "", string $resultName = "result") {
                parent::__construct($value1, self::ADDITION, $value2, $resultName);
            }
        });
        self::registerAlias(new class extends FourArithmeticOperations {
            protected $id = self::FOUR_ARITHMETIC_OPERATIONS_SUB;
            public function __construct(string $value1 = "", string $value2 = "", string $resultName = "result") {
                parent::__construct($value1, self::SUBTRACTION, $value2, $resultName);
            }
        });
        self::registerAlias(new class extends FourArithmeticOperations {
            protected $id = self::FOUR_ARITHMETIC_OPERATIONS_MUL;
            public function __construct(string $value1 = "", string $value2 = "", string $resultName = "result") {
                parent::__construct($value1, self::MULTIPLICATION, $value2, $resultName);
            }
        });
        self::registerAlias(new class extends FourArithmeticOperations {
            protected $id = self::FOUR_ARITHMETIC_OPERATIONS_DIV;
            public function __construct(string $value1 = "", string $value2 = "", string $resultName = "result") {
                parent::__construct($value1, self::DIVISION, $value2, $resultName);
            }
        });
        self::registerAlias(new class extends Calculate {
            protected $id = self::CALCULATE_SIN;
            public function __construct(string $value = "", string $resultName = "result") {
                parent::__construct($value, (string)self::CALC_SIN, $resultName);
            }
        });
        self::registerAlias(new class extends Calculate {
            protected $id = self::CALCULATE_COS;
            public function __construct(string $value = "", string $resultName = "result") {
                parent::__construct($value, (string)self::CALC_COS, $resultName);
            }
        });
        self::registerAlias(new class extends Calculate {
            protected $id = self::CALCULATE_TAN;
            public function __construct(string $value = "", string $resultName = "result") {
                parent::__construct($value, (string)self::CALC_TAN, $resultName);
            }
        });
        self::registerAlias(new class extends Calculate {
            protected $id = self::CALCULATE_FLOOR;
            public function __construct(string $value = "", string $resultName = "result") {
                parent::__construct($value, (string)self::CALC_FLOOR, $resultName);
            }
        });
        self::registerAlias(new class extends Calculate {
            protected $id = self::CALCULATE_CEIL;
            public function __construct(string $value = "", string $resultName = "result") {
                parent::__construct($value, (string)self::CALC_CEIL, $resultName);
            }
        });
        self::registerAlias(new class extends Calculate2 {
            protected $id = self::CALCULATE2_ROUND;
            public function __construct(string $value1 = "", string $value2 = "", string $resultName = "result") {
                parent::__construct($value1, $value2, (string)self::CALC_ROUND, $resultName);
            }
        });
    }

    /**
     * @param string $id
     * @param bool $alias
     * @return FlowItem|null
     */
    public static function get(string $id, bool $alias = false): ?FlowItem {
        if (isset(self::$list[$id])) {
            return clone self::$list[$id];
        }
        if ($alias and isset(self::$aliases[$id])) {
            return clone self::$aliases[$id];
        }
        return null;
    }

    /**
     * @param string|null $category
     * @param int|null $permission
     * @param bool $getAction
     * @param bool $getCondition
     * @return FlowItem[]
     */
    public static function getByFilter(string $category = null, int $permission = null, bool $getAction = true, bool $getCondition = true): array {
        $items = [];
        foreach (self::$list as $item) {
            if ($category !== null and $item->getCategory() !== $category) continue;
            if ($permission !== null and $item->getPermission() > $permission) continue;
            if (!$getAction and !($item instanceof Condition)) continue;
            if (!$getCondition and ($item instanceof Condition)) continue;
            $items[] = $item;
        }
        return $items;
    }

    /**
     * @return array
     */
    public static function getAll(): array {
        return self::$list;
    }

    /**
     * @param FlowItem $action
     */
    public static function register(FlowItem $action): void {
        self::$list[$action->getId()] = clone $action;
    }

    /**
     * @param FlowItem $action
     */
    public static function registerAlias(FlowItem $action): void {
        self::$aliases[$action->getId()] = clone $action;
    }
}