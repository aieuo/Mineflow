<?php

namespace aieuo\mineflow\flowItem\condition;

interface ConditionIds {

    const CHECK_NOTHING = "checkNothing";
    const IS_OP = "isOp";
    const IS_SNEAKING = "isSneaking";
    const IS_FLYING = "isFlying";
    const IN_AREA = "inArea";
    const GAMEMODE = "gamemode";
    const HAS_PERMISSION = "hasPermission";

    const OVER_MONEY = "overMoney";
    const LESS_MONEY = "lessMoney";
    const TAKE_MONEY = "takeMoney";

    const CAN_ADD_ITEM = "canAddItem";
    const EXISTS_ITEM = "existsItem";
    const IN_HAND = "isHandItem";
    const REMOVE_ITEM = "removeItem";

    const COMPARISON_NUMBER = "comparisonNumber";

    const IS_ACTIVE_ENTITY = "isActiveEntity";
    const IS_PLAYER = "isPlayer";
    const IS_CREATURE = "isCreature";

    const EXISTS_VARIABLE = "existsVariable";
    const EXISTS_LIST_VARIABLE_KEY = "existsListVariableKey";

    const CONDITION_AND = "and";
    const CONDITION_OR = "or";
    const CONDITION_NAND = "nand";
    const CONDITION_NOR = "nor";
    const CONDITION_NOT = "not";

    const RANDOM_NUMBER = "randomNumber";
}