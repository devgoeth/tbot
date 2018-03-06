<?php
namespace devgoeth\tbot;

use devgoeth\tbot\models\State;
use yii\httpclient\Client;

/**
 * Class Base for Telegram Bots
 * @package devgoeth\tbot
 */
class Base
{
    /**
     * Object of TelegramBot\Api\BotApi
     * @var TelegramBot\Api\BotApi
     */
    public $bot;

    /**
     * Params received from Telegram
     * @var mixed
     */
    public $params;

    /**
     * Params from config
     * @var mixed
     */
    public $config;

    /**
     * Array of menus
     * @var array|mixed
     */
    public $menuArray = [
        'mainMenu' => []
    ];

    /**
     * User's first name
     * @var string
     */
    public $firstName;

    /**
     * User's last name
     * @var string
     */
    public $lastName;

    /**
     * User's nickname
     * @var string
     */
    public $username;

    /**
     * Default mark up
     * @var string
     */
    public $markUp = 'html';

    /**
     * Base constructor.
     */
    public function __construct()
    {
        $this->menuArray = require(\Yii::getAlias('@frontend') . '/components/tbot/config/menu.php');
        $this->config = require(\Yii::getAlias('@frontend') . '/components/tbot/config/params.php');
    }

    /**
     * Base function for validate web-hook and execute command
     */
    public function webHook(){
        $request = \Yii::$app->request;
        $this->params = json_decode($request->rawBody);

        $result['keyboard'] = $this->menuArray['default'];
        $result['inline'] = false;
        $result['message'] = '';

        if (!isset($this->params->message)){
            if (isset($this->params->callback_query)){
                $this->params->message = $this->params->callback_query->message;
            }
        }

        $this->firstName = isset($this->params->message->from->first_name)? $this->params->message->from->first_name : '';
        $this->lastName = isset($this->params->message->from->last_name)? $this->params->message->from->last_name : '';
        $this->username = isset($this->params->message->from->username) ? $this->params->message->from->username : '';

        if (isset($this->params->callback_query->data)){
            $this->params->message->text = $this->params->callback_query->data;
        }

        if (isset($this->params->message->chat->id)) {
            $state = State::findOne($this->params->message->chat->id);
            if (!$state){
                $state->id_user = $this->params->message->chat->id;
                $state->state = 'bot/start';
                $state->menu = 'default';
                $state->save();
            }

            $commands = $this->getCommands($this->menuArray[$state->menu]);

            if (key_exists(trim($this->params->message->text), $commands)) {
                $command = explode('/',$commands[$this->params->message->text]);
            } else {
                $commands = $this->getCommands($this->menuArray['noneMenuFunctions']);
                if (key_exists(trim($this->params->message->text), $commands)) {
                    $command = explode('/',$commands[$this->params->message->text]);
                }
            }

            if (isset($command[0])) {
                $class = '\frontend\components\tbot\controllers\\' . $command[0] . 'Controller';
                $function = $command[1];

                $controller = new $class($this);

                if (method_exists($controller, $function)) {
                    $result = $controller->{$function}();
                }
            }
        } else {
            $result['message'] = 'Bot has some error, pls, send it to administrator';
        }

        if (!empty($result['message'])) {
            if (!isset($result['inline'])) {
                $result['inline'] = false;
            }
            $result['keyboard'] = ($result['inline'])? $result['keyboard'] : $this->toKeyboard($result['keyboard']);
            $this->send(
                $result['message'],
                $result['keyboard'],
                $result['inline']
            );
        }

        die();
    }

    /**
     * Turn bot menu to keyboard
     * @param $keyboards
     * @return array
     */
    private function toKeyboard($keyboards){
        $keyboard = [[]];
        foreach ($keyboards as $key => $line){
            foreach ($line as $keyItem => $item){
                $keyboard[$key][] = $keyItem;
            }
        }
        return $keyboard;
    }
    /**
     * Turn array in list
     * @param $menus
     * @return array
     */
    private function getCommands ($menus){
        $commands = [];
        foreach ($menus as $items) {
            foreach ($items as $key => $item){
                $commands[$key] = $item;
            }
        }

        return $commands;
    }

    /**
     * Sending message to telegram
     * @param $message
     * @param null $keyboard
     * @param $inline
     */
    private function send($message, $keyboard = null, $inline = false ){
        try {
            $this->bot = new \TelegramBot\Api\BotApi($this->config['token']);
            if ($inline){
                $keyboard = new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($keyboard);
            } else {
                $keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup($keyboard, false, true);
            }
            $this->bot->sendMessage($this->params->message->chat->id, $message, $this->markUp, false, null, $keyboard);
        } catch (\TelegramBot\Api\Exception $e) {

        }
    }

    /**
     * Setting web-hook for telegram
     * @param $url
     * @param string $crt
     */
    public function setWebHook($url, $crt = './../../ssl/bundle.crt'){
        $client = new Client();
        $response = $client->createRequest()
            ->setMethod('post')
            ->setUrl('http://api.telegram.org/bot' . $this->config['token'] .
                '/setWebhook?url=' . $url)
            ->addFile('certificate', $crt)
            ->send();
        if ($response->isOk) {
            if ($response->data['ok'] != true) {
                $message = "Error !ok: ". var_dump($response->data);
            } else {
                $message = "Webhook is already set" ;
            }
        } else {
            $message = "Error telegram: " . $response->headers['http-code'];
        }
        echo $message;
    }
}

