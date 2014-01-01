## KCS Watchdog bundle for Symfony2

### Requirements:

* Symfony2
* Doctrine 2

### Installation:


* Include this bundle in your composer.json

```javascript
    "require": {
        ...
        "kcs/watchdog-bundle": "dev-master",
        ...
        }
```

* Create the watchdog table on your database
* Enjoy!


If you want to use Doctrine CouchDB ODM you have to add this to your configuration:

```yaml
...

kcs_watchdog:
    db_driver:          orm         # Allowed values "orm" (default), "couchdb"

...
```

You can ignore some exceptions you don't want to log; Example:

```yaml

kcs_watchdog:
    allowed_exceptions:
        - Symfony\Component\HttpKernel\Exception\NotFoundHttpException

```
