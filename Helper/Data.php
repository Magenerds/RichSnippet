<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace Magenerds\RichSnippet\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

/**
 * @category   Magenerds
 * @package    Magenerds_RichSnippet
 * @subpackage Block
 * @copyright  Copyright (c) 2019 TechDivision GmbH (https://www.techdivision.com)
 * @link       https://www.techdivision.com/
 * @author     Belinda Tschampel <b.tschampel@techdivision.com>
 * @author     Philipp Steinkopff <p.steinkopff@techdivision.com>
 */
class Data extends AbstractHelper
{
    /**
     * Load color config value
     *
     * @param int $storeId
     * @return mixed
     */
    public function getColorConfig($storeId = 0)
    {
        return $this->getConfig($storeId, 'color');
    }

    /**
     * Load logo config value
     *
     * @param int $storeId int
     * @return string
     */
    public function getLogoConfig($storeId = 0)
    {
        return $this->getConfig($storeId, 'logo');
    }

    /**
     * Load description config value
     *
     * @param int $storeId int
     * @return mixed
     */
    public function getDescriptionType($storeId = 0)
    {
        return $this->getConfig($storeId, 'description');
    }

    /**
     * Load sku config value
     *
     * @param int $storeId int
     * @return mixed
     */
    public function getSkuConfig($storeId = 0)
    {
        return $this->getConfig($storeId, 'sku');
    }

    /**
     * Load product_id config value
     *
     * @param int $storeId int
     * @return mixed
     */
    public function getProductIdConfig($storeId = 0)
    {
        return $this->getConfig($storeId, 'product_id');
    }

    /**
     * Load brand config value
     *
     * @param int $storeId int
     * @return mixed
     */
    public function getBrandConfig($storeId = 0)
    {
        return $this->getConfig($storeId, 'brand');
    }

    /**
     * Load any config value
     *
     * @param int $storeId int
     * @return string
     */
    public function getDynamicConfigValue($name, $second, $storeId = 0)
    {
        return $this->getConfig($storeId, $name, $second);
    }

    /**
     * Load facebook_app_id config value
     *
     * @param int $storeId
     * @return string
     */
    public function getFacebookAppIdConfig($storeId = 0)
    {
        return $this->scopeConfig->getValue('richsnippet/open_graph/facebook_app_id', ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * Returns system configuration
     *
     * @param $storeId int store id
     * @param $name string configname
     * @return mixed
     */
    protected function getConfig($storeId, $name, $second = 'product_properties')
    {
        if ($storeId) {
            return $this->scopeConfig->getValue('richsnippet/' . $second . '/' . $name, ScopeInterface::SCOPE_STORE, $storeId);
        } else {
            return $this->scopeConfig->getValue('richsnippet/' . $second . '/' . $name);
        }
    }
}
