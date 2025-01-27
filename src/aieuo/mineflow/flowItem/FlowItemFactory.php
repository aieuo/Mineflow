<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem;

use aieuo\mineflow\flowItem\action\alias\Calculate2Round;
use aieuo\mineflow\flowItem\action\alias\CalculateCeil;
use aieuo\mineflow\flowItem\action\alias\CalculateCos;
use aieuo\mineflow\flowItem\action\alias\CalculateFloor;
use aieuo\mineflow\flowItem\action\alias\CalculateSin;
use aieuo\mineflow\flowItem\action\alias\CalculateTan;
use aieuo\mineflow\flowItem\action\alias\FourArithmeticOperationsAdd;
use aieuo\mineflow\flowItem\action\alias\FourArithmeticOperationsDiv;
use aieuo\mineflow\flowItem\action\alias\FourArithmeticOperationsMul;
use aieuo\mineflow\flowItem\action\alias\FourArithmeticOperationsSub;
use aieuo\mineflow\flowItem\action\block\CreateBlockVariable;
use aieuo\mineflow\flowItem\action\command\Command;
use aieuo\mineflow\flowItem\action\command\CommandConsole;
use aieuo\mineflow\flowItem\action\common\DoNothing;
use aieuo\mineflow\flowItem\action\common\GetDate;
use aieuo\mineflow\flowItem\action\common\SendMessageToConsole;
use aieuo\mineflow\flowItem\action\config\CreateConfigVariable;
use aieuo\mineflow\flowItem\action\config\RemoveConfigData;
use aieuo\mineflow\flowItem\action\config\SaveConfigFile;
use aieuo\mineflow\flowItem\action\config\SetConfigData;
use aieuo\mineflow\flowItem\action\entity\AddDamage;
use aieuo\mineflow\flowItem\action\entity\AddEffect;
use aieuo\mineflow\flowItem\action\entity\ClearAllEffect;
use aieuo\mineflow\flowItem\action\entity\ClearEffect;
use aieuo\mineflow\flowItem\action\entity\CreateHumanEntity;
use aieuo\mineflow\flowItem\action\entity\GetEntity;
use aieuo\mineflow\flowItem\action\entity\LookAt;
use aieuo\mineflow\flowItem\action\entity\Motion;
use aieuo\mineflow\flowItem\action\entity\MoveTo;
use aieuo\mineflow\flowItem\action\entity\SetHealth;
use aieuo\mineflow\flowItem\action\entity\SetImmobile;
use aieuo\mineflow\flowItem\action\entity\SetInvisible;
use aieuo\mineflow\flowItem\action\entity\SetMaxHealth;
use aieuo\mineflow\flowItem\action\entity\SetNameTag;
use aieuo\mineflow\flowItem\action\entity\SetPitch;
use aieuo\mineflow\flowItem\action\entity\SetScale;
use aieuo\mineflow\flowItem\action\entity\SetYaw;
use aieuo\mineflow\flowItem\action\entity\Teleport;
use aieuo\mineflow\flowItem\action\entity\TeleportToWorld;
use aieuo\mineflow\flowItem\action\entity\UnsetImmobile;
use aieuo\mineflow\flowItem\action\event\CallCustomTrigger;
use aieuo\mineflow\flowItem\action\event\EventCancel;
use aieuo\mineflow\flowItem\action\event\playerChatEvent\PlayerChatEventSetMessage;
use aieuo\mineflow\flowItem\action\form\button\AddButtonToListForm;
use aieuo\mineflow\flowItem\action\form\CreateListFormVariable;
use aieuo\mineflow\flowItem\action\form\SendConfirmForm;
use aieuo\mineflow\flowItem\action\form\SendDropdown;
use aieuo\mineflow\flowItem\action\form\SendForm;
use aieuo\mineflow\flowItem\action\form\SendInputForm;
use aieuo\mineflow\flowItem\action\form\SendMenuForm;
use aieuo\mineflow\flowItem\action\form\SendSlider;
use aieuo\mineflow\flowItem\action\form\SendStepSlider;
use aieuo\mineflow\flowItem\action\form\ShowFormVariable;
use aieuo\mineflow\flowItem\action\internal\AddLanguageMappings;
use aieuo\mineflow\flowItem\action\internal\AddSpecificLanguageMapping;
use aieuo\mineflow\flowItem\action\internal\GetLanguage;
use aieuo\mineflow\flowItem\action\inventory\AddItem;
use aieuo\mineflow\flowItem\action\inventory\ClearInventory;
use aieuo\mineflow\flowItem\action\inventory\EquipArmor;
use aieuo\mineflow\flowItem\action\inventory\RemoveItem;
use aieuo\mineflow\flowItem\action\inventory\RemoveItemAll;
use aieuo\mineflow\flowItem\action\inventory\SetItem;
use aieuo\mineflow\flowItem\action\inventory\SetItemInHand;
use aieuo\mineflow\flowItem\action\item\AddEnchantment;
use aieuo\mineflow\flowItem\action\item\CreateItemVariable;
use aieuo\mineflow\flowItem\action\item\GetItemData;
use aieuo\mineflow\flowItem\action\item\RegisterCraftingRecipe;
use aieuo\mineflow\flowItem\action\item\RemoveItemData;
use aieuo\mineflow\flowItem\action\item\SetArmorColor;
use aieuo\mineflow\flowItem\action\item\SetItemCount;
use aieuo\mineflow\flowItem\action\item\SetItemDamage;
use aieuo\mineflow\flowItem\action\item\SetItemData;
use aieuo\mineflow\flowItem\action\item\SetItemDataFromNBTJson;
use aieuo\mineflow\flowItem\action\item\SetItemLore;
use aieuo\mineflow\flowItem\action\item\SetItemName;
use aieuo\mineflow\flowItem\action\math\Calculate;
use aieuo\mineflow\flowItem\action\math\Calculate2;
use aieuo\mineflow\flowItem\action\math\CalculateReversePolishNotation;
use aieuo\mineflow\flowItem\action\math\FourArithmeticOperations;
use aieuo\mineflow\flowItem\action\math\GenerateRandomNumber;
use aieuo\mineflow\flowItem\action\math\GetE;
use aieuo\mineflow\flowItem\action\math\GetPi;
use aieuo\mineflow\flowItem\action\player\AddXpLevel;
use aieuo\mineflow\flowItem\action\player\AddXpProgress;
use aieuo\mineflow\flowItem\action\player\AllowClimbWalls;
use aieuo\mineflow\flowItem\action\player\AllowFlight;
use aieuo\mineflow\flowItem\action\player\bossbar\RemoveBossbar;
use aieuo\mineflow\flowItem\action\player\bossbar\ShowBossbar;
use aieuo\mineflow\flowItem\action\player\Emote;
use aieuo\mineflow\flowItem\action\player\GetArmorInventoryContents;
use aieuo\mineflow\flowItem\action\player\GetInventoryContents;
use aieuo\mineflow\flowItem\action\player\GetPlayerByName;
use aieuo\mineflow\flowItem\action\player\GetTargetBlock;
use aieuo\mineflow\flowItem\action\player\HideScoreboard;
use aieuo\mineflow\flowItem\action\player\Kick;
use aieuo\mineflow\flowItem\action\player\message\BroadcastMessage;
use aieuo\mineflow\flowItem\action\player\message\Chat;
use aieuo\mineflow\flowItem\action\player\message\SendActionBarMessage;
use aieuo\mineflow\flowItem\action\player\message\SendJukeboxPopup;
use aieuo\mineflow\flowItem\action\player\message\SendMessage;
use aieuo\mineflow\flowItem\action\player\message\SendMessageToOp;
use aieuo\mineflow\flowItem\action\player\message\SendPopup;
use aieuo\mineflow\flowItem\action\player\message\SendTip;
use aieuo\mineflow\flowItem\action\player\message\SendTitle;
use aieuo\mineflow\flowItem\action\player\message\SendToast;
use aieuo\mineflow\flowItem\action\player\permission\AddPermission;
use aieuo\mineflow\flowItem\action\player\permission\RemovePermission;
use aieuo\mineflow\flowItem\action\player\PlaySound;
use aieuo\mineflow\flowItem\action\player\SendGameRule;
use aieuo\mineflow\flowItem\action\player\SetFood;
use aieuo\mineflow\flowItem\action\player\SetGamemode;
use aieuo\mineflow\flowItem\action\player\SetSitting;
use aieuo\mineflow\flowItem\action\player\SetSleeping;
use aieuo\mineflow\flowItem\action\player\TransferServer;
use aieuo\mineflow\flowItem\action\scoreboard\CreateScoreboardVariable;
use aieuo\mineflow\flowItem\action\scoreboard\DecrementScoreboardScore;
use aieuo\mineflow\flowItem\action\scoreboard\IncrementScoreboardScore;
use aieuo\mineflow\flowItem\action\scoreboard\RemoveScoreboardScore;
use aieuo\mineflow\flowItem\action\scoreboard\RemoveScoreboardScoreName;
use aieuo\mineflow\flowItem\action\scoreboard\SetScoreboardScore;
use aieuo\mineflow\flowItem\action\scoreboard\SetScoreboardScoreName;
use aieuo\mineflow\flowItem\action\scoreboard\ShowScoreboard;
use aieuo\mineflow\flowItem\action\script\ActionGroup;
use aieuo\mineflow\flowItem\action\script\CallRecipe;
use aieuo\mineflow\flowItem\action\script\ExecuteRecipe;
use aieuo\mineflow\flowItem\action\script\ExecuteRecipeWithEntity;
use aieuo\mineflow\flowItem\action\script\ExitRecipe;
use aieuo\mineflow\flowItem\action\script\ifelse\ElseAction;
use aieuo\mineflow\flowItem\action\script\ifelse\ElseifAction;
use aieuo\mineflow\flowItem\action\script\ifelse\IFAction;
use aieuo\mineflow\flowItem\action\script\ifelse\IFNotAction;
use aieuo\mineflow\flowItem\action\script\loop\ForAction;
use aieuo\mineflow\flowItem\action\script\loop\ForeachAction;
use aieuo\mineflow\flowItem\action\script\loop\ForeachPosition;
use aieuo\mineflow\flowItem\action\script\loop\RepeatAction;
use aieuo\mineflow\flowItem\action\script\loop\WhileTaskAction;
use aieuo\mineflow\flowItem\action\script\SaveData;
use aieuo\mineflow\flowItem\action\script\Wait;
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
use aieuo\mineflow\flowItem\action\variable\DeleteListVariableContentByValue;
use aieuo\mineflow\flowItem\action\variable\DeleteVariable;
use aieuo\mineflow\flowItem\action\variable\GetVariableNested;
use aieuo\mineflow\flowItem\action\variable\JoinListVariableToString;
use aieuo\mineflow\flowItem\action\variable\player\SetDefaultPlayerData;
use aieuo\mineflow\flowItem\action\variable\player\SetPlayerData;
use aieuo\mineflow\flowItem\action\world\AddParticle;
use aieuo\mineflow\flowItem\action\world\CreateAABB;
use aieuo\mineflow\flowItem\action\world\CreateAABBByVector3Variable;
use aieuo\mineflow\flowItem\action\world\CreatePositionVariable;
use aieuo\mineflow\flowItem\action\world\DropItem;
use aieuo\mineflow\flowItem\action\world\GenerateRandomPosition;
use aieuo\mineflow\flowItem\action\world\GetBlock;
use aieuo\mineflow\flowItem\action\world\GetDistance;
use aieuo\mineflow\flowItem\action\world\GetEntitiesInArea;
use aieuo\mineflow\flowItem\action\world\GetEntitySidePosition;
use aieuo\mineflow\flowItem\action\world\GetNearestEntity;
use aieuo\mineflow\flowItem\action\world\GetNearestLiving;
use aieuo\mineflow\flowItem\action\world\GetNearestPlayer;
use aieuo\mineflow\flowItem\action\world\GetPlayersInArea;
use aieuo\mineflow\flowItem\action\world\GetWorldByName;
use aieuo\mineflow\flowItem\action\world\PlaySoundAt;
use aieuo\mineflow\flowItem\action\world\PositionVariableAddition;
use aieuo\mineflow\flowItem\action\world\SetBlock;
use aieuo\mineflow\flowItem\action\world\SetWorldTime;
use aieuo\mineflow\flowItem\condition\block\IsSameBlock;
use aieuo\mineflow\flowItem\condition\common\CheckNothing;
use aieuo\mineflow\flowItem\condition\Condition;
use aieuo\mineflow\flowItem\condition\config\ExistsConfigData;
use aieuo\mineflow\flowItem\condition\config\ExistsConfigFile;
use aieuo\mineflow\flowItem\condition\entity\InArea;
use aieuo\mineflow\flowItem\condition\entity\InWorld;
use aieuo\mineflow\flowItem\condition\entity\IsActiveEntity;
use aieuo\mineflow\flowItem\condition\entity\IsActiveEntityVariable;
use aieuo\mineflow\flowItem\condition\entity\IsCreature;
use aieuo\mineflow\flowItem\condition\entity\IsCreatureVariable;
use aieuo\mineflow\flowItem\condition\entity\IsSneaking;
use aieuo\mineflow\flowItem\condition\item\CanAddItem;
use aieuo\mineflow\flowItem\condition\item\ExistsArmor;
use aieuo\mineflow\flowItem\condition\item\ExistsItem;
use aieuo\mineflow\flowItem\condition\item\HasItemData;
use aieuo\mineflow\flowItem\condition\item\InHand;
use aieuo\mineflow\flowItem\condition\item\IsSameItem;
use aieuo\mineflow\flowItem\condition\item\RemoveItemCondition;
use aieuo\mineflow\flowItem\condition\math\RandomNumber;
use aieuo\mineflow\flowItem\condition\player\Gamemode;
use aieuo\mineflow\flowItem\condition\player\IsFlying;
use aieuo\mineflow\flowItem\condition\player\IsGliding;
use aieuo\mineflow\flowItem\condition\player\IsOp;
use aieuo\mineflow\flowItem\condition\player\IsPlayer;
use aieuo\mineflow\flowItem\condition\player\IsPlayerOnline;
use aieuo\mineflow\flowItem\condition\player\IsPlayerOnlineByName;
use aieuo\mineflow\flowItem\condition\player\IsPlayerVariable;
use aieuo\mineflow\flowItem\condition\player\IsSprinting;
use aieuo\mineflow\flowItem\condition\player\IsSwimming;
use aieuo\mineflow\flowItem\condition\player\OnlinePlayerLessThan;
use aieuo\mineflow\flowItem\condition\player\OnlinePlayerMoreThan;
use aieuo\mineflow\flowItem\condition\player\permission\HasPermission;
use aieuo\mineflow\flowItem\condition\script\AndScript;
use aieuo\mineflow\flowItem\condition\script\ComparisonNumber;
use aieuo\mineflow\flowItem\condition\script\ComparisonString;
use aieuo\mineflow\flowItem\condition\script\NandScript;
use aieuo\mineflow\flowItem\condition\script\NorScript;
use aieuo\mineflow\flowItem\condition\script\NotScript;
use aieuo\mineflow\flowItem\condition\script\ORScript;
use aieuo\mineflow\flowItem\condition\variable\ExistsListVariableKey;
use aieuo\mineflow\flowItem\condition\variable\ExistsVariable;
use function in_array;

