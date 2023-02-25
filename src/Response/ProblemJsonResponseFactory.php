<?php
declare(strict_types=1);

namespace DR\Review\Response;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Throwable;

class ProblemJsonResponseFactory
{
    public function __construct(private bool $debug)
    {
    }

    public function createFromThrowable(Throwable $throwable): ProblemJsonResponse
    {
        $this->debug = true;
        $data        = ['type' => 'about:blank', 'title' => null, 'status' => null, 'detail' => []];

        if ($throwable instanceof HttpExceptionInterface) {
            $data['title']  = $throwable->getMessage();
            $data['status'] = $throwable->getStatusCode();
        } elseif ($throwable instanceof AccessDeniedException) {
            $data['title']  = $throwable->getMessage();
            $data['status'] = Response::HTTP_FORBIDDEN;
        } else {
            $data['title']  = Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR];
            $data['status'] = Response::HTTP_INTERNAL_SERVER_ERROR;
            if ($this->debug) {
                $data['detail'][] = $throwable->getMessage();
            }
        }

        if ($this->debug) {
            $data['detail'] = array_merge($data['detail'], explode("\n", $throwable->getTraceAsString()));
        }

        return new ProblemJsonResponse(array_filter($data, static fn($val) => $val !== null && $val !== ''), $data['status']);
    }
}
