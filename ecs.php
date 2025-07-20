<?php

declare(strict_types=1);

use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->paths([__DIR__ . '/src', __DIR__ . '/tests']);

    $ecsConfig->sets([
        SetList::PSR_12,
        SetList::SYMPLIFY,        // clean code rules from Symplify
        SetList::COMMON,          // additional standard rules
        SetList::CLEAN_CODE,      // general clean-up rules
    ]);
};
