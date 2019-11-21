<?php

namespace aieuo\mineflow\action\process;

interface ProcessIds {

    const DO_NOTHINIG = "doNothing";
    const EVENT_CANCEL = "eventCancel";

    const SEND_MESSAGE = "sendMessage";
    const SEND_TIP = "sendTip";
    const SEND_POPUP = "sendPopup";
    const SEND_BROADCAST_MESSAGE = "broadcastMessage";
    const SEND_MESSAGE_TO_OP= "sendMessageToOp";
    const SEND_TITLE = "sendTitle";

    const GET_ENTITY = "getEntity";
    const TELEPORT = "teleport";
    const MOTION = "motion";
    const SET_SLEEPING = "setSleeping";

    const ADD_MONEY = "addMoney";
    const SET_MONEY = "setMoney";
    const TAKE_MONEY = "takeMoney";
    const GET_MONEY = "getMoney";

    const EXECUTE_RECIPE = "executeRecipe";
    const EXECUTE_RECIPE_WITH_ENTITY = "executeRecipeWithEntity";
}