<?php

namespace aieuo\mineflow\flowItem;

use aieuo\mineflow\economy\Economy;
use aieuo\mineflow\flowItem\action\AddDamage;
use aieuo\mineflow\flowItem\action\AddEffect;
use aieuo\mineflow\flowItem\action\AddEnchantment;
use aieuo\mineflow\flowItem\action\AddItem;
use aieuo\mineflow\flowItem\action\AddListVariable;
use aieuo\mineflow\flowItem\action\AddMapVariable;
use aieuo\mineflow\flowItem\action\AddMoney;
use aieuo\mineflow\flowItem\action\AddParticle;
use aieuo\mineflow\flowItem\action\AddPermission;
use aieuo\mineflow\flowItem\action\AddVariable;
use aieuo\mineflow\flowItem\action\AddXpLevel;
use aieuo\mineflow\flowItem\action\AddXpProgress;
use aieuo\mineflow\flowItem\action\AllowClimbWalls;
use aieuo\mineflow\flowItem\action\AllowFlight;
use aieuo\mineflow\flowItem\action\BroadcastMessage;
use aieuo\mineflow\flowItem\action\Calculate;
use aieuo\mineflow\flowItem\action\Calculate2;
use aieuo\mineflow\flowItem\action\CalculateReversePolishNotation;
use aieuo\mineflow\flowItem\action\CallRecipe;
use aieuo\mineflow\flowItem\action\ClearInventory;
use aieuo\mineflow\flowItem\action\Command;
use aieuo\mineflow\flowItem\action\CommandConsole;
use aieuo\mineflow\flowItem\action\CountListVariable;
use aieuo\mineflow\flowItem\action\CreateBlockVariable;
use aieuo\mineflow\flowItem\action\CreateConfigVariable;
use aieuo\mineflow\flowItem\action\CreateHumanEntity;
use aieuo\mineflow\flowItem\action\CreateItemVariable;
use aieuo\mineflow\flowItem\action\CreateListVariable;
use aieuo\mineflow\flowItem\action\CreateMapVariable;
use aieuo\mineflow\flowItem\action\CreateMapVariableFromJson;
use aieuo\mineflow\flowItem\action\CreatePositionVariable;
use aieuo\mineflow\flowItem\action\CreateScoreboardVariable;
use aieuo\mineflow\flowItem\action\DecrementScoreboardScore;
use aieuo\mineflow\flowItem\action\DeleteListVariableContent;
use aieuo\mineflow\flowItem\action\DeleteVariable;
use aieuo\mineflow\flowItem\action\DoNothing;
use aieuo\mineflow\flowItem\action\DropItem;
use aieuo\mineflow\flowItem\action\EditString;
use aieuo\mineflow\flowItem\action\ElseAction;
use aieuo\mineflow\flowItem\action\ElseifAction;
use aieuo\mineflow\flowItem\action\EquipArmor;
use aieuo\mineflow\flowItem\action\EventCancel;
use aieuo\mineflow\flowItem\action\ExecuteIFChain;
use aieuo\mineflow\flowItem\action\ExecuteRecipe;
use aieuo\mineflow\flowItem\action\ExecuteRecipeWithEntity;
use aieuo\mineflow\flowItem\action\ExitRecipe;
use aieuo\mineflow\flowItem\action\ForAction;
use aieuo\mineflow\flowItem\action\ForeachAction;
use aieuo\mineflow\flowItem\action\FourArithmeticOperations;
use aieuo\mineflow\flowItem\action\GenerateRandomNumber;
use aieuo\mineflow\flowItem\action\GetArmorInventoryContents;
use aieuo\mineflow\flowItem\action\GetBlock;
use aieuo\mineflow\flowItem\action\GetDate;
use aieuo\mineflow\flowItem\action\GetDistance;
use aieuo\mineflow\flowItem\action\GetE;
use aieuo\mineflow\flowItem\action\GetEntity;
use aieuo\mineflow\flowItem\action\GetEntitySidePosition;
use aieuo\mineflow\flowItem\action\GetInventoryContents;
use aieuo\mineflow\flowItem\action\GetMoney;
use aieuo\mineflow\flowItem\action\GetPi;
use aieuo\mineflow\flowItem\action\GetPlayerByName;
use aieuo\mineflow\flowItem\action\GetTargetBlock;
use aieuo\mineflow\flowItem\action\GetVariableNested;
use aieuo\mineflow\flowItem\action\HideScoreboard;
use aieuo\mineflow\flowItem\action\IFAction;
use aieuo\mineflow\flowItem\action\IncrementScoreboardScore;
use aieuo\mineflow\flowItem\action\JoinListVariableToString;
use aieuo\mineflow\flowItem\action\Kick;
use aieuo\mineflow\flowItem\action\Motion;
use aieuo\mineflow\flowItem\action\PlaySound;
use aieuo\mineflow\flowItem\action\PlaySoundAt;
use aieuo\mineflow\flowItem\action\RemoveBossbar;
use aieuo\mineflow\flowItem\action\RemoveConfigData;
use aieuo\mineflow\flowItem\action\RemoveItem;
use aieuo\mineflow\flowItem\action\RemoveItemAll;
use aieuo\mineflow\flowItem\action\RemovePermission;
use aieuo\mineflow\flowItem\action\RemoveScoreboardScore;
use aieuo\mineflow\flowItem\action\RemoveScoreboardScoreName;
use aieuo\mineflow\flowItem\action\RepeatAction;
use aieuo\mineflow\flowItem\action\ReplenishResource;
use aieuo\mineflow\flowItem\action\SaveConfigFile;
use aieuo\mineflow\flowItem\action\SaveData;
use aieuo\mineflow\flowItem\action\SendForm;
use aieuo\mineflow\flowItem\action\SendInputForm;
use aieuo\mineflow\flowItem\action\SendMenuForm;
use aieuo\mineflow\flowItem\action\SendMessage;
use aieuo\mineflow\flowItem\action\SendMessageToOp;
use aieuo\mineflow\flowItem\action\SendPopup;
use aieuo\mineflow\flowItem\action\SendTip;
use aieuo\mineflow\flowItem\action\SendTitle;
use aieuo\mineflow\flowItem\action\SetBlock;
use aieuo\mineflow\flowItem\action\SetConfigData;
use aieuo\mineflow\flowItem\action\SetFood;
use aieuo\mineflow\flowItem\action\SetGamemode;
use aieuo\mineflow\flowItem\action\SetHealth;
use aieuo\mineflow\flowItem\action\SetImmobile;
use aieuo\mineflow\flowItem\action\SetItem;
use aieuo\mineflow\flowItem\action\SetItemCount;
use aieuo\mineflow\flowItem\action\SetItemDamage;
use aieuo\mineflow\flowItem\action\SetItemInHand;
use aieuo\mineflow\flowItem\action\SetItemLore;
use aieuo\mineflow\flowItem\action\SetItemName;
use aieuo\mineflow\flowItem\action\SetMaxHealth;
use aieuo\mineflow\flowItem\action\SetMoney;
use aieuo\mineflow\flowItem\action\SetNameTag;
use aieuo\mineflow\flowItem\action\SetPitch;
use aieuo\mineflow\flowItem\action\SetScale;
use aieuo\mineflow\flowItem\action\SetScoreboardScore;
use aieuo\mineflow\flowItem\action\SetScoreboardScoreName;
use aieuo\mineflow\flowItem\action\SetSitting;
use aieuo\mineflow\flowItem\action\SetSleeping;
use aieuo\mineflow\flowItem\action\SetYaw;
use aieuo\mineflow\flowItem\action\ShowBossbar;
use aieuo\mineflow\flowItem\action\ShowScoreboard;
use aieuo\mineflow\flowItem\action\StringLength;
use aieuo\mineflow\flowItem\action\TakeMoney;
use aieuo\mineflow\flowItem\action\Teleport;
use aieuo\mineflow\flowItem\action\UnsetImmobile;
use aieuo\mineflow\flowItem\action\Wait;
use aieuo\mineflow\flowItem\action\WhileTaskAction;
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
use aieuo\mineflow\flowItem\condition\IsCreature;
use aieuo\mineflow\flowItem\condition\IsFlying;
use aieuo\mineflow\flowItem\condition\IsOp;
use aieuo\mineflow\flowItem\condition\IsPlayer;
use aieuo\mineflow\flowItem\condition\IsPlayerOnline;
use aieuo\mineflow\flowItem\condition\IsPlayerOnlineByName;
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
        self::register(new SetYaw);
        self::register(new SetPitch);
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
        if (Server::getInstance()->getPluginManager()->getPlugin("ReplenishResources") !== null) {
            self::register(new ReplenishResource);
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
                parent::__construct($value1, $value2, self::CALC_ROUND, $resultName);
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