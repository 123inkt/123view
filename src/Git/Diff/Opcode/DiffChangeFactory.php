<?php
declare(strict_types=1);

namespace DR\Review\Git\Diff\Opcode;

use DR\Review\Entity\Git\Diff\DiffChange;

// TODO remove
class DiffChangeFactory
{
    /**
     * @return DiffChange[]
     */
    public function createFromOpcodes(string $beforeChange, string $opcodes): array
    {
        $opcodesLength = mb_strlen($opcodes);
        $changes       = [];

        for ($fromOffset = 0, $opcodesOffset = 0; $opcodesOffset < $opcodesLength;) {
            $opcode = mb_substr($opcodes, $opcodesOffset, 1);
            $opcodesOffset++;
            $length = (int)mb_substr($opcodes, $opcodesOffset);

            if ($length !== 0) {
                $opcodesOffset += mb_strlen((string)$length);
            } else {
                // @codeCoverageIgnoreStart
                $length = 1;
                // @codeCoverageIgnoreEnd
            }

            switch ($opcode) {
                case 'c':
                    // copy n characters from source
                    $changes[]  = new DiffChange(DiffChange::UNCHANGED, mb_substr($beforeChange, $fromOffset, $length));
                    $fromOffset += $length;
                    break;
                case 'd':
                    // delete n characters from source
                    $changes[]  = new DiffChange(DiffChange::REMOVED, mb_substr($beforeChange, $fromOffset, $length));
                    $fromOffset += $length;
                    break;
                default:
                    // insert n characters from opcodes
                    $changes[]     = new DiffChange(DiffChange::ADDED, mb_substr($opcodes, $opcodesOffset + 1, $length));
                    $opcodesOffset += 1 + $length;
                    break;
            }
        }

        return $changes;
    }
}
