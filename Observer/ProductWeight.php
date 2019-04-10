<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace Magenerds\Richsnippet\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
/**
 * Class ProductWeight
 *
 * @copyright   Copyright (c) 2019 TechDivision GmbH (http://www.techdivision.com)
 * @site        https://www.techdivision.com/
 * @author      Martin Eisenführer <m.eisenfuehrer@techdivision.com>
 */
class ProductWeight implements ObserverInterface
{
    /**
     * Execute observer function
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        // get block
        $productSchema = $observer->getData('productSchema');
        $productModel = $observer->getData('productModel');

        $weight = [];
        $weight = $this->getProductSchemaData($weight, $productModel->getWeight(), 'value');
        if (!empty($weight)) {
            $weight['@type'] = 'QuantitativeValue';
            $productSchema['weight'] = $weight;
        }
    }


    protected function getProductSchemaData(array $data, $value, $key)
    {
        if (is_string($value)) {
            if ($this->valueIsSet($value)) {
                $data[$key] = $value;
            }
        } elseif ($value !== null && $value !== false) {
            $data[$key] = $value;
        }
        return $data;
    }
}
