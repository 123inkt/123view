<?php
declare(strict_types=1);

namespace DR\Review\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\Annotation\Route;

class MercureTestController
{
    public function __construct(private readonly HubInterface $hub)
    {
    }

    #[Route('/test', self::class, methods: 'GET')]
    public function __invoke(): Response
    {
        $update = new Update(
            '/review/2',
            json_encode(['status' => 'OutOfStock']),
            true // private
        );

        $this->hub->publish($update);

        return new Response('good');
    }
}
