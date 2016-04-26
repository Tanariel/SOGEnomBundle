# SOGEnomBundle
Symfony2 bundle for the [Enom](http://www.enom.com/resellers/api-reseller.aspx) API.
This wraps the Enom API in a Symfony2 bundle.

[![Build Status](https://secure.travis-ci.org/shaneog/SOGEnomBundle.png)](http://travis-ci.org/shaneog/SOGEnomBundle)

**License**

SOGEnomBundle is licensed under the MIT License - see the `Resources/meta/LICENSE` file for details

**Enom API Commands Supported**

*(See [API Command Catalog](http://www.enom.com/APICommandCatalog/index.htm) for more details)*

1. GetAccountInfo
2. GetTLDList
3. GetServiceContact
4. GetOrderList
5. Check (domain registration)
6. GetConfirmationSettings
7. GetExtAttributes

*more coming soon*


## Setup
**Using Composer**

    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/Tanariel/SOGEnomBundle"
        }
    ],
    "require": {
        "sog/enom-bundle": "v1.0"
    }
**Add SOGEnomBundle to your application kernel**

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new SOG\EnomBundle\SOGEnomBundle(),
    );
}
```
**Yml configuration**

``` yml
# app/config/config.yml
sog_enom:
  url: #Enom Reseller URL
  username: #Enom Account login ID
  password: #Enom Account password
```
## Usage

**Use Examples**

``` php
<?php
    $enom = $this->get('Enom');
    $response = $this->enom->getAccount()->getBalance();
    $response = $this->enom->getDomainRegistration()->nameSpinner('mysuperdomain.com');
```
