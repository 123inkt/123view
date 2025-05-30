<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Form\Rule;

use DR\Review\Controller\App\Notification\RuleController;
use DR\Review\Entity\Notification\Rule;
use DR\Review\Form\Rule\EditRuleFormType;
use DR\Review\Form\Rule\RuleType;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use function DR\PHPUnitExtensions\Mock\consecutive;

#[CoversClass(EditRuleFormType::class)]
class EditRuleFormTypeTest extends AbstractTestCase
{
    private UrlGeneratorInterface&MockObject $urlGenerator;
    private EditRuleFormType                 $type;

    protected function setUp(): void
    {
        parent::setUp();
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->type         = new EditRuleFormType($this->urlGenerator);
    }

    public function testBuildForm(): void
    {
        $url  = 'https://123view/add/rule';
        $rule = (new Rule())->setId(123);

        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with(RuleController::class, ['id' => 123])
            ->willReturn($url);

        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects($this->once())->method('setAction')->with($url);
        $builder->expects($this->once())->method('setMethod')->with('POST');
        $builder->expects($this->exactly(2))
            ->method('add')
            ->with(
                ...consecutive(
                    ['rule', RuleType::class],
                    ['save', SubmitType::class, ['label' => 'save']],
                )
            )->willReturnSelf();

        $this->type->buildForm($builder, ['data' => ['rule' => $rule]]);
    }
}
