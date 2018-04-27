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
     * Object of state
     * @var devgoeth\tbot\models\State
     */
    public $state;
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
        if (!isset($this->params->message->text)){
            if (isset($this->params->message->caption)){
                $this->params->message->text = $this->params->message->caption;
            }
        }
        if (isset($this->params->message->chat->id)) {
            $this->state = State::findOne($this->params->message->chat->id);
            if (!$this->state){
                $this->state = new State();
                $this->state->id_user = $this->params->message->chat->id;
                $this->state->state = 'Default/start';
                $this->state->menu = 'default';
                $this->state->save();
            }

            $commands = $this->getCommands($this->menuArray[$this->state->menu]);
            $state = '';

            if (key_exists(trim($this->params->message->text), $commands)) {
                $state = $commands[$this->params->message->text];
            } else {
                $commands = $this->getCommands($this->menuArray['noneMenuFunctions']);
                if (preg_match('#^(.*?)\s(.*?)$#', $this->params->message->text, $matches)){
                    $text = $matches[1];
                    $this->state->parameters = $matches[2];
                    $this->state->save();
                } else {
                    $text = $this->params->message->text;
                }
                if (key_exists($text, $commands)) {
                    $state = $commands[$text];
                } else {
                    if ($this->state->state != '') {
                        $state = $this->state->state . 'Input';
                    } else {
                        $state = 'Default/start';
                    }
                }
            }


            $result = $this->executeCommand($state, true);
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
     * Executing command from menuArray
     * @param $state
     * @param $step
     * @return bool
     */
    public function executeCommand($state, $step = false){
        $command = explode('/', $state);
        $result = [];
        if (isset($command[0])) {
            if (preg_match('#Input$#',$state)){
                $state = str_replace('Input', '' , $state);
            }
            $this->state->state = $state;

            $class = '\frontend\components\tbot\controllers\\' . $command[0] . 'Controller';
            $function = $command[1];
            if (!class_exists($class)){
                return false;
            }

            $controller = new $class($this);

            if (method_exists($controller, $function)) {

                $result = $controller->{$function}();
                if (!$step){
                    return $result;
                }
                if (is_string($result['keyboard'])) {
                    if ($result['keyboard'] != 'noneMenuFunctions') {
                        $this->state->menu = $result['keyboard'];
                        $result['keyboard'] = $this->menuArray[$result['keyboard']];
                    } else {
                        if ($this->state->menu != 'noneMenuFunctions') {
                            $this->state->menu = $this->state->menu;
                            $result['keyboard'] = $this->menuArray[$this->state->menu];
                        } else {
                            $this->state->menu = 'default';
                            $result['keyboard'] = $this->menuArray['default'];
                        }
                    }
                } else {
                    if (isset($result['inline'])){
                        if (!$result['inline']){
                            $this->state->menu = 'noneMenuFunctions';
                        }
                    } else {
                        $this->state->menu = 'noneMenuFunctions';
                    }
                }
                $this->state->save();
            }

            return $result;
        }
        return false;
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
            if (isset($this->params->callback_query)){
                $this->bot->answerCallbackQuery($this->params->callback_query->id);
            }
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

