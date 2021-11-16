<?php

namespace Ivanstan\SymfonyRest\Services;

class DateTimeService
{
    public const UTC_TIMEZONE_NAME = 'UTC';

    /**
     * @codeCoverageIgnore
     *
     * @throws \Exception
     */
    public static function getCurrentUTC(): \DateTime
    {
        return new \DateTime('now', new \DateTimeZone(self::UTC_TIMEZONE_NAME));
    }
}
