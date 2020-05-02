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
    const SET_PITCH = "setPitch";
    const ADD_DAMAGE = "addDamage";
    const SET_IMMOBILE = "setImmobile";
    const UNSET_IMMOBILE = "unsetImmobile";
    const SET_HEALTH = "setHealth";
    const SET_MAX_HEALTH = "setMaxHealth";
    const SET_NAME = "setName";
    const SET_SCALE = "setScale";
    const ADD_EFFECT = "addEffect";

    const GET_PLAYER = "getPlayer";
    const SET_SLEEPING = "setSleeping";
    const SET_SITTING = "setSitting";
    const KICK = "kick";
    const CLEAR_INVENTORY = "clearInventory";
    const SET_FOOD = "setFood";
    const SET_GAMEMODE = "setGamemode";
    const SHOW_BOSSBAR = "showBossbar";
    const REMOVE_BOSSBAR = "removeBossbar";
    const PLAY_SOUND = "playSound";
    const PLAY_SOUND_AT = "playSoundAt";
    const ADD_PERMISSION = "addPermission";
    const REMOVE_PERMISSION = "removePermission";

    const ADD_MONEY = "addMoney";
    const SET_MONEY = "setMoney";
    const TAKE_MONEY = "takeMoney";
    const GET_MONEY = "getMoney";

    const ADD_ITEM = "addItem";
    const REMOVE_ITEM = "removeItem";
    const REMOVE_ITEM_ALL = "removeItemAll";
    const SET_ITEM = "setItem";
    const SET_ITEM_IN_HAND = "setItemInHand";
    const ADD_ENCHANTMENT = "addEnchant";
    const SET_ITEM_LORE = "setLore";
    const EQUIP_ARMOR = "equipArmor";
    const GET_INVENTORY_CONTENTS = "getInventory";

    const COMMAND = "command";
    const COMMAND_CONSOLE = "commandConsole";

    const SET_BLOCK = "setBlock";
    const ADD_PARTICLE = "addParticle";

    const EXECUTE_RECIPE = "executeRecipe";
    const EXECUTE_RECIPE_WITH_ENTITY = "executeRecipeWithEntity";
    const CALL_RECIPE = "callRecipe";

    const FOUR_ARITHMETIC_OPERATIONS = "fourArithmeticOperations";
    const CALCULATE = "calculate";
    const EDIT_STRING = "editString";
    const GET_PI = "getPi";
    const GET_E = "getE";
    const GENERATE_RANDOM_NUMBER = "random";

    const ADD_VARIABLE = "addVariable";
    const DELETE_VARIABLE = "deleteVariable";
    const ADD_LIST_VARIABLE = "addListVariable";
    const CREATE_LIST_VARIABLE = "createList";
    const ADD_MAP_VARIABLE = "addMapVariable";
    const CREATE_MAP_VARIABLE = "createMap";
    const CREATE_ITEM_VARIABLE = "createItem";
    const CREATE_POSITION_VARIABLE = "createPosition";
    const CREATE_BLOCK_VARIABLE = "createBlock";
    const GET_VARIABLE_NESTED = "getVariable";
    const COUNT_LIST_VARIABLE = "count";

    const ACTION_IF = "if";
    const ACTION_ELSEIF = "elseif";
    const ACTION_ELSE = "else";
    const ACTION_REPEAT = "repeat";
    const ACTION_WHILE = "while";
    const ACTION_WAIT = "wait";
    const SAVE_DATA = "save";
    const ACTION_WHILE_TASK = "whileTask";
    const CREATE_CONFIG_VARIABLE = "createConfig";
    const SET_CONFIG_DATA = "setConfig";
    const SAVE_CONFIG_FILE = "saveConfig";
    const EXIT_RECIPE = "exit";
    const EXIT_CONTAINER = "break";

    const SEND_INPUT = "input";
    const SEND_MENU = "select";
    const SEND_SLIDER = "slider";
}