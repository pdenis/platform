<?php

namespace Oro\Bundle\ApiBundle\Tests\Unit\Processor\Subresource\Shared;

use Doctrine\ORM\EntityRepository;

use Oro\Bundle\ApiBundle\Config\EntityDefinitionConfig;
use Oro\Bundle\ApiBundle\Processor\Subresource\Shared\LoadParentEntity;
use Oro\Bundle\ApiBundle\Tests\Unit\Processor\Subresource\GetSubresourceProcessorTestCase;
use Oro\Bundle\ApiBundle\Util\DoctrineHelper;

class LoadParentEntityTest extends GetSubresourceProcessorTestCase
{
    const TEST_PARENT_CLASS_NAME = 'Test\Class';

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $doctrineHelper;

    /** @var LoadParentEntity */
    protected $processor;

    protected function setUp()
    {
        parent::setUp();

        $this->doctrineHelper = $this->getMockBuilder(DoctrineHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->processor = new LoadParentEntity($this->doctrineHelper);
    }

    public function testProcessWhenParentEntityIsAlreadyLoaded()
    {
        $entity = new \stdClass();

        $this->doctrineHelper->expects($this->never())
            ->method('isManageableEntityClass');

        $this->context->setParentEntity($entity);
        $this->processor->process($this->context);

        $this->assertSame($entity, $this->context->getParentEntity());
    }

    public function testProcessForNotManageableEntity()
    {
        $this->doctrineHelper->expects($this->once())
            ->method('isManageableEntityClass')
            ->with(self::TEST_PARENT_CLASS_NAME)
            ->willReturn(false);

        $this->context->setParentClassName(self::TEST_PARENT_CLASS_NAME);
        $this->context->setParentConfig(new EntityDefinitionConfig());
        $this->processor->process($this->context);

        $this->assertFalse($this->context->hasParentEntity());
    }

    public function testProcessForManageableEntity()
    {
        $parentId = 123;
        $entity = new \stdClass();

        $this->doctrineHelper->expects($this->once())
            ->method('isManageableEntityClass')
            ->with(self::TEST_PARENT_CLASS_NAME)
            ->willReturn(true);
        $repo = $this->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->doctrineHelper->expects($this->once())
            ->method('getEntityRepositoryForClass')
            ->with(self::TEST_PARENT_CLASS_NAME)
            ->willReturn($repo);
        $repo->expects($this->once())
            ->method('find')
            ->with($parentId)
            ->willReturn($entity);

        $this->context->setParentClassName(self::TEST_PARENT_CLASS_NAME);
        $this->context->setParentId($parentId);
        $this->processor->process($this->context);

        $this->assertSame($entity, $this->context->getParentEntity());
    }

    public function testProcessForManageableEntityWhenEntityNotFound()
    {
        $parentId = 123;

        $this->doctrineHelper->expects($this->once())
            ->method('isManageableEntityClass')
            ->with(self::TEST_PARENT_CLASS_NAME)
            ->willReturn(true);
        $repo = $this->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->doctrineHelper->expects($this->once())
            ->method('getEntityRepositoryForClass')
            ->with(self::TEST_PARENT_CLASS_NAME)
            ->willReturn($repo);
        $repo->expects($this->once())
            ->method('find')
            ->with($parentId)
            ->willReturn(null);

        $this->context->setParentClassName(self::TEST_PARENT_CLASS_NAME);
        $this->context->setParentId($parentId);
        $this->processor->process($this->context);

        $this->assertNull($this->context->getParentEntity());
        $this->assertTrue($this->context->hasParentEntity());
    }

    public function testProcessForResourceBasedOnManageableEntity()
    {
        $parentResourceClass = 'Test\ParentResourceClass';
        $parentId = 123;
        $entity = new \stdClass();

        $parentConfig = new EntityDefinitionConfig();
        $parentConfig->setParentResourceClass($parentResourceClass);

        $this->doctrineHelper->expects($this->exactly(2))
            ->method('isManageableEntityClass')
            ->willReturnMap([
                [self::TEST_PARENT_CLASS_NAME, false],
                [$parentResourceClass, true],
            ]);
        $repo = $this->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->doctrineHelper->expects($this->once())
            ->method('getEntityRepositoryForClass')
            ->with($parentResourceClass)
            ->willReturn($repo);
        $repo->expects($this->once())
            ->method('find')
            ->with($parentId)
            ->willReturn($entity);

        $this->context->setParentClassName(self::TEST_PARENT_CLASS_NAME);
        $this->context->setParentId($parentId);
        $this->context->setParentConfig($parentConfig);
        $this->processor->process($this->context);

        $this->assertSame($entity, $this->context->getParentEntity());
    }

    public function testProcessForResourceBasedOnNotManageableEntity()
    {
        $parentResourceClass = 'Test\ParentResourceClass';
        $parentId = 123;

        $parentConfig = new EntityDefinitionConfig();
        $parentConfig->setParentResourceClass($parentResourceClass);

        $this->doctrineHelper->expects($this->exactly(2))
            ->method('isManageableEntityClass')
            ->willReturnMap([
                [self::TEST_PARENT_CLASS_NAME, false],
                [$parentResourceClass, false],
            ]);
        $this->doctrineHelper->expects($this->never())
            ->method('getEntityRepositoryForClass');

        $this->context->setParentClassName(self::TEST_PARENT_CLASS_NAME);
        $this->context->setParentId($parentId);
        $this->context->setParentConfig($parentConfig);
        $this->processor->process($this->context);

        $this->assertNull($this->context->getParentEntity());
    }
}
