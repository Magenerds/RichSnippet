<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace Magenerds\Richsnippet\Observer;

use Magenerds\RichSnippet\Block\Schemaorg;
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
    /** @var Schemaorg */
    private $schemaorg;

    /**
     * @param Schemaorg $schemaorg
     */
    public function __construct(Schemaorg $schemaorg)
    {
        $this->schemaorg = $schemaorg;
    }

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
        $weight = $this->schemaorg->getProductSchemaData($weight, $productModel->getWeight(), 'value');
        if (!empty($weight)) {
            $weight['@type'] = 'QuantitativeValue';
            $productSchema['weight'] = $weight;
        }
    }
}
