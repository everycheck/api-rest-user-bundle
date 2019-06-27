<?php
namespace EveryCheck\UserApiRestBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
	public function getConfigTreeBuilder()
	{
		$treeBuilder = new TreeBuilder();
		$rootNode = $treeBuilder->root('user_api_rest');

		$rootNode
            ->addDefaultsIfNotSet()			
			->children()
					->booleanNode('generate_password')
						->isRequired()
						->defaultValue(true)
					->end()
					->scalarNode('blacklist_provider')
						->isRequired()
						->defaultValue('default_blacklist')
					->end()
				->end()
			->end()
		;

		return $treeBuilder;
	}				
}

?>