class FlowItemFactory {

    /** @var FlowItem[] */
    private static array $list = [];
    /** @var FlowItem[] */
    private static array $aliases = [];

    public static function init(): void {
        /** actions **/
        /* common */
        self::register(new DoNothing);
        self::register(new EventCancel);
        self::register(new CallCustomTrigger);
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
        self::register(new SendToast);
        self::register(new SendJukeboxPopup);
        self::register(new SendActionBarMessage);
        self::register(new Chat);
        /* entity */
        self::register(new SetNameTag);
        self::register(new GetEntity);
        self::register(new Teleport);
        self::register(new TeleportToWorld);
        self::register(new Motion);
        self::register(new MoveTo);
        self::register(new SetYaw);
        self::register(new SetPitch);
        self::register(new LookAt);
        self::register(new AddDamage);
        self::register(new SetImmobile);
        self::register(new UnsetImmobile);
        self::register(new SetInvisible);
        self::register(new SetHealth);
        self::register(new SetMaxHealth);
        self::register(new SetScale);
        self::register(new AddEffect);
        self::register(new ClearEffect);
        self::register(new ClearAllEffect);
        self::register(new CreateHumanEntity);
        /* player */
        self::register(new GetPlayerByName);
        self::register(new SetSleeping);
        self::register(new SetSitting);
        self::register(new Kick);
        self::register(new TransferServer);
        self::register(new SetFood);
        self::register(new SetGamemode);
        self::register(new ShowBossbar);
        self::register(new RemoveBossbar);
        self::register(new ShowScoreboard);
        self::register(new HideScoreboard);
        self::register(new PlaySound);
        self::register(new Emote);
        self::register(new SendGameRule);
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
        self::register(new SetItemData);
        self::register(new SetItemDataFromNBTJson);
        self::register(new GetItemData);
        self::register(new RemoveItemData);
        self::register(new ClearInventory);
        self::register(new SetArmorColor);
        self::register(new GetInventoryContents);
        self::register(new GetArmorInventoryContents);
        /* script */
        self::register(new IFAction);
        self::register(new ElseifAction);
        self::register(new ElseAction);
        self::register(new IFNotAction);
        self::register(new RepeatAction);
        self::register(new ForAction);
        self::register(new ForeachAction);
        self::register(new ForeachPosition);
        self::register(new WhileTaskAction);
        self::register(new Wait);
        self::register(new CallRecipe);
        self::register(new ExecuteRecipe);
        self::register(new ExecuteRecipeWithEntity);
        self::register(new ActionGroup);
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
        self::register(new SetPlayerData);
        self::register(new SetDefaultPlayerData);
        self::register(new CreateMapVariableFromJson);
        self::register(new DeleteListVariableContent);
        self::register(new DeleteListVariableContentByValue);
        self::register(new CreatePositionVariable);
        self::register(new GetVariableNested);
        self::register(new CountListVariable);
        self::register(new JoinListVariableToString);
        /* form */
        self::register(new SendForm);
        self::register(new SendInputForm);
        self::register(new SendMenuForm);
        self::register(new SendConfirmForm);
        self::register(new SendSlider);
        self::register(new SendDropdown);
        self::register(new SendStepSlider);
        self::register(new CreateListFormVariable);
        self::register(new AddButtonToListForm);
        self::register(new ShowFormVariable);
        /* command */
        self::register(new Command);
        self::register(new CommandConsole);
        /* block */
        self::register(new CreateBlockVariable);
        /* world */
        self::register(new SetBlock);
        self::register(new GetBlock);
        self::register(new AddParticle);
        self::register(new PlaySoundAt);
        self::register(new DropItem);
        self::register(new GetDistance);
        self::register(new GetEntitySidePosition);
        self::register(new GenerateRandomPosition);
        self::register(new PositionVariableAddition);
        self::register(new GetWorldByName);
        self::register(new SetWorldTime);
        self::register(new GetNearestEntity);
        self::register(new GetNearestLiving);
        self::register(new GetNearestPlayer);
        self::register(new CreateAABB);
        self::register(new CreateAABBByVector3Variable);
        self::register(new GetEntitiesInArea);
        self::register(new GetPlayersInArea);
        /* scoreboard */
        self::register(new CreateScoreboardVariable);
        self::register(new SetScoreboardScore);
        self::register(new SetScoreboardScoreName);
        self::register(new IncrementScoreboardScore);
        self::register(new DecrementScoreboardScore);
        self::register(new RemoveScoreboardScore);
        self::register(new RemoveScoreboardScoreName);
        /* internal */
        self::register(new AddLanguageMappings);
        self::register(new AddSpecificLanguageMapping);
        self::register(new GetLanguage);
        /* event */
        self::register(new PlayerChatEventSetMessage);


        /** conditions **/
        /* common */
        self::register(new CheckNothing);
        self::register(new IsOp);
        self::register(new IsSneaking);
        self::register(new IsFlying);
        self::register(new IsGliding);
        self::register(new IsSwimming);
        self::register(new IsSprinting);
        self::register(new RandomNumber);
        /* block */
        self::register(new IsSameBlock);
        /* item */
        self::register(new IsSameItem);
        self::register(new InHand);
        self::register(new ExistsItem);
        self::register(new CanAddItem);
        self::register(new RemoveItemCondition);
        self::register(new ExistsArmor);
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
        self::register(new InWorld);
        /* player */
        self::register(new Gamemode);
        self::register(new HasPermission);
        self::register(new IsPlayerOnline);
        self::register(new IsPlayerOnlineByName);
        self::register(new OnlinePlayerLessThan);
        self::register(new OnlinePlayerMoreThan);
        /* variable */
        self::register(new ExistsVariable);
        self::register(new ExistsListVariableKey);
        /* item */
        self::register(new HasItemData);


        self::registerAliases();
    }

