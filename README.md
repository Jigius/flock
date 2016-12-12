Jigius FLock
=========

Simple process lock management library.

Installation
------------

Installation is best managed via [Composer](https://getcomposer.org/).

```json
{
    "require": {
        "jigius/flock": "1.0.*"
    }
}
```

Or:

```
composer require jigius/flock=1.0.*
```

Usage
-----

```php
<?php

$lock = new \Jigius\FLock\FLock("lock-name", "/path/to/lock/folder");
if ($lock->acquire()) {
    // Do work here
} else {
    die ("Unable to acquire lock! Make sure no other process is running!");
}

$lock->release();
```

Methods
-------

- bool \Jigius\FLock\FLock::__construct( string $name [, string $path = null ] )
- object \Jigius\FLock\FLock::create( string $name [, string $path = null ] )
- bool \Jigius\FLock\FLock::acquire( [ bool $block = false ] )
- bool \Jigius\FLock\FLock::release()
- bool \Jigius\FLock\FLock::check()
- string \Jigius\FLock\FLock::getOwnerPid()
