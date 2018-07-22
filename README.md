# synonym-antonym-indonesia
A Package for Getting Synonym or Antonym of word from Indonesian Language

### Source Data
I am using curl to get the dictionary data from http://www.sinonimkata.com

# Installation
For installing this package, you can install using composer quickly with command
```
composer require "satria/synonym-antonym-indonesia":"dev-master"
```
# Using the Package 
Easy to use this package, you just include the ``autoload.php`` file to your ``.php`` file like this
```php
<?php
require_once "vendor/autoload.php";
```
After that, you can use the package by following this code
```php
<?php

use SynonymAntonym\Dictionary

$init = new Dictionary;
$results = $init->word('satria')->synonym();
```

# Example Results
### Success Result
```php
Array
(
    [status] => Array
        (
            [code] => 200
            [description] => OK
        )
    [title] => Sinonim
    [data] => Array
        (
            [0] => ahli
            [1] => anak buah
            [4] => bagian
            ...
        )
)
```

### Error Result
```php
Array
(
    [status] => Array
        (
            [code] => 400
            [description] => Data untuk kata mamam tidak ditemukan
        )
)
```

# Available Methods
I am develop this package with some method that can use for your project application

| Methods        | Description           |
|:-------------:|-------------|
| ``$init->antonym()`` | For getting the antonym data |
| ``$init->synonym()`` | For getting the synonym data |
| ``$init->all()`` | Get all data (antonym and synonym) |
