<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * https://opensource.org/licenses/osl-3.0.php
 */
declare(strict_types=1);

namespace Magenerds\RichSnippet\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Class AttributePatch
 * @package Magenerds\RichSnippet\Setup\Patch\Data
 */
class AttributePatch implements DataPatchInterface
{
    private const PRODUCT_ATTRIBUTES = [
        'meta_seo_category' => [
            'type' => 'varchar',
            'label' => 'Primary Category ID',
            'input' => 'text',
            'required' => false,
            'sort_order' => 50,
            'global' => ScopedAttributeInterface::SCOPE_STORE,
            'user_defined' => true,
            'group' => 'Search Engine Optimization',
            'is_used_in_grid' => false,
            'is_visible_in_grid' => false,
            'is_filterable_in_grid' => false,
            'note' => 'For schema.org breadcrumb.'
        ]
    ];
    /** @var ModuleDataSetupInterface */
    private $moduleDataSetup;
    /** @var EavSetupFactory */
    private $eavSetupFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * @return array|string[]
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @return array|string[]
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * @return AttributePatch|void
     */
    public function apply()
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        foreach (self::PRODUCT_ATTRIBUTES as $attributeCode => $attributeData) {
            if ($eavSetup->getAttributeId(Product::ENTITY, $attributeCode) !== false) {
                // if attribute exits we do nothing
                continue;
            }

            $eavSetup->addAttribute(Product::ENTITY, $attributeCode, $attributeData);
        }

    }
}
