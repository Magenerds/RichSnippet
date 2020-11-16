<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * https://opensource.org/licenses/osl-3.0.php
 */

namespace Magenerds\RichSnippet\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magenerds\RichSnippet\Setup\AttributeSetupFactory;
use Magento\Framework\App\State;

/**
 * Class InstallData
 *
 * @package Magenerds\RichSnippet\Setup
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var AttributeSetupFactory
     */
    private $attributeSetupFactory;

    /**
     * @var State
     */
    private $state;

    /**
     * InstallData constructor.
     *
     * @param State $state
     * @param AttributeSetupFactory $attributeSetupFactory
     */
    public function __construct(
        State $state,
        AttributeSetupFactory $attributeSetupFactory
    ) {
        $this->state = $state;
        $this->attributeSetupFactory = $attributeSetupFactory;
    }

    /**
     * Installs data for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     * @throws \Exception
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if ($context->getVersion() && version_compare($context->getVersion(), '1.1.0') < 0) {

            $attributeSetup = $this->attributeSetupFactory->create(['setup' => $setup]);
            $this->state->emulateAreaCode(
                \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE,
                [$attributeSetup, 'addSeoProductCategoryAttributes']
            );
        }
    }
}
