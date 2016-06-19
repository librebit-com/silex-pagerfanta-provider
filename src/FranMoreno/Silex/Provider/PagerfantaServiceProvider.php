<?php

namespace FranMoreno\Silex\Provider;

use Pimple\ServiceProviderInterface;
use Pimple\Container;
use Silex\Api\BootableProviderInterface;
use Silex\Application;

use Pagerfanta\View\DefaultView;
use Pagerfanta\View\TwitterBootstrapView;
use Pagerfanta\View\TwitterBootstrap3View;
use Pagerfanta\View\ViewFactory;
use FranMoreno\Silex\Service\PagerfantaFactory;
use FranMoreno\Silex\Twig\PagerfantaExtension;

class PagerfantaServiceProvider implements ServiceProviderInterface, BootableProviderInterface
{
    public function register(Container $container)
    {
        $container['pagerfanta.pager_factory'] = $container->factory(function ($container) {
            return new PagerfantaFactory();
        });

        $container['pagerfanta.view.default_options'] = array(
            'routeName'        => null,
            'routeParams'      => array(),
            'pageParameter'    => '[page]',
            'proximity'        => 3,
            'next_message'     => '&raquo;',
            'previous_message' => '&laquo;',
            'default_view'     => 'default'
        );

        $container['pagerfanta.view_factory'] = $container->factory(function ($container) {
            $defaultView = new DefaultView();
            $twitterBoostrapView = new TwitterBootstrapView();
            $twitterBoostrap3View = new TwitterBootstrap3View();

            $factoryView = new ViewFactory();
            $factoryView->add(array(
                $defaultView->getName() => $defaultView,
                $twitterBoostrapView->getName() => $twitterBoostrapView,
                $twitterBoostrap3View->getName() => $twitterBoostrap3View,
            ));

            return $factoryView;
        });

        if (isset($container['twig'])) {
            $container['twig'] = $container->factory(
              $container->extend('twig', function ($twig, $app) {
                    $twig->addExtension(new PagerfantaExtension($app));

                    return $twig;
                })
            );
        }
    }

    public function boot(Application $app)
    {
        $options = isset($app['pagerfanta.view.options']) ? $app['pagerfanta.view.options'] : array();
        $app['pagerfanta.view.options'] = array_replace($app['pagerfanta.view.default_options'], $options);
    }
}
