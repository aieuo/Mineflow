<?php

namespace aieuo\mineflow\flowItem;

interface FlowItemIds {

    const DO_NOTHING = "doNothing";
    const GET_DATE = "getDate";
    const EVENT_CANCEL = "eventCancel";
    const SEND_FORM = "sendForm";

    const SEND_MESSAGE = "sendMessage";
    const SEND_TIP = "sendTip";
    const SEND_POPUP = "sendPopup";
    const BROADCAST_MESSAGE = "broadcastMessage";
    const SEND_MESSAGE_TO_OP = "sendMessageToOp";
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
    const ADD_XP_PROGRESS = "addXp";
    const ADD_XP_LEVEL = "addXpLevel";
    const GET_TARGET_BLOCK = "getTargetBlock";
    const CREATE_HUMAN_ENTITY = "createHuman";

    const GET_PLAYER = "getPlayer";
    const SET_SLEEPING = "setSleeping";
    const SET_SITTING = "setSitting";
    const KICK = "kick";
    const CLEAR_INVENTORY = "clearInventory";
    const SET_FOOD = "setFood";
    const SET_GAMEMODE = "setGamemode";
    const SHOW_BOSSBAR = "showBossbar";
    const REMOVE_BOSSBAR = "removeBossbar";
    const SHOW_SCOREBOARD = "showScoreboard";
    const HIDE_SCOREBOARD = "hideScoreboard";
    const PLAY_SOUND = "playSound";
    const PLAY_SOUND_AT = "playSoundAt";
    const ADD_PERMISSION = "addPermission";
    const REMOVE_PERMISSION = "removePermission";
    const ALLOW_FLIGHT = "allowFlight";
    const ALLOW_CLIMB_WALLS = "allowClimbWalls";

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
    const SET_ITEM_DAMAGE = "setItemDamage";
    const SET_ITEM_COUNT = "setItemCount";
    const SET_ITEM_NAME = "setItemName";
    const EQUIP_ARMOR = "equipArmor";
    const GET_INVENTORY_CONTENTS = "getInventory";

    const COMMAND = "command";
    const COMMAND_CONSOLE = "commandConsole";

    const SET_BLOCK = "setBlock";
    const GET_BLOCK = "getBlock";
    const ADD_PARTICLE = "addParticle";
    const SET_WEATHER = "setWeather";
    const GET_DISTANCE = "getDistance";

    const EXECUTE_RECIPE = "executeRecipe";
    const EXECUTE_IF_CHAIN = "executeIFChain";
    const EXECUTE_RECIPE_WITH_ENTITY = "executeRecipeWithEntity";
    const CALL_RECIPE = "callRecipe";

    const FOUR_ARITHMETIC_OPERATIONS = "fourArithmeticOperations";
    const FOUR_ARITHMETIC_OPERATIONS_ADD = "add";
    const FOUR_ARITHMETIC_OPERATIONS_SUB = "sub";
    const FOUR_ARITHMETIC_OPERATIONS_MUL = "mul";
    const FOUR_ARITHMETIC_OPERATIONS_DIV = "div";
    const CALCULATE = "calculate";
    const CALCULATE_SIN = "sin";
    const CALCULATE_COS = "cos";
    const CALCULATE_TAN = "tan";
    const CALCULATE2 = "calculate2";
    const GET_PI = "getPi";
    const GET_E = "getE";
    const GENERATE_RANDOM_NUMBER = "random";
    const REVERSE_POLISH_NOTATION = "calculateRPN";

    const EDIT_STRING = "editString";
    const STRING_LENGTH = "strlen";

