<?php

namespace aieuo\mineflow\action\process;

interface ProcessIds {

    const DO_NOTHINIG = "doNothing";

    const SEND_MESSAGE = "sendMessage";
    const SEND_TIP = "sendTip";
    const SEND_POPUP = "sendPopup";
    const SEND_BROADCAST_MESSAGE = "broadcastMessage";
    const SEND_MESSAGE_TO_OP= "sendMessageToOp";
}