    public static function registerAliases(): void {
        self::registerAlias(new FourArithmeticOperationsAdd());
        self::registerAlias(new FourArithmeticOperationsSub());
        self::registerAlias(new FourArithmeticOperationsMul());
        self::registerAlias(new FourArithmeticOperationsDiv());
        self::registerAlias(new CalculateSin());
        self::registerAlias(new CalculateCos());
        self::registerAlias(new CalculateTan());
        self::registerAlias(new CalculateFloor());
        self::registerAlias(new CalculateCeil());
        self::registerAlias(new Calculate2Round());
    }

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
     * @param array $permissions
     * @param bool $getAction
     * @param bool $getCondition
     * @return FlowItem[]
     */
    public static function getByFilter(string $category = null, array $permissions = [], bool $getAction = true, bool $getCondition = true): array {
        $items = [];
        foreach (self::$list as $item) {
            if ($category !== null and $item->getCategory() !== $category) continue;
            foreach ($item->getPermissions() as $permission) {
                if (!in_array($permission, $permissions, true)) continue 2;
            }
            if (!$getAction and !($item instanceof Condition)) continue;
            if (!$getCondition and ($item instanceof Condition)) continue;

            $items[] = $item;
        }
        return $items;
    }

    public static function getAll(): array {
        return self::$list;
    }

    public static function getAllAliases(): array {
        return self::$aliases;
    }

    public static function register(FlowItem $action, bool $override = false): void {
        if (!$override and isset(self::$list[$action->getId()])) {
            throw new \InvalidArgumentException("FlowItem id ".$action->getId()." is already used by ".self::$list[$action->getId()]::class);
        }

        self::$list[$action->getId()] = clone $action;
    }

    public static function registerAlias(FlowItem $action): void {
        self::$aliases[$action->getId()] = clone $action;
    }
}