    const ADD_VARIABLE = "addVariable";
    const DELETE_VARIABLE = "deleteVariable";
    const ADD_LIST_VARIABLE = "addListVariable";
    const JOIN_LIST_VARIABLE_TO_STRING = "joinToString";
    const CREATE_LIST_VARIABLE = "createList";
    const ADD_MAP_VARIABLE = "addMapVariable";
    const CREATE_MAP_VARIABLE = "createMap";
    const CREATE_MAP_VARIABLE_FROM_JSON = "createMapFromJson";
    const CREATE_ITEM_VARIABLE = "createItem";
    const CREATE_POSITION_VARIABLE = "createPosition";
    const CREATE_BLOCK_VARIABLE = "createBlock";
    const GET_VARIABLE_NESTED = "getVariable";
    const COUNT_LIST_VARIABLE = "count";
    const DELETE_LIST_VARIABLE_CONTENT = "removeContent";
    const CREATE_SCOREBOARD_VARIABLE = "createScoreboard";

    const ACTION_IF = "if";
    const ACTION_ELSEIF = "elseif";
    const ACTION_ELSE = "else";
    const ACTION_REPEAT = "repeat";
    const ACTION_WHILE = "while";
    const ACTION_WAIT = "wait";
    const ACTION_FOR = "for";
    const ACTION_FOREACH = "foreach";
    const SAVE_DATA = "save";
    const ACTION_WHILE_TASK = "whileTask";
    const CREATE_CONFIG_VARIABLE = "createConfig";
    const SET_CONFIG_VALUE = "setConfig";
    const SAVE_CONFIG_FILE = "saveConfig";
    const REMOVE_CONFIG_VALUE = "removeConfig";
    const EXIT_RECIPE = "exit";
    const EXIT_CONTAINER = "break";

    const SEND_INPUT = "input";
    const SEND_MENU = "select";
    const SEND_SLIDER = "slider";
    const SEND_DROPDOWN = "dropdown";

    const REPLENISH_RESOURCE = "replenishResource";

    const SET_SCOREBOARD_SCORE = "setScore";
    const SET_SCOREBOARD_SCORE_NAME = "setScoreName";
    const REMOVE_SCOREBOARD_SCORE = "removeScore";
    const REMOVE_SCOREBOARD_SCORE_NAME = "removeScoreName";
    const INCREMENT_SCOREBOARD_SCORE = "incrementScore";
    const DECREMENT_SCOREBOARD_SCORE = "decrementScore";


    /* condition */
    const CHECK_NOTHING = "checkNothing";
    const IS_OP = "isOp";
    const IS_SNEAKING = "isSneaking";
    const IS_FLYING = "isFlying";
    const IN_AREA = "inArea";
    const GAMEMODE = "gamemode";
    const HAS_PERMISSION = "hasPermission";

    const OVER_MONEY = "overMoney";
    const LESS_MONEY = "lessMoney";
    const TAKE_MONEY_CONDITION = "takeMoneyCondition";

    const CAN_ADD_ITEM = "canAddItem";
    const EXISTS_ITEM = "existsItem";
    const IN_HAND = "isHandItem";
    const REMOVE_ITEM_CONDITION = "removeItemCondition";

    const COMPARISON_NUMBER = "comparisonNumber";
    const COMPARISON_STRING = "comparisonString";

    const IS_ACTIVE_ENTITY = "isActiveEntity";
    const IS_PLAYER = "isPlayer";
    const IS_PLAYER_ONLINE = "isPlayerOnline";
    const IS_PLAYER_ONLINE_BY_NAME = "isPlayerOnlineByName";
    const IS_CREATURE = "isCreature";

    const EXISTS_VARIABLE = "existsVariable";
    const EXISTS_LIST_VARIABLE_KEY = "existsListVariableKey";

    const CONDITION_AND = "and";
    const CONDITION_OR = "or";
    const CONDITION_NAND = "nand";
    const CONDITION_NOR = "nor";
    const CONDITION_NOT = "not";
    const EXISTS_CONFIG_FILE = "existsConfigFile";
    const EXISTS_CONFIG_DATA = "existsConfig";

    const RANDOM_NUMBER = "randomNumber";
}