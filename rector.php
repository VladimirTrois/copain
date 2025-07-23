<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([__DIR__ . '/src', __DIR__ . '/tests']);

    // Set level and PHP version
    $rectorConfig->phpVersion(\Rector\ValueObject\PhpVersion::PHP_83);
    $rectorConfig->sets([SetList::PHP_83]);
};
