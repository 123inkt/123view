<?php
declare(strict_types=1);

namespace DR\Review\Response;

use Symfony\Component\HttpFoundation\JsonResponse;

class ProblemJsonResponse extends JsonResponse
{
    /**
     * @param array<string, int|string|string[]> $data
     * @param array<string, string|string[]>     $headers
     */
    public function __construct(array $data = [], int $status = 200, array $headers = [], bool $json = false)
    {
        parent::__construct($data, $status, $headers, $json);
        $this->headers->set('content-type', 'application/problem+json');
    }
}
