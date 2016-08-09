<?php

namespace nacholibre\RichUploaderBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('nacholibre_rich_uploader');

        $rootNode
            ->children()
                ->arrayNode('mappings')
                    ->isRequired()
                    ->prototype('array')
                        ->children()
                            //->integerNode('max_levels')
                            //    ->info('Max category levels for the category type.')
                            //    ->min(1)
                            //    ->max(3)
                            //    ->defaultValue(1)
                            //->end()
                            ->scalarNode('uri_prefix')
                                ->info('The url prefix that is before the image name.')
                                ->cannotBeEmpty()
                                ->defaultValue('/uploads/richuploader/')
                            ->end()
                            ->scalarNode('upload_destination')
                                ->info('Where to upload files.')
                                ->cannotBeEmpty()
                                ->defaultValue('%kernel.root_dir%/../web/uploads/richuploader/')
                            ->end()
                            ->arrayNode('mime_types')
                                ->prototype('scalar')->end()
                                ->defaultValue(['*'])
                                ->cannotBeEmpty()
                            ->end()
                            ->scalarNode('max_size')
                                ->info('Max size for uploaded file.')
                                ->defaultValue('5M')
                                ->cannotBeEmpty()
                            ->end()
                            //->scalarNode('form_class')
                            //    ->info('Form class used when displaying category in admin. Example: AppBundle\Form\ProductCategoryType')
                            //    ->defaultValue('')
                            //->end()
                            //->enumNode('url_type')
                            //    ->info('What type of urls to be used when listing category members.')
                            //    ->values(['slug', 'id', 'slug_id'])
                            //    ->defaultValue('slug')
                            //->end()
                            //->scalarNode('url_prefix')
                            //    ->info('The prefix used when listing category members.')
                            //    ->defaultValue('')
                            //->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
