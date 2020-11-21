<?php

namespace aieuo\mineflow\trigger;

use aieuo\mineflow\trigger\block\BlockTrigger;
use aieuo\mineflow\trigger\command\CommandTrigger;
use aieuo\mineflow\trigger\event\EventTrigger;
use aieuo\mineflow\trigger\form\FormTrigger;
use aieuo\mineflow\ui\trigger\BlockTriggerForm;
use aieuo\mineflow\ui\trigger\CommandTriggerForm;
use aieuo\mineflow\ui\trigger\EventTriggerForm;
use aieuo\mineflow\ui\trigger\FormTriggerForm;
use aieuo\mineflow\ui\trigger\TriggerForm;

class Triggers {

    public const BLOCK = "block";
    public const COMMAND = "command";
    public const EVENT = "event";
    public const FORM = "form";

    /** @var TriggerForm[] */
    private static $forms = [];
    /** @var Trigger[] */
    private static $list = [];

    public static function init(): void {
        self::add(self::BLOCK, BlockTrigger::create(""), new BlockTriggerForm());
        self::add(self::COMMAND, CommandTrigger::create(""), new CommandTriggerForm());
        self::add(self::EVENT, EventTrigger::create(""), new EventTriggerForm());
        self::add(self::FORM, FormTrigger::create(""), new FormTriggerForm());
    }

    public static function add(string $type, Trigger $trigger, TriggerForm $form): void {
        self::$list[$type] = $trigger;
        self::$forms[$type] = $form;
    }

    public static function getTrigger(string $type, string $key = "", string $subKey = ""): ?Trigger {
        $trigger = self::$list[$type] ?? null;
        if ($trigger === null) return null;

        $trigger = clone $trigger;
        $trigger->setKey($key);
        $trigger->setSubKey($subKey);
        return $trigger;
    }

    /**
     * @return TriggerForm[]
     */
    public static function getAllForm(): array {
        return self::$forms;
    }

    public static function getForm(string $type): ?TriggerForm {
        $form = self::$forms[$type] ?? null;
        if ($form === null) return null;
        return clone $form;
    }

    public static function existsForm(string $type): bool {
        return isset(self::$forms[$type]);
    }

}