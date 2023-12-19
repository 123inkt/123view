<?php
declare(strict_types=1);

namespace DR\Review\Webhook;

use DR\Review\RemoteEvent\GitlabRemoteEvent;
use DR\Utils\Assert;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\HttpFoundation\ChainRequestMatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcher\IsJsonRequestMatcher;
use Symfony\Component\HttpFoundation\RequestMatcher\MethodRequestMatcher;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RemoteEvent\RemoteEvent;
use Symfony\Component\Webhook\Client\AbstractRequestParser;
use Symfony\Component\Webhook\Exception\RejectWebhookException;

/**
 * @see https://docs.gitlab.com/ee/user/project/integrations/webhooks.html
 * @see https://symfony.com/blog/new-in-symfony-6-3-webhook-and-remoteevent-components
 */
class GitlabRequestParser extends AbstractRequestParser implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private const SUPPORTED_EVENTS = ['Push Hook', 'Note Hook'];

    protected function getRequestMatcher(): RequestMatcherInterface
    {
        /*
         * Conditions:
         * - POST request
         * - Must have X-Gitlab-Event and X-Gitlab-Token headers
         * - Must have valid json body
         */
        return new ChainRequestMatcher([
            new MethodRequestMatcher('POST'),
            new class implements RequestMatcherInterface {
                public function matches(Request $request): bool
                {
                    return $request->headers->has('X-Gitlab-Event') && $request->headers->has('X-Gitlab-Token');
                }
            },
            new IsJsonRequestMatcher(),
        ]);
    }

    protected function doParse(Request $request, string $secret): ?RemoteEvent
    {
        if (trim($secret) === '' || hash_equals($secret, $request->headers->get('X-Gitlab-Token', '')) === false) {
            throw new RejectWebhookException(Response::HTTP_FORBIDDEN, 'Access denied');
        }

        $eventId   = $request->headers->get('X-Gitlab-Event-UUID', '');
        $eventType = Assert::string($request->headers->get('X-Gitlab-Event'));
        if (in_array($eventType, self::SUPPORTED_EVENTS, true) === false) {
            $this->logger?->info('GitlabRequestParser: Unsupported event type {eventType}', ['eventType' => $eventType]);

            return null;
        }

        return new GitlabRemoteEvent($eventType, $eventId, $request->toArray());
    }
}
