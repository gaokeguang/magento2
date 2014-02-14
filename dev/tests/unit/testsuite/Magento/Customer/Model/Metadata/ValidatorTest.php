<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Customer\Model\Metadata;

class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var Validator */
    protected $validator;

    /** @var string */
    protected $entityType;

    /** @var \Magento\Customer\Model\Metadata\ElementFactory | \PHPUnit_Framework_MockObject_MockObject */
    protected $attrDataFactoryMock;

    public function setUp()
    {
        $this->attrDataFactoryMock = $this->getMockBuilder('\Magento\Customer\Model\Metadata\ElementFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $this->validator = new Validator($this->attrDataFactoryMock);
    }

    public function testValidateDataWithNoDataModel()
    {
        $attribute = $this->getMockBuilder('\Magento\Customer\Service\V1\Dto\Eav\AttributeMetadata')
            ->disableOriginalConstructor()
            ->getMock();
        $this->attrDataFactoryMock
            ->expects($this->never())
            ->method('create');
        $this->assertTrue($this->validator->validateData([], [$attribute], 'ENTITY_TYPE'));
    }

    /**
     * @param bool $isValid
     * @dataProvider trueFalseDataProvider
     */
    public function testValidateData($isValid)
    {
        $attribute = $this->getMockAttribute();
        $this->mockDataModel($isValid, $attribute);
        $this->assertEquals($isValid, $this->validator->validateData([], [$attribute], 'ENTITY_TYPE'));
    }

    public function testIsValidWithNoModel()
    {
        $attribute = $this->getMockBuilder('\Magento\Customer\Service\V1\Dto\Eav\AttributeMetadata')
            ->disableOriginalConstructor()
            ->getMock();
        $this->attrDataFactoryMock
            ->expects($this->never())
            ->method('create');
        $this->validator->setAttributes([$attribute]);
        $this->validator->setEntityType('ENTITY_TYPE');
        $this->validator->setData(['something']);
        $this->assertTrue($this->validator->isValid('entity'));
        $this->validator->setData([]);
        $this->assertTrue($this->validator->isValid(new \Magento\Object([])));
    }

    /**
     * @param bool $isValid
     * @dataProvider trueFalseDataProvider
     */
    public function testIsValid($isValid)
    {
        $attribute = $this->getMockAttribute();
        $this->mockDataModel($isValid, $attribute);
        $this->validator->setAttributes([$attribute]);
        $this->validator->setEntityType('ENTITY_TYPE');
        $this->validator->setData(['something']);
        $this->assertEquals($isValid, $this->validator->isValid('ENTITY'));
        $this->validator->setData([]);
        $this->assertEquals($isValid, $this->validator->isValid(new \Magento\Object([])));
    }

    public function trueFalseDataProvider()
    {
        return [[true], [false]];
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject | \Magento\Customer\Service\V1\Dto\Eav\AttributeMetadata
     */
    protected function getMockAttribute()
    {
        $attribute = $this->getMockBuilder('\Magento\Customer\Service\V1\Dto\Eav\AttributeMetadata')
            ->disableOriginalConstructor()
            ->setMethods(['__wakeup', 'getAttributeCode', 'getDataModel'])
            ->getMock();
        $attribute->expects($this->any())
            ->method('getAttributeCode')
            ->will($this->returnValue('ATTR_CODE'));
        $attribute->expects($this->any())
            ->method('getDataModel')
            ->will($this->returnValue('DATA_MODEL'));
        return $attribute;
    }

    /**
     * @param bool                                                   $isValid
     * @param \Magento\Customer\Service\V1\Dto\Eav\AttributeMetadata $attribute
     * @return void
     */
    protected function mockDataModel($isValid, \Magento\Customer\Service\V1\Dto\Eav\AttributeMetadata $attribute)
    {
        $dataModel = $this->getMockBuilder('\Magento\Customer\Model\Metadata\Form\Text')
            ->disableOriginalConstructor()
            ->getMock();
        $dataModel->expects($this->any())
            ->method('validateValue')
            ->will($this->returnValue($isValid));
        $this->attrDataFactoryMock
            ->expects($this->any())
            ->method('create')
            ->with(
                $this->equalTo($attribute),
                $this->equalTo('ENTITY_TYPE'),
                $this->equalTo(null)
            )
            ->will($this->returnValue($dataModel));
    }
}
 