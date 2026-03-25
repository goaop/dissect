<?php

declare(strict_types=1);

namespace Dissect\Parser\LALR1\Dumper;

/**
 * A common contract for parse table dumpers.
 *
 * @author Jakub Lédl <jakubledl@gmail.com>
 */
interface TableDumper
{
    /**
     * Dumps the parse table.
     *
     * @param array{action: array<int, array<string, int>>, goto: array<int, array<string, int>>} $table The parse table.
     *
     * @return string The resulting string representation of the table.
     */
    public function dump(array $table): string;
}
