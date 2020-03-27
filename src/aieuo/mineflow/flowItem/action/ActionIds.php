<?php

namespace aieuo\mineflow\flowItem\action;

interface ActionIds {

    const DO_NOTHING = "doNothing";
    const EVENT_CANCEL = "eventCancel";
    const SEND_FORM = "sendForm";

    const SEND_MESSAGE = "sendMessage";
    const SEND_TIP = "sendTip";
    const SEND_POPUP = "sendPopup";
    const BROADCAST_MESSAGE = "broadcastMessage";
    const SEND_MESSAGE_TO_OP= "sendMessageToOp";
    const SEND_TITLE = "sendTitle";

    const GET_ENTITY = "getEntity";
    const TELEPORT = "teleport";
    const MOTION = "motion";
    const SET_YAW = "setYaw";
    const ADD_DAMAGE = "addDamage";
    const SET_IMMOBILE = "setImmobile";
    const UNSET_IMMOBILE = "unsetImmobile";
    const SET_HEALTH = "setHealth";
    const SET_MAX_HEALTH = "setMaxHealth";
    const SET_FOOD = "setFood";

    const SET_SLEEPING = "setSleeping";
    const SET_SITTING = "setSitting";
    const KICK = "kick";
    const CLEAR_INVENTORY = "clearInventory";

    const ADD_MONEY = "addMoney";
    const SET_MONEY = "setMoney";
    const TAKE_MONEY = "takeMoney";
    const GET_MONEY = "getMoney";

    const ADD_ITEM = "addItem";
    const REMOVE_ITEM = "removeItem";
    const REMOVE_ITEM_ALL = "removeItemAll";
    const SET_ITEM = "setItem";
    const SET_ITEM_IN_HAND = "setItemInHand";

    const EXECUTE_RECIPE = "executeRecipe";
    const EXECUTE_RECIPE_WITH_ENTITY = "executeRecipeWithEntity";
    const CALL_RECIPE = "callRecipe";

    const FOUR_ARITHMETIC_OPERATIONS = "fourArithmeticOperations";
    const CALCULATE = "calculate";
    const GET_PI = "getPi";
    const GET_E = "getE";
    const GENERATE_RANDOM_NUMBER = "random";

    const ADD_VARIABLE = "addVariable";
    const DELETE_VARIABLE = "deleteVariable";
    const ADD_LIST_VARIABLE = "addListVariable";
    const ADD_MAP_VARIABLE = "addMapVariable";
    const CREATE_ITEM_VARIABLE = "createItem";
    const CREATE_POSITION_VARIABLE = "createPosition";

    const ACTION_IF = "if";
    const ACTION_ELSEIF = "elseif";
    const ACTION_ELSE = "else";
    const ACTION_REPEAT = "repeat";
    const ACTION_WHILE = "while";
    const ACTION_WAIT = "wait";
    const SAVE_DATA = "save";
}