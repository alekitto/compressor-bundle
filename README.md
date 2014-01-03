## KCS Compressor bundle for Symfony2

### Requirements:

* Symfony2
* Doctrine 2

### Installation:


* Include this bundle in your composer.json

```bash
$ php composer.phar require kcs/compressor-bundle dev-master
```

Enable the bundle in your AppKernel.php
```php
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            ...
            new Kcs\CompressorBundle\KcsCompressorBundle(),
            ...
        );
        ...
    }
}
```

* Enjoy!

You can simply disable the compressor by setting this in your config.yml:

```yml
kcs_compressor:
    enabled: false
```
