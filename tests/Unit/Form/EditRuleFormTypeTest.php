<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Form;

use DR\GitCommitNotification\Controller\App\RuleController;
use DR\GitCommitNotification\Entity\Config\Rule;
use DR\GitCommitNotification\Form\EditRuleFormType;
use DR\GitCommitNotification\Form\Rule\RuleType;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Form\EditRuleFormType
 * @covers ::__construct
 */
class EditRuleFormTypeTest extends AbstractTestCase
{
    /** @var MockObject&UrlGeneratorInterface */
    private UrlGeneratorInterface $urlGenerator;
    private EditRuleFormType      $type;

    protected function setUp(): void
    {
        parent::setUp();
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->type         = new EditRuleFormType($this->urlGenerator);
    }

    /**
     * @covers ::buildForm
     */
    public function testBuildForm(): void
    {
        $url = 'https://commit-notification/add/rule';
        $rule = (new Rule())->setId(123);

        $this->urlGenerator->expects(self::once())
            ->method('generate')
            ->with(RuleController::class, ['id' => 123])
            ->willReturn($url);

        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects(self::once())->method('setAction')->with($url);
        $builder->expects(self::once())->method('setMethod')->with('POST');
        $builder->expects(self::exactly(2))
            ->method('add')
            ->withConsecutive(
                ['rule', RuleType::class],
                ['save', SubmitType::class, ['label' => 'Save']],
            )->willReturnSelf();

        $this->type->buildForm($builder, ['data' => ['rule' => $rule]]);
    }
}
