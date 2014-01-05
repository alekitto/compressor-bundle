<?php

namespace Kcs\CompressorBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

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
        $container->setParameter('kcs_compressor.enabled', $config['enabled']);
        $container->setParameter('kcs_compressor.compress_html', $config['compress_html']);

        $container->setParameter('kcs_compressor.preserve_line_breaks', $config['preserve_line_breaks']);

        $container->setParameter('kcs_compressor.remove_comments', $config['remove_comments']);
        $container->setParameter('kcs_compressor.remove_extra_spaces', $config['remove_extra_spaces']);
        $container->setParameter('kcs_compressor.compress_js', $config['compress_js']);
        $container->setParameter('kcs_compressor.compress_css', $config['compress_css']);

        $this->setJsCompressorClass($container, $config);
        $this->setCssCompressorClass($container, $config);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
        $loader->load('compressors.xml');
        $loader->load('preservers.xml');
    }

    private function setJsCompressorClass(ContainerBuilder $container, $config) {
        switch ($config['js_compressor']) {
            case 'none':
                $class = 'Kcs\CompressorBundle\Compressor\NoneCompressor';
                break;

            case 'custom':
                $class = $config['js_compressor_class'];
                break;
        }

        $container->setParameter('kcs_compressor.inline_js_compressor.class', $class);
    }

    private function setCssCompressorClass(ContainerBuilder $container, $config) {
        switch ($config['css_compressor']) {
            case 'none':
                $class = 'Kcs\CompressorBundle\Compressor\NoneCompressor';
                break;

            case 'custom':
                $class = $config['css_compressor_class'];
                break;
        }

        $container->setParameter('kcs_compressor.inline_css_compressor.class', $class);
    }
}
