<?php
declare(strict_types=1);

namespace DR\Review\Entity\Report;

use DR\Review\Utility\Assert;
use Nette\Utils\Json;
use Nette\Utils\JsonException;

class LineCoverage
{
    /** @var array<int, int> */
    private array $lines = [];

    /**
     * @throws JsonException
     */
    public static function fromBinaryString(string $data): self
    {
        $lineCoverage = new LineCoverage();
        if ($data !== '') {
            $lineCoverage->lines = Assert::isArray(Json::decode(Assert::notFalse(gzuncompress($data)), true));
        }

        return $lineCoverage;
    }

    /**
     * @throws JsonException
     */
    public function toBinaryString(): string
    {
        if (count($this->lines) === 0) {
            return '';
        }

        // level 3 compression for best compression/performance ratio
        return Assert::notFalse(gzcompress(Json::encode($this->lines), 3));
    }

    public function getPercentage(): ?float
    {
        $covered   = 0;
        $uncovered = 0;

        foreach ($this->lines as $coverage) {
            if ($coverage === 0) {
                ++$uncovered;
            } else {
                ++$covered;
            }
        }

        if ($uncovered === 0) {
            return 100.0;
        }

        return $covered / ($covered + $uncovered) * 100;
    }

    public function setCoverage(int $lineNumber, int $count): self
    {
        $this->lines[$lineNumber] = $count;

        return $this;
    }

    public function getCoverage(int $lineNumber): ?int
    {
        return $this->lines[$lineNumber] ?? null;
    }
}
