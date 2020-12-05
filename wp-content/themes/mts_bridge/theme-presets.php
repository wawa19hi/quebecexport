<?php
// make sure to not include translations
$args['presets']['default'] = array(
	'title' => 'Default',
	'demo' => 'http://demo.mythemeshop.com/bridge/',
	'thumbnail' => get_template_directory_uri().'/options/demo-importer/demo-files/default/thumb.jpg', // could use external url, to minimize theme zip size
	'menus' => array( 'primary-menu' => 'Primary', 'secondary-menu' => 'Secondary', 'footer-menu' => 'Footer' ), // menu location slug => Demo menu name
	'options' => array( 'show_on_front' => 'posts' ),
);

$args['presets']['health'] = array(
	'title' => 'Health',
	'demo' => 'http://demo.mythemeshop.com/bridge-health/',
	'thumbnail' => get_template_directory_uri().'/options/demo-importer/demo-files/health/thumb.jpg', // could use external url, to minimize theme zip size
	'menus' => array( 'primary-menu' => 'Primary' ), // menu location slug => Demo menu name
	//'options' => array( 'show_on_front' => 'page', 'page_on_front' => '4' ), // To set static front page
);

$args['presets']['news'] = array(
	'title' => 'News',
	'demo' => 'http://demo.mythemeshop.com/bridge-news/',
	'thumbnail' => get_template_directory_uri().'/options/demo-importer/demo-files/news/thumb.jpg', // could use external url, to minimize theme zip size
	'menus' => array( 'secondary-menu' => 'Secondary' ), // menu location slug => Demo menu name
	//'options' => array( 'show_on_front' => 'page', 'page_on_front' => '4' ), // To set static front page
);

$args['presets']['sports'] = array(
	'title' => 'Sports',
	'demo' => 'http://demo.mythemeshop.com/bridge-sports/',
	'thumbnail' => get_template_directory_uri().'/options/demo-importer/demo-files/sports/thumb.jpg', // could use external url, to minimize theme zip size
	'menus' => array( 'primary-menu' => 'Primary' ), // menu location slug => Demo menu name
	//'options' => array( 'show_on_front' => 'page', 'page_on_front' => '4' ), // To set static front page
);

global $mts_presets;
$mts_presets = $args['presets'];
