<?php
declare(strict_types=1);

namespace aieuo\mineflow\flowItem;

interface FlowItemIds {

    public const DO_NOTHING = "doNothing";
    public const GET_DATE = "getDate";
    public const EVENT_CANCEL = "eventCancel";
    public const PLAYER_CHAT_EVENT_SET_MESSAGE = "playerChatEventSetMessage";
    public const CALL_CUSTOM_TRIGGER = "callTrigger";
    public const SEND_FORM = "sendForm";

    public const SEND_MESSAGE = "sendMessage";
    public const SEND_TIP = "sendTip";
    public const SEND_POPUP = "sendPopup";
    public const SEND_JUKEBOX_POPUP = "sendJukeboxPopup";
    public const SEND_ACTION_BAR_MESSAGE = "sendActionBarMessage";
    public const BROADCAST_MESSAGE = "broadcastMessage";
    public const SEND_MESSAGE_TO_OP = "sendMessageToOp";
    public const SEND_MESSAGE_TO_CONSOLE = "sendMessageToConsole";
    public const SEND_TITLE = "sendTitle";
    public const SEND_TOAST = "sendToast";
    public const CHAT = "chat";

    public const GET_ENTITY = "getEntity";
    public const TELEPORT = "teleport";
    public const MOTION = "motion";
    public const SET_YAW = "setYaw";
    public const SET_PITCH = "setPitch";
    public const ADD_DAMAGE = "addDamage";
    public const SET_IMMOBILE = "setImmobile";
    public const UNSET_IMMOBILE = "unsetImmobile";
    public const SET_INVISIBLE = "setInvisible";
    public const SET_HEALTH = "setHealth";
    public const SET_MAX_HEALTH = "setMaxHealth";
    public const SET_NAME = "setName";
    public const SET_SCALE = "setScale";
    public const ADD_EFFECT = "addEffect";
    public const CLEAR_EFFECT = "clearEffect";
    public const CLEAR_ALL_EFFECT = "clearAllEffect";
    public const ADD_XP_PROGRESS = "addXp";
    public const ADD_XP_LEVEL = "addXpLevel";
    public const GET_TARGET_BLOCK = "getTargetBlock";
    public const CREATE_HUMAN_ENTITY = "createHuman";
    public const GET_ENTITY_SIDE = "getEntitySide";
    public const LOOK_AT = "lookAt";
    public const MOVE_TO = "moveTo";
    public const TELEPORT_TO_WORLD = "teleportToWorld";
    public const GET_NEAREST_ENTITY = "getNearestEntity";
    public const GET_NEAREST_LIVING = "getNearestLiving";
    public const GET_NEAREST_PLAYER = "getNearestPlayer";

    public const GET_PLAYER = "getPlayer";
    public const SET_SLEEPING = "setSleeping";
    public const SET_SITTING = "setSitting";
    public const KICK = "kick";
    public const TRANSFER_SERVER = "transfer";
    public const CLEAR_INVENTORY = "clearInventory";
    public const SET_FOOD = "setFood";
    public const SET_GAMEMODE = "setGamemode";
    public const SHOW_BOSSBAR = "showBossbar";
    public const REMOVE_BOSSBAR = "removeBossbar";
    public const SHOW_SCOREBOARD = "showScoreboard";
    public const HIDE_SCOREBOARD = "hideScoreboard";
    public const PLAY_SOUND = "playSound";
    public const PLAY_SOUND_AT = "playSoundAt";
    public const ADD_PERMISSION = "addPermission";
    public const REMOVE_PERMISSION = "removePermission";
    public const ALLOW_FLIGHT = "allowFlight";
    public const ALLOW_CLIMB_WALLS = "allowClimbWalls";
    public const EMOTE = "emote";
    public const SEND_BOOL_GAMERULE = "sendBoolGamerule";

    public const ADD_MONEY = "addMoney";
    public const SET_MONEY = "setMoney";
    public const TAKE_MONEY = "takeMoney";
    public const GET_MONEY = "getMoney";

    public const ADD_ITEM = "addItem";
    public const REMOVE_ITEM = "removeItem";
    public const REMOVE_ITEM_ALL = "removeItemAll";
    public const SET_ITEM = "setItem";
    public const SET_ITEM_IN_HAND = "setItemInHand";
    public const ADD_ENCHANTMENT = "addEnchant";
    public const SET_ITEM_LORE = "setLore";
    public const SET_ITEM_DAMAGE = "setItemDamage";
    public const SET_ITEM_COUNT = "setItemCount";
    public const SET_ITEM_NAME = "setItemName";
    public const SET_ITEM_DATA = "setItemData";
    public const SET_ITEM_DATA_FROM_NBT_JSON = "setItemDataFromNBTJson";
    public const GET_ITEM_DATA = "getItemData";
    public const REMOVE_ITEM_DATA = "removeItemData";
    public const HAS_ITEM_DATA = "hasItemData";
    public const SET_ARMOR_COLOR = "setArmorColor";

