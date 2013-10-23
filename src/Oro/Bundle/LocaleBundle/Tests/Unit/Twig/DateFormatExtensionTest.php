<?php

namespace Oro\Bundle\LocaleBundle\Tests\Unit\Twig;

use Oro\Bundle\LocaleBundle\Twig\DateFormatExtension;

class DateFormatExtensionTest extends \PHPUnit_Framework_TestCase
{
    const TEST_TYPE = 'test_format_type';
    const TEST_FORMAT = 'MMM, d y t';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $converterRegistry;

    /**
     * @var DateFormatExtension
     */
    protected $extension;

    /**
     * @var array
     */
    protected $expectedFunctions = array(
        'oro_dateformat' => 'getDateFormat',
        'oro_timeformat' => 'getTimeFormat',
        'oro_datetimeformat' => 'getDateTimeFormat',
    );

    protected function setUp()
    {
        $this->converterRegistry =
            $this->getMockBuilder('Oro\Bundle\LocaleBundle\Converter\DateTimeFormatConverterRegistry')
                ->disableOriginalConstructor()
                ->setMethods(array('getFormatConverter'))
                ->getMock();

        $this->extension = new DateFormatExtension($this->converterRegistry);
    }

    protected function tearDown()
    {
        unset($this->converterRegistry);
        unset($this->extension);
    }

    public function testGetName()
    {
        $this->assertEquals('oro_locale_dateformat', $this->extension->getName());
    }

    public function testGetFunctions()
    {
        $actualFunctions = $this->extension->getFunctions();
        $this->assertSameSize($this->expectedFunctions, $actualFunctions);

        /** @var $actualFunction \Twig_SimpleFunction */
        foreach ($actualFunctions as $actualFunction) {
            $this->assertInstanceOf('\Twig_SimpleFunction', $actualFunction);
            $actualFunctionName = $actualFunction->getName();
            $this->assertArrayHasKey($actualFunctionName, $this->expectedFunctions);
            $expectedCallback = array($this->extension, $this->expectedFunctions[$actualFunctionName]);
            $this->assertEquals($expectedCallback, $actualFunction->getCallable());
        }
    }

    public function testGetDateFormat()
    {
        $locale = 'en';
        $dateType = 'short';

        $formatConverter = $this->createFormatConverter();
        $formatConverter->expects($this->once())
            ->method('getDateFormat')
            ->with($dateType, $locale)
            ->will($this->returnValue(self::TEST_FORMAT));

        $this->converterRegistry->expects($this->once())
            ->method('getFormatConverter')
            ->with(self::TEST_TYPE)
            ->will($this->returnValue($formatConverter));

        $this->assertEquals(self::TEST_FORMAT, $this->extension->getDateFormat(self::TEST_TYPE, $dateType, $locale));
    }

    public function testGetTimeFormat()
    {
        $locale = 'en';
        $timeType = 'short';

        $formatConverter = $this->createFormatConverter();
        $formatConverter->expects($this->once())
            ->method('getTimeFormat')
            ->with($timeType, $locale)
            ->will($this->returnValue(self::TEST_FORMAT));

        $this->converterRegistry->expects($this->once())
            ->method('getFormatConverter')
            ->with(self::TEST_TYPE)
            ->will($this->returnValue($formatConverter));

        $this->assertEquals(self::TEST_FORMAT, $this->extension->getTimeFormat(self::TEST_TYPE, $timeType, $locale));
    }

    public function testGetDateTimeFormat()
    {
        $locale = 'en';
        $dateType = 'medium';
        $timeType = 'short';

        $formatConverter = $this->createFormatConverter();
        $formatConverter->expects($this->once())
            ->method('getDateTimeFormat')
            ->with($dateType, $timeType, $locale)
            ->will($this->returnValue(self::TEST_FORMAT));

        $this->converterRegistry->expects($this->once())
            ->method('getFormatConverter')
            ->with(self::TEST_TYPE)
            ->will($this->returnValue($formatConverter));

        $this->assertEquals(
            self::TEST_FORMAT,
            $this->extension->getDateTimeFormat(self::TEST_TYPE, $dateType, $timeType, $locale)
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function createFormatConverter()
    {
        return $this->getMockBuilder('Oro\Bundle\LocaleBundle\Converter\DateTimeFormatConverterInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
    }
}
