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

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
        $loader->load('compressors.xml');
        $loader->load('preservers.xml');
    }
}
