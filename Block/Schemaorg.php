<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace Magenerds\RichSnippet\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\Registry;
use Magento\Review\Model\Review\SummaryFactory;
use Magento\Framework\View\Element\Template\Context;
use Magenerds\RichSnippet\Helper\Data;
use Magento\Theme\Block\Html\Header\Logo;
use Magento\Catalog\Model\Product;
use Magento\Review\Model\Review\Summary;

/**
 * Class Schemaorg
 *
 * @package     Magenerds\RichSnippet\Block
 * @file        Schemaorg.php
 * @copyright   Copyright (c) 2017 TechDivision GmbH (http://www.techdivision.com)
 * @site        https://www.techdivision.com/
 * @author      Philipp Steinkopff <p.steinkopff@techdivision.com>
 * @author      Belinda Tschampel <b.tschampel@techdivision.com>
 */
class Schemaorg extends Template
{
    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var SummaryFactory
     */
    protected $reviewSummaryFactory;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var Logo
     */
    protected $logo;

    /**
     * Schemaorg constructor.
     * @param Registry $registry
     * @param SummaryFactory $reviewSummaryFactory
     * @param Data $helper
     * @param Logo $logo
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Registry $registry,
        SummaryFactory $reviewSummaryFactory,
        Data $helper,
        Logo $logo,
        Context $context,
        $data = []
    )
    {
        $this->coreRegistry = $registry;
        $this->reviewSummaryFactory = $reviewSummaryFactory;
        $this->helper = $helper;
        $this->logo = $logo;
        parent::__construct($context, $data);
    }

    /**
     * Check if a value is a string and not empty.
     *
     * @param $value
     * @return bool
     */
    public function valueIsSet($value)
    {
        return is_string($value) && strlen(trim($value));
    }

    /**
     * Retrieve current product model
     *
     * @return Product
     */
    public function getProduct()
    {
        return $this->coreRegistry->registry('product');
    }

    /**
     * Logo image url if set to active
     *
     * @return string
     */
    public function getLogo()
    {
        $logoUrl = '';
        if ($this->helper->getLogoConfig()) {
            $logoUrl = $this->logo->getLogoSrc();
        }
        return $logoUrl;
    }

    /**
     * Short or long description depending on configuration
     *
     * @return string
     */
    public function getDescription()
    {
        if ($this->helper->getDescriptionType()) {
            return nl2br($this->getProduct()->getData('description'));
        } else {
            return nl2br($this->getProduct()->getData('short_description'));
        }
    }

    /**
     * @return Summary
     */
    public function getReviewSummary()
    {
        $storeId = $this->_storeManager->getStore()->getId();

        /** @var $reviewSummary Summary */
        $reviewSummary = $this->reviewSummaryFactory->create();
        $reviewSummary->setData('store_id', $storeId);
        $summaryModel = $reviewSummary->load($this->getProduct()->getId());

        return $summaryModel;
    }

    /**
     * @return string
     */
    public function getCurrencyCode()
    {
        return $this->_storeManager->getStore()->getCurrentCurrencyCode();
    }

    /**
     * Specific color name for product
     *
     * @return string
     */
    public function getColor()
    {
        $colorAttribute = $this->helper->getColorConfig();

        return $this->getAttribute($colorAttribute);
    }

    /**
     * Sku value for product
     *
     * @return string
     */
    public function getSku()
    {
        $skuAttribute = $this->helper->getSkuConfig();

        return $this->getAttribute($skuAttribute);
    }

    /**
     * Product id value for product
     *
     * @return string
     */
    public function getProductId()
    {
        $productIdAttribute = $this->helper->getProductIdConfig();

        return $this->getAttribute($productIdAttribute);
    }

    /**
     * Specific brand name for product
     *
     * @return string
     */
    public function getBrand()
    {
        $brandAttribute = $this->helper->getBrandConfig();

        return $this->getAttribute($brandAttribute);
    }

    /**
     * Returns the attribute value for the given attribute code
     *
     * @param $code
     * @return string
     */
    protected function getAttribute($code)
    {
        $attributeValue = '';

        $attribute = $this->getProduct()->getResource()->getAttribute($code);

        if (!$attribute) return $attributeValue;

        if (in_array($attribute->getFrontendInput(), ['select', 'multiselect'])) {
            $attributeValue = $this->getProduct()->getAttributeText($code);
        } else {
            $attributeValue = $this->getProduct()->getData($code);
        }

        return $attributeValue;
    }
}