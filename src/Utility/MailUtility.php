<?php

namespace DigitalMarketingFramework\Distributor\Mail\Utility;

class MailUtility
{
    public static function encode(string $string): string
    {
        return '=?UTF-8?B?' . base64_encode(trim($string)) . '?=';
    }
}
