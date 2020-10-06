<?php

namespace aieuo\mineflow\trigger;

use aieuo\mineflow\ui\BlockTriggerForm;
use aieuo\mineflow\ui\CommandTriggerForm;
use aieuo\mineflow\ui\EventTriggerForm;
use aieuo\mineflow\ui\FormTriggerForm;
use aieuo\mineflow\ui\TriggerForm;

class TriggerTypes {

    public const BLOCK = "block";
    public const COMMAND = "command";
    public const EVENT = "event";
    public const FORM = "form";

    /** @var array<string, TriggerForm> */
    private static $list = [];

    public static function init(): void {
        self::add(self::BLOCK, new BlockTriggerForm());
        self::add(self::COMMAND, new CommandTriggerForm());
        self::add(self::EVENT, new EventTriggerForm());
        self::add(self::FORM, new FormTriggerForm());
    }

    public static function add(string $type, TriggerForm $form): void {
        self::$list[$type] = $form;
    }

    /**
     * @return TriggerForm[]
     */
    public static function getAll(): array {
        return self::$list;
    }

    public static function getForm(string $type): ?TriggerForm {
        $form = self::$list[$type] ?? null;
        if ($form === null) return null;
        return clone $form;
    }

    public static function exists(string $type): bool {
        return isset(self::$list[$type]);
    }

}