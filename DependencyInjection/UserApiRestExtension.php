<?php
namespace EveryCheck\UserApiRestBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class UserApiRestExtension extends Extension
{
	public function load(array $config, ContainerBuilder $container)
	{

		$configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $config);
        $container->setParameter('user_api_rest.generate_password', $config['generate_password']);

		$loader = new YamlFileLoader($container, new FileLocator(
			[
				__DIR__ . '/../Resources/config/'
			])
		);
		$loader->load('services.yml');
	}
}