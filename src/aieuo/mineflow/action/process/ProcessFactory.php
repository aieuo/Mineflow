<?php

namespace aieuo\mineflow\action\process;

class ProcessFactory {
    private static $list = [];

    public static function init(): void {
        self::register(new DoNothing);
        /* message */
        self::register(new SendMessage);
        self::register(new SendTip);
        self::register(new SendPopup);
        self::register(new SendBroadcastMessage);
        self::register(new SendMessageToOp);
        self::register(new SendTitle);
        /* entity */
        self::register(new GetEntity);
        /* money */
        self::register(new AddMoney);
        self::register(new TakeMoney);
        self::register(new SetMoney);
        self::register(new GetMoney);
    }

    /**
     * @param  string $id
     * @return Process|null
     */
    public static function get(string $id): ?Process {
        if (isset(self::$list[$id])) {
            return clone self::$list[$id];
        }
        return null;
    }

    /**
     * @return Process[]
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
     * @param  Process $process
     */
    public static function register(Process $process): void {
        self::$list[$process->getId()] = clone $process;
    }
}