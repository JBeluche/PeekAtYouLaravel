<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

final class DatabaseTable extends Enum
{
    const CALENDAR = 0;
    const DATE = 1;
    const COLOR_ASSOCIATION = 2;
    const COLOR_ASSOCIATION_DATE = 3;
}
