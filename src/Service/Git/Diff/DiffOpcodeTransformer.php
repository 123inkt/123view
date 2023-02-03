<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Diff;

use DR\Review\Entity\Git\Diff\DiffChange;

class DiffOpcodeTransformer
{
    /**
     * @return DiffChange[]
     */
    public function transform(string $beforeChange, string $opcodes): array
    {
        $result        = [];
        $opcodesLength = mb_strlen($opcodes);

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
                    $result[]   = new DiffChange(DiffChange::UNCHANGED, mb_substr($beforeChange, $fromOffset, $length));
                    $fromOffset += $length;
                    break;
                case 'd':
                    // delete n characters from source
                    $result[]   = new DiffChange(DiffChange::REMOVED, mb_substr($beforeChange, $fromOffset, $length));
                    $fromOffset += $length;
                    break;
                default:
                    // insert n characters from opcodes
                    $result[]      = new DiffChange(DiffChange::ADDED, mb_substr($opcodes, $opcodesOffset + 1, $length));
                    $opcodesOffset += 1 + $length;
                    break;
            }
        }

        return $result;
    }
}
