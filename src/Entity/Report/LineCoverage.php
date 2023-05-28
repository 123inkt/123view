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

    public function setCoverage(int $lineNumber, int $count): void
    {
        $this->lines[$lineNumber] = $count;
    }

    public function getCoverage(int $lineNumber): ?int
    {
        return $this->lines[$lineNumber] ?? null;
    }
}
