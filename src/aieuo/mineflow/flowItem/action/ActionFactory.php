<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\economy\Economy;

class ActionFactory {
    private static $list = [];

    public static function init(): void {
        self::register(new DoNothing);
        self::register(new EventCancel);
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
        /* player */
        self::register(new SetSleeping);
        self::register(new SetSitting);
        self::register(new Kick);
        self::register(new SetFood);
        self::register(new SetGamemode);
        self::register(new ShowBossbar);
        self::register(new RemoveBossbar);
        self::register(new PlaySound);
        self::register(new PlaySoundAt);
        /* item */
        self::register(new CreateItemVariable);
        self::register(new AddItem);
        self::register(new SetItemInHand);
        self::register(new RemoveItem);
        self::register(new RemoveItemAll);
        self::register(new AddEnchantment);
        self::register(new EquipArmor);
        self::register(new SetItem);
        self::register(new ClearInventory);
        /* money */
        if (Economy::isPluginLoaded()) {
            self::register(new AddMoney);
            self::register(new TakeMoney);
            self::register(new SetMoney);
            self::register(new GetMoney);
        }
        /* script */
        self::register(new IFAction);
        self::register(new ElseifAction);
        self::register(new ElseAction);
        self::register(new RepeatAction);
        self::register(new WhileAction);
        self::register(new Wait);
        self::register(new CallRecipe);
        self::register(new ExecuteRecipe);
        self::register(new ExecuteRecipeWithEntity);
        self::register(new SaveData);
        /* calculation */
        self::register(new FourArithmeticOperations);
        self::register(new Calculate);
        self::register(new GetPi);
        self::register(new GetE);
        self::register(new GenerateRandomNumber);
        /* variable */
        self::register(new AddVariable);
        self::register(new DeleteVariable);
        self::register(new AddListVariable);
        self::register(new AddMapVariable);
        self::register(new CreatePositionVariable);
        /* form */
        self::register(new SendForm);
        /* command */
        self::register(new Command);
        self::register(new CommandConsole);
        /* block */
        self::register(new CreateBlockVariable);
        self::register(new SetBlock);
        /* level */
        self::register(new AddParticle);
    }

    /**
     * @param  string $id
     * @return Action|null
     */
    public static function get(string $id): ?Action {
        if (isset(self::$list[$id])) {
            return clone self::$list[$id];
        }
        return null;
    }

    /**
     * @param int $category
     * @return Action[]
     */
    public static function getByCategory(int $category): array {
        $processes = [];
        foreach (self::$list as $process) {
            if ($process->getCategory() === $category) $processes[] = $process;
        }
        return $processes;
    }

    /**
     * @return array
     */
    public static function getAll(): array {
        return self::$list;
    }

    /**
     * @param  Action $process
     */
    public static function register(Action $process): void {
        self::$list[$process->getId()] = clone $process;
    }
}