    public const EQUIP_ARMOR = "equipArmor";
    public const GET_INVENTORY_CONTENTS = "getInventory";
    public const GET_ARMOR_INVENTORY_CONTENTS = "getArmorInventory";

    public const COMMAND = "command";
    public const COMMAND_CONSOLE = "commandConsole";

    public const SET_BLOCK = "setBlock";
    public const GET_BLOCK = "getBlock";
    public const ADD_PARTICLE = "addParticle";
    public const SET_WEATHER = "setWeather";
    public const GET_DISTANCE = "getDistance";
    public const DROP_ITEM = "dropItem";
    public const GENERATE_RANDOM_POSITION = "randomPosition";
    public const POSITION_VARIABLE_ADDITION = "positionAddition";
    public const CREATE_AABB = "createAABB";
    public const CREATE_AABB_BY_VECTOR3_VARIABLE = "createAABBByVector3Variable";
    public const GET_PLAYERS_IN_AREA = "getPlayersInArea";
    public const GET_ENTITIES_IN_AREA = "getEntitiesInArea";
    public const GET_WORLD_BY_NAME = "getWorldByName";
    public const SET_WORLD_TIME = "setWorldTime";

    public const EXECUTE_RECIPE = "executeRecipe";
    public const EXECUTE_RECIPE_WITH_ENTITY = "executeRecipeWithEntity";
    public const CALL_RECIPE = "callRecipe";

    public const FOUR_ARITHMETIC_OPERATIONS = "fourArithmeticOperations";
    public const FOUR_ARITHMETIC_OPERATIONS_ADD = "add";
    public const FOUR_ARITHMETIC_OPERATIONS_SUB = "sub";
    public const FOUR_ARITHMETIC_OPERATIONS_MUL = "mul";
    public const FOUR_ARITHMETIC_OPERATIONS_DIV = "div";
    public const CALCULATE = "calculate";
    public const CALCULATE_SIN = "sin";
    public const CALCULATE_COS = "cos";
    public const CALCULATE_TAN = "tan";
    public const CALCULATE_FLOOR = "floor";
    public const CALCULATE_CEIL = "ceil";
    public const CALCULATE2 = "calculate2";
    public const CALCULATE2_ROUND = "round";
    public const GET_PI = "getPi";
    public const GET_E = "getE";
    public const GENERATE_RANDOM_NUMBER = "random";
    public const REVERSE_POLISH_NOTATION = "calculateRPN";

    public const EDIT_STRING = "editString";
    public const STRING_LENGTH = "strlen";

    public const ADD_VARIABLE = "addVariable";
    public const DELETE_VARIABLE = "deleteVariable";
    public const ADD_LIST_VARIABLE = "addListVariable";
    public const JOIN_LIST_VARIABLE_TO_STRING = "joinToString";
    public const CREATE_LIST_VARIABLE = "createList";
    public const ADD_MAP_VARIABLE = "addMapVariable";
    public const CREATE_MAP_VARIABLE = "createMap";
    public const CREATE_MAP_VARIABLE_FROM_JSON = "createMapFromJson";
    public const CREATE_ITEM_VARIABLE = "createItem";
    public const CREATE_POSITION_VARIABLE = "createPosition";
    public const CREATE_BLOCK_VARIABLE = "createBlock";
    public const GET_VARIABLE_NESTED = "getVariable";
    public const SET_PLAYER_DATA = "setPlayerData";
    public const SET_DEFAULT_PLAYER_DATA = "setDefaultPlayerData";
    public const COUNT_LIST_VARIABLE = "count";
    public const GET_LIST_KEYS = "keys";
    public const GET_LIST_VALUES = "values";
    public const SEARCH_LIST_KEY = "searchKey";
    public const DELETE_LIST_VARIABLE_CONTENT = "removeContent";
    public const DELETE_LIST_VARIABLE_CONTENT_BY_VALUE = "removeContentByValue";
    public const CREATE_SCOREBOARD_VARIABLE = "createScoreboard";

