<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace Magenerds\RichSnippet\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection;

/**
 * Class AbstractAttributeConfig
 *
 * @package     Magenerds\RichSnippet\Model\Config\Source
 * @file        AbstractAttributeConfig.php
 * @copyright   Copyright (c) 2019 TechDivision GmbH (https://www.techdivision.com)
 * @site        https://www.techdivision.com/
 * @author      Belinda Tschampel <b.tschampel@techdivision.com>
 * @author      Philipp Steinkopff <p.steinkopff@techdivision.com>
 */
class AbstractAttributeConfig implements ArrayInterface
{
    /**
     * @var CollectionFactory
     */
    protected $attributeCollectionFactory;

    /**
     * AbstractAttributeConfig constructor.
     *
     * @param CollectionFactory $attributeCollectionFactory
     */
    public function __construct(
        CollectionFactory $attributeCollectionFactory
    )
    {
        $this->attributeCollectionFactory = $attributeCollectionFactory;
    }

    /**
     * Return list of Attributes for Brand Options
     *
     * @param string|array $filter
     * @return array
     */
    public function toOptionArray($filter = null)
    {
        $options = [];
        $options[] = [
            'value' => 0,
            'label' => __('Select to activate')
        ];

        /** @var $attributeCollection Collection */
        $attributeCollection = $this->attributeCollectionFactory->create();
        $attributeCollection->addVisibleFilter()
            ->setOrder('frontend_label', Collection::SORT_ORDER_ASC);

        if ($filter) {
            $attributeCollection->addFieldToFilter($filter, 1);
        }

        foreach ($attributeCollection->getItems() as $attribute) {
            $options[] = [
                'value' => $attribute->getData('attribute_code'),
                'label' => $attribute->getData('frontend_label')
            ];
        }

        return $options;
    }
}
