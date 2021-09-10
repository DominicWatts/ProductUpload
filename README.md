# Product Upload # 

![phpcs](https://github.com/DominicWatts/ProductUpload/workflows/phpcs/badge.svg)

![PHPCompatibility](https://github.com/DominicWatts/ProductUpload/workflows/PHPCompatibility/badge.svg)

![PHPStan](https://github.com/DominicWatts/ProductUpload/workflows/PHPStan/badge.svg)

![php-cs-fixer](https://github.com/DominicWatts/ProductUpload/workflows/php-cs-fixer/badge.svg)

# Install instructions #

`composer require dominicwatts/productupload`

`php bin/magento setup:upgrade`

# Usage instructions #

Managed within admin

Content > Csv >
  - Product Import

Once import queue has been built product can be inserted a couple of ways

## Submit screen ##

![Submit](https://i2.paste.pics/71f5a6a998f77c50980373a8eebae85f.png)

## Console commnad ## 

`xigen:product:import <import>`

`php bin/magento xigen:product:import import`
