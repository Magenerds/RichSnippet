<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * https://opensource.org/licenses/osl-3.0.php
 */

namespace Magenerds\RichSnippet\Setup;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;

/**
 * Class AttributeSetup
 * @package Magenerds\RichSnippet\Setup
 */
class AttributeSetup extends \Magento\Catalog\Setup\CategorySetup
{
    const PRODUCT_ATTRIBUTES = [
        'meta_seo_category' => [
            'type' => 'varchar',
            'label' => 'Primary Category ID',
            'input' => 'text',
            'required' => false,
            'sort_order' => 50,
            'global' => ScopedAttributeInterface::SCOPE_STORE,
            'group' => 'Search Engine Optimization',
            'is_used_in_grid' => false,
            'is_visible_in_grid' => false,
            'is_filterable_in_grid' => false,
            'note' => 'For schema.org breadcrumb.'
        ],
    ];

    /**
     * Adds attributes and attribute group for product which defines the
     * configuration options in the Products edit form
     */
    public function addSeoProductCategoryAttributes()
    {
        $this->getSetup()->startSetup();

        $entityTypeId = $this->getEntityTypeId(Product::ENTITY);
        $this->addAttribute($entityTypeId, 'meta_seo_category', self::PRODUCT_ATTRIBUTES['meta_seo_category']);

        $this->getSetup()->endSetup();
    }
}
