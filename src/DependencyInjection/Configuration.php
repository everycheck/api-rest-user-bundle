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
			->children()
					->booleanNode('generate_password')
						->isRequired()
					->end()
				->end()
			->end()
		;

		return $treeBuilder;
	}				
}

?>