<?php

namespace AditumPayments\ApiSDK\Enum;

abstract class ChargeStatus {
    public const AUTHORIZED         = "Authorized";
    public const PRE_AUTHORIZED     = "PreAuthorized";
    public const CANCELED           = "Canceled";
    public const PARTIAL            = "Partial";
    public const NOT_AUTHORIZED     = "NotAuthorized";
    public const NOT_PENDING_CANCEL = "PendingCancel";

}