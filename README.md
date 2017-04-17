# Html Compressor bundle for Symfony2

### Requirements:

* Symfony 2 (>= 2.2)

### Installation:

* Include this bundle in your composer.json

```bash
$ php composer.phar require kcs/compressor-bundle dev-master
```

Enable the bundle in your AppKernel.php
```
<?php

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

### Configuration

You can enable or disable preservers and compressors changing one of these flags in your ``` config.yml ```

```yml
kcs_compressor:
    enabled:              true      # Set to false to disable the compressor
    compress_html:        true      # Enable HTML optimizations
    preserve_line_breaks: true      # Enable the line breaks preservation
    remove_comments:      true      # Remove HTML comments while compressing
    remove_extra_spaces:  true      # Remove extra spaces in HTML
    compress_js:          true      # Enable inline js compression
    compress_css:         true      # Enable inline css compression
```

If inline js (or css) compression is enabled you must specify the compressor to be used:

```yml
kcs_compressor:
    js_compressor:        none      # Can be none (disabled), yui or custom
    css_compressor:       none      # Can be none (disabled), yui or custom
```

You can specify a ``` custom ``` class for the inline js and css compressors.
Use the ``` js_compressor_class ``` and the ``` css_compressor_class ``` setting to specify which classes must be used.

The custom inline compressor class must implement the ``` Kcs\CompressorBundle\Compressor\InlineCompressorInterface ``` interface and export a ``` compress ``` public function accepting the uncompressed content as argument and returning the compressed block

### YUI compressor

If the yui compressor is used the YUI jar file location must be specified in the ``` yui_jar ``` setting. You can also change the java executable path modifying the ``` java_path ``` setting. If not specified defaults to ``` /usr/bin/java ```.