    public const ACTION_IF = "if";
    public const ACTION_ELSEIF = "elseif";
    public const ACTION_ELSE = "else";
    public const ACTION_REPEAT = "repeat";
    public const ACTION_WHILE = "while";
    public const ACTION_WAIT = "wait";
    public const ACTION_FOR = "for";
    public const ACTION_IF_NOT = "if_not";
    public const FOREACH_POSITION = "foreachPosition";
    public const ACTION_FOREACH = "foreach";
    public const SAVE_DATA = "save";
    public const ACTION_WHILE_TASK = "whileTask";
    public const CREATE_CONFIG_VARIABLE = "createConfig";
    public const SET_CONFIG_VALUE = "setConfig";
    public const SAVE_CONFIG_FILE = "saveConfig";
    public const REMOVE_CONFIG_VALUE = "removeConfig";
    public const GET_CONFIG_VALUE = "getConfig";
    public const GET_CONFIG_VALUE_OR_SET = "getConfigOrSet";
    public const EXIT_RECIPE = "exit";
    public const EXIT_CONTAINER = "break";
    public const ACTION_GROUP = "group";

    public const SEND_INPUT = "input";
    public const SEND_MENU = "select";
    public const SEND_SLIDER = "slider";
    public const SEND_DROPDOWN = "dropdown";
    public const SEND_STEP_SLIDER = "stepSlider";
    public const SEND_CONFIRM_FORM = "sendConfirmForm";
    public const CREATE_LIST_FORM = "createListForm";
    public const ADD_BUTTON = "addButton";
    public const SHOW_FORM_VARIABLE = "showFormVariable";

    public const SET_SCOREBOARD_SCORE = "setScore";
    public const SET_SCOREBOARD_SCORE_NAME = "setScoreName";
    public const REMOVE_SCOREBOARD_SCORE = "removeScore";
    public const REMOVE_SCOREBOARD_SCORE_NAME = "removeScoreName";
    public const INCREMENT_SCOREBOARD_SCORE = "incrementScore";
    public const DECREMENT_SCOREBOARD_SCORE = "decrementScore";

    public const REGISTER_SHAPED_RECIPE = "registerShapedRecipe";

    public const ADD_SPECIFIC_LANGUAGE_MAPPING = "addSpecificLanguageMapping";
    public const ADD_LANGUAGE_MAPPINGS = "addLanguageMappings";
    public const GET_LANGUAGE_MESSAGE = "getLanguageMessage";

    /* condition */
    public const CHECK_NOTHING = "checkNothing";
    public const IS_OP = "isOp";
    public const IS_SNEAKING = "isSneaking";
    public const IS_FLYING = "isFlying";
    public const IS_SPRINTING = "isSprinting";
    public const IS_SWIMMING = "isSwimming";
    public const IS_GLIDING = "isGliding";
    public const IN_AREA = "inArea";
    public const IN_WORLD = "inWorld";
    public const GAMEMODE = "gamemode";
    public const HAS_PERMISSION = "hasPermission";

    public const OVER_MONEY = "overMoney";
    public const LESS_MONEY = "lessMoney";
    public const TAKE_MONEY_CONDITION = "takeMoneyCondition";

    public const CAN_ADD_ITEM = "canAddItem";
    public const EXISTS_ITEM = "existsItem";
    public const IN_HAND = "isHandItem";
    public const REMOVE_ITEM_CONDITION = "removeItemCondition";
    public const EXISTS_ARMOR = "existsArmor";

    public const COMPARISON_NUMBER = "comparisonNumber";
    public const COMPARISON_STRING = "comparisonString";

    public const IS_ACTIVE_ENTITY = "isActiveEntity";
    public const IS_ACTIVE_ENTITY_VARIABLE = "isActiveEntityVariable";
    public const IS_PLAYER = "isPlayer";
    public const IS_PLAYER_VARIABLE = "isPlayerVariable";
    public const IS_PLAYER_ONLINE = "isPlayerOnline";
    public const IS_PLAYER_ONLINE_BY_NAME = "isPlayerOnlineByName";
    public const IS_CREATURE = "isCreature";
    public const IS_CREATURE_VARIABLE = "isCreatureVariable";

    public const EXISTS_VARIABLE = "existsVariable";
    public const EXISTS_LIST_VARIABLE_KEY = "existsListVariableKey";

    public const CONDITION_AND = "and";
    public const CONDITION_OR = "or";
    public const CONDITION_NAND = "nand";
    public const CONDITION_NOR = "nor";
    public const CONDITION_NOT = "not";
    public const EXISTS_CONFIG_FILE = "existsConfigFile";
    public const EXISTS_CONFIG_DATA = "existsConfig";

    public const RANDOM_NUMBER = "randomNumber";

    public const ONLINE_PLAYER_LESS_THAN = "onlinePlayerLessThan";
    public const ONLINE_PLAYER_MORE_THAN = "onlinePlayerMoreThan";

    public const IS_SAME_ITEM = "isSameItem";
    public const IS_SAME_BLOCk = "isSameBlock";
}