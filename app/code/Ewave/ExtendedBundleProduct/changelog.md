1.0.0 - 2.0.0
=============
* New features:
    * Extended Bundle Product. Solution Architecture
    * Extended Bundle Product. As an admin, I want to be able to create Bundle product from Configurable products
    * Extended Bundle Product. As a user, I want to be able to use Bundle product from Configurable products
    * Extended Bundle Product. As an admin, I want Extended Bundle Product extension to be compatible with Product Qty extension
    * As a user, I want to be able to use Bundle product from Configurable products with drop-down input type
    * As an admin, I want to see a message while trying to add bundle product to a new order
    * As an admin, I want bundle products to be treated as product quantity for every bundle product separately

2.0.1
=============
* Bug fixes:
    * Fixed impossibility to Add to Cart a bundle in Magento CE

2.0.2
=============
* Bug fixes:
    * Fixed impossibility to Add to Cart a bundle with multiselect option

2.0.3
=============
* Bug fixes:
    * Fixed impossibility to delete option in Bundle if other option has type multiselect

2.0.4
=============
* Bug fixes:
    * Fixed displaying of configurable product added to different options only in 1 option 

2.0.5
=============
* Bug fixes:
    * Fixed price updating on bundle's PDP if simple products from configurable have different prices
    * Fixed adding to cart when bundle with same simple products was added in cart as separate item instead qty updating

2.0.6
=============
* Bug fixes:
    * Fixed considering of Default Quantity if Separate Cart Items = Yes
    * Fixed impossibility to delete option in Bundle if other option has type multiselect

2.0.7
=============
* Bug fixes:
    * Fixed ability to add configurable product to bundle option with multiselect type

2.0.8
=============
* Bug fixes:
    * Fixed the error message in back-office by trying to create configurable product

2.1.0
=============
* New features:
    * Cleaning up the code and refactoring

* Bug fixes:
    * Fixed using of regular price instead of tier price for calculation on PDP
    * Fixed the error message after trying to add Bundle with Configurable products to the wishlist

2.2.0
=============
* New features:
    * Compatibility with Magento 2.4 CE & EE

* Bug fixes:
    * Fixed wrong finished price of the bundle on the PDP when the special price is applied
    * Fixed showing bundle product as out of stock on the storefront in some cases

2.2.1
=============
* Bug fixes:
    * Fixed issue when products were added to the cart with the same configuration when user buy different configurations of one product

2.3.0
=============
* New features:
    * As a system, I want to restrict admin to add products with required options to the Bundle product

2.3.1
=============
* Bug fixes:
    * Fixed storefront error by opening the product page

2.3.2
=============
* Bug fixes:
    * Fixed back-office impossibility to save configurable product multiselect options in combination with another input type 
    * Fixed possibility to set zero or negative qty on product page 

2.3.3
=============
* Bug fixes:
    * Fixed displaying of options of the configurable product on invoice PDF file

2.3.4
=============
* Bug fixes:
    * Fixed displaying of options when admin try to add configurable product to order via admin panel

2.3.5
=============
* Bug fixes:
  * Fixed the issue when price of configurable product was displayed without tax

2.3.6
=============
* Bug fixes:
  * Fixed issue with long saving of bundles resulted in timeout error
  * Fixed issue with selecting of configurable options in back-office

2.3.7
=============
* Maintenance:
  * Install/upgrade scripts transformed to new Magento format

2.3.8
=============
* New features:
  * As an admin I want to be able to create order with bundle product which contain configurable product in Admin Panel
* Bug fixes:
  * Fixed issue with opening Bundle product if the configurable with more than 100 options is added