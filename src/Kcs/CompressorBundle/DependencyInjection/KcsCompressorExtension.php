<?php

namespace Kcs\CompressorBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

use Symfony\Component\DependencyInjection\Definition;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class KcsCompressorExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        foreach ($config as $key => $value) {
            if ($value === null) {
                continue;
            }

            $container->setParameter('kcs_compressor.' . $key, $value);
        }

        $this->setJsCompressorClass($container, $config);
        $this->setCssCompressorClass($container, $config);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
        $loader->load('compressors.xml');
        $loader->load('preservers.xml');
    }

    private function setJsCompressorClass(ContainerBuilder $container, $config) {
        $arguments = array();
        switch ($config['js_compressor']) {
            case 'none':
                $class = 'Kcs\CompressorBundle\Compressor\NoneCompressor';
                break;

            case 'yui':
                $class = 'Kcs\CompressorBundle\Js\YuiCompressor';
                if (!($jarPath = $container->getParameterBag()->resolveValue('%kcs_compressor.yui_jar%'))) {
                    throw new \RuntimeException('kcs_compressor.yui_jar must be specified if using yui compressor');
                }

                $container->setParameter('kcs_compressor.inline_js_compressor.class', $class);

                $arguments[] = $jarPath;
                if ($container->hasParameter('kcs_compressor.java_path') &&
                        ($javaPath = $container->getParameterBag()->resolveValue('%kcs_compressor.java_path%'))) {
                    $arguments[] = $javaPath;
                }
                break;

            case 'custom':
                $class = $config['js_compressor_class'];
                break;
        }

        $container->setDefinition('kcs_compressor.inline_js_compressor', new Definition($class, $arguments));
    }

    private function setCssCompressorClass(ContainerBuilder $container, $config) {
        $arguments = array();
        switch ($config['css_compressor']) {
            case 'none':
                $class = 'Kcs\CompressorBundle\Compressor\NoneCompressor';
                break;

            case 'yui':
                $class = 'Kcs\CompressorBundle\Css\YuiCompressor';
                if (!($jarPath = $container->getParameterBag()->resolveValue('%kcs_compressor.yui_jar%'))) {
                    throw new \RuntimeException('kcs_compressor.yui_jar must be specified if using yui compressor');
                }

                $container->setParameter('kcs_compressor.inline_css_compressor.class', $class);

                $arguments[] = $jarPath;
                if ($container->hasParameter('kcs_compressor.java_path') &&
                        ($javaPath = $container->getParameterBag()->resolveValue('%kcs_compressor.java_path%'))) {
                    $arguments[] = $javaPath;
                }
                break;

            case 'custom':
                $class = $config['css_compressor_class'];
                break;
        }

        $container->setDefinition('kcs_compressor.inline_css_compressor', new Definition($class, $arguments));
    }
}
