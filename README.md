# Installation

``` bash
cd /path/to/project
composer require devgoeth/tbot @dev
```

#### Execute migration
``` bash
php yii migrate --migrationPath=./vendor/devgoeth/tbot/migrations --interactive=0
```

# Overview
Migration create Folders and Files:
```
/frontend/components/tbot
/frontend/components/tbot/config/menu.php
/frontend/components/tbot/config/params.php
/frontend/components/tbot/controllers/DefaultController.php
```

## Examples
You might view example with migration.

#### 1. Step
Edit /frontend/components/tbot/config/params.php and write your apibot token in array of params. 

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

Use web-hook in Yii controller action. Don't forget disable csrf validation for your web-hook action
``` php
public function actionWebHook(){
    $bot = new \devgoeth\tbot\Base();
    $bot->webHook();
}
```

#### 4. Step

Edit menu array for your buttons

/frontend/components/tbot/config/menu.php

For Example:
``` php
<?php
return [
	'noneMenuFunctions' => [
		['/start' => 'Default/start'],
		['/other' => 'Default/start'],
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

All controllers for menu array must be in tbot/controllers


### Inline mode

Your can turn on inline mode in message. In tbot Controller function.

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

### Base

In Controller you can use base parameter which contain all base parameters include object of TelegramBot\Api https://github.com/TelegramBot/Api

``` php
public function myMessage(){
	$keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup(array(array("one", "two", "three")), false);
	$message = 'It\'s awesome';
	
	// $this->base->markUp = 'html' by default; 
	$this->base->bot->sendMessage(
		$this->params->message->chat->id, 
		$message, $this->base->markUp, 
		false, 
		null, 
		$keyboard
	);
	return [
		'message' => 'Input value, pls',
		'keyboard' => 'default',
	];
}
```

You can access previus comand's parameters.

``` php
$this->base->state->parameters;
```

You can send message 

``` php
$this->base->send($text);
```
Also you can disappear keyboard menu. In tbot action use and next message disappear keyboard

``` php
$this->base->visible = true;
```
