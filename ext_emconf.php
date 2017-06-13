<?php

$EM_CONF[$_EXTKEY] = [
	'title' => 'Speed up 404 error pages',
	'description' => 'Help rendering faster 404 error pages',
	'category' => 'misc',
	'state' => 'stable',
	'author' => 'Visol',
	'author_email' => 'fabien.udriot@visol.ch',
	'author_company' => 'Visol',
    'autoload' => [
        'psr-4' => ['Visol\\WiseErrorPage\\' => 'Classes']
    ],
	'constraints' =>
	[
		'depends' =>
		[
			'typo3' => '6.2.0-8.6.99',
        ],
		'conflicts' => '',
		'suggests' =>
		[
			'nc_staticfilecache' => '',
        ],
    ],
];
