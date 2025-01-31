<?php
declare(strict_types=1);

namespace DR\Review\Tests\Functional\Controller\Api\CodeReviewActivity;

use DR\Review\Repository\User\UserRepository;
use DR\Review\Tests\AbstractFunctionalTestCase;
use DR\Review\Tests\DataFixtures\CodeReviewActivityFixtures;
use DR\Utils\Assert;
use Exception;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
class GetCollectionControllerTest extends AbstractFunctionalTestCase
{
    /**
     * @throws Exception
     */
    public function testGet(): void
    {
        $user = Assert::notNull(self::getService(UserRepository::class)->findOneBy(['name' => 'Sherlock Holmes']));

        $this->client->loginUser($user);
        $this->client->request('GET', '/api/code-review-activities');
        self::assertResponseIsSuccessful();

        $data = $this->getResponseArray();
        static::assertIsArray($data);
        static::assertCount(1, $data);
        static::assertSame('event', $data[0]['eventName']);
    }

    /**
     * @inheritDoc
     */
    protected function getFixtures(): array
    {
        return [CodeReviewActivityFixtures::class];
    }
}
