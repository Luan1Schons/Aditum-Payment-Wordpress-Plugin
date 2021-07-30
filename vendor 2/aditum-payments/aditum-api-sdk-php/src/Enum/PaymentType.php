<?php

namespace AditumPayments\ApiSDK\Enum;

abstract class PaymentType {
    public const UNDEFINED   = 0;
    public const DEBIT       = 1;
    public const CREDIT      = 2;
    public const VOUCHER     = 3;
    public const BOLETO      = 4;
    public const TED         = 5;
    public const DOC         = 6;
    public const SAFETY_PAY  = 7;
}
