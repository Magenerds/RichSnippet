# Magenerds Rich Snippet

* Add some Magento2 product information from schema.org
* Add some organization information from schema.org

Organization and product information can be configured in Store -> Configuration -> Magenerds -> Rich Snippet

To extend organization and product feel free to implement observer for event:
* organization_schema_add_as_last
* product_schema_add_as_last

One observer for **product_schema_add_as_last** for example in Observer/ProductWeight.php
