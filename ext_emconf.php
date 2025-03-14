<?php

$EM_CONF['jar_columnrow'] = array(
	'title' => 'JAR Column Row',
	'description' => 'Provides a freely definable and universal grid element with bootstrap-based output.',
	'category' => 'plugin',
	'author' => 'invokable GmbH',
	'author_email' => 'info@invokable.gmbh',
	'version' => '2.0.0',
	'state' => 'stable',
	'internal' => '',
	'uploadfolder' => '0',
	'createDirs' => '',
	'clearCacheOnLoad' => 0,
	'constraints' => array(
		'depends' => array(
			'typo3' => '12.4.1-12.4.99',
			'php' => '8.1.999-8.4.99',
			'container' => '^3.0'
		),
		'conflicts' => array(),
		'suggests' => array(), 
	),
);
