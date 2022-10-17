<?php

use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use Cake\Routing\Route\DashedRoute;

Router::scope('/', function (RouteBuilder $routes) {
    $routes->connect(
        '/seo-filter/render/:slug_seo_filter',
        ['controller' => 'Render', 'action' => 'index', 'plugin' => 'SeoFilter']
    );

    $filtres = \Cake\ORM\TableRegistry::get('SeoFilter.SeofilterFilters')->find()->all();

    foreach ($filtres as $filtre) {
        $routes->connect(
            '/' . $filtre->slug,
            ['controller' => $filtre->controller, 'action' => $filtre->action, 'slug_seo_filter' => $filtre->slug],
            ['_name' => $filtre->slug . '_empty']
        );
        $routes->connect(
            '/' . $filtre->slug . '/{filtres}',
            ['controller' => $filtre->controller, 'action' => $filtre->action, 'slug_seo_filter' => $filtre->slug],
            ['_name' => $filtre->slug, 'filtres' => '[_a-zA-Z0-9\s\/\+:-]+']
        );
    }
    $routes->fallbacks(DashedRoute::class);
});
