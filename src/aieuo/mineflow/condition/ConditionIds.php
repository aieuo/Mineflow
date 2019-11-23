<?php

namespace aieuo\mineflow\condition;

interface ConditionIds {

    const CHECK_NOTHING = "checkNothing";
    const IS_OP = "isOp";
    const IS_SNEAKING = "isSneaking";
    const IS_FLYING = "isFlying";

    const OVER_MONEY = "overMoney";
    const LESS_MONEY = "lessMoney";
    const TAKE_MONEY = "takeMoney";

    const CAN_ADD_ITEM = "canAddItem";
    const EXISTS_ITEM = "existsItem";
    const IN_HAND = "isHandItem";

    const EXISTS_LIST_VARIABLE_KEY = "existsListVariableKey";
}