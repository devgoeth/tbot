# Installation

``` bash
composer require devgoeth/tbot
```

#### Execute migration
``` bash
cd /path/to/project
php yii migrate --migrationPath=./vendor/devgoeth/tbot/migrations --interactive=0
```

# Overview
Migration create Folders and Files:
```
/var/www/telegram/frontend/components/tbot
/var/www/telegram/frontend/components/tbot/config/menu.php
/var/www/telegram/frontend/components/tbot/config/params.php
/var/www/telegram/frontend/components/tbot/controllers/DefaultController.php
```

## Examples
You might view example with migration.

#### 1. Step
Edit /var/www/telegram/frontend/components/tbot/config/params.php and write your apibot token in array of params. 

``` php
<?php
return [
    'token' => ''
];
```
#### 2. Step

Set Webhook for your bot, for example.
``` php
$url = 'https://' . $_SERVER['SERVER_NAME'] . '/test/web-hook';
$crt = './../../ssl/bundle.crt';

$bot = new \devgoeth\tbot\Base();
$bot->setWebHook($url, $crt);
```

And now telegram will be send data to your web-hook action

#### 3. Step

Use web-hook in Yii controller action
``` php
public function actionWebHook(){
    date_default_timezone_set("Europe/Moscow");
    $bot = new \devgoeth\tbot\Base();
    $bot->webHook();
}
```

#### 4. Step

Edit menu array for your buttons

/var/www/telegram/frontend/components/tbot/config/menu.php

For Example:
``` php
<?php
return [
	'noneMenuFunctions' => [
		['/start' => 'Default/start'],
		['/start2' => 'Default/start'],
	],
	'default' => [
		['The Button' => 'Default/button'],
		[
			'The Wizard' => 'Default/wizard',
			'The Input' => 'Default/input'
		],
	]
];
```
Where 'Label for Button' => 'controllerName/functionName', all function which execute whithout menu must be in 'noneMenuFunction' array


### Inline mode

Your can turn on inline mode in message

``` php
public function start(){
	return [
		'message' => 'Welcome to bot',
		'keyboard' => [
			[
				['text' => 'Label for button', 'callback_data' => 'command']
			]
		],
		'inline' => true
	];
}
```

or just link

``` php
public function start(){
	return [
		'message' => 'Welcome to bot',
		'keyboard' => [
			[
				['text' => 'Google', 'url' => 'https://google.com']
			]
		],
	'inline' => true
	];
}
```
### Input mode

In tbot/controllers/DefaultController.php (Don't forget create button 'The Input' => 'Default/input' in menu.php)
You must add prefix Input for your function and it will be execute after main function.

``` php
public function input(){
    return [
        'message' => 'Input value, pls',
        'keyboard' => 'default',
    ];
}

public function inputInput(){
    return [
        'message' => 'Your value ' . $this->params->message->text,
        'keyboard' => 'default',
    ];
}
```

### Wizard Mode

You can execute command from function in tbot Controllers and create step by step wizards

``` php
public function wizard(){
    return $this->base->executeCommand('Default/input');
}
```
