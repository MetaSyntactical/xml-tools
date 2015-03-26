MetaSyntactical XML Tools
=========================

[![Build Status](https://img.shields.io/travis/MetaSyntactical/xml-tools.svg?style=flat-square)](https://travis-ci.org/MetaSyntactical/xml-tools)
[![Downloads this Month](https://img.shields.io/packagist/dm/metasyntactical/xml-tools.svg?style=flat-square)](https://packagist.org/packages/metasyntactical/xml-tools)
[![Latest stable](https://img.shields.io/packagist/v/metasyntactical/xml-tools.svg?style=flat-square&label=stable)](https://packagist.org/packages/metasyntactical/xml-tools)
[![Latest dev](https://img.shields.io/packagist/vpre/metasyntactical/xml-tools.svg?style=flat-square&label=unstable)](https://packagist.org/packages/metasyntactical/xml-tools)

Several tools for handling of XML files in PHP. Currently includes the following tools:

- XML Stream Reader (with callable ability)

Install
-------

### Using Composer

Require the library using composer:

```bash
$ composer require metasyntactical/xml-tools
```

Usage
-----

### XML Stream Reader

```php

use MetaSyntactical\Xml\Reader\XmlStreamReader;
use MetaSyntactical\Xml\Reader\XmlPath;
use DOMElement;
use MetaSyntactical\Xml\XmlStream\FileXmlStream;

$reader = new XmlStreamReader();

// register callables
$reader->registerCallback(
    new XmlPath("/example/node"),
    function (DOMElement $element) {
        echo "Match";
    }
}

// parse file
$reader->parse(new FileXmlStream("/path/to/xml/file.xml");
```

Contribute
----------

You are very welcome to contribute to this component. Please follow the information found in (CONTRIBUTE.md)[CONTRIBUTE.md].
