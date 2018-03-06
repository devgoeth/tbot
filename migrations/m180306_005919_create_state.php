<?php

use yii\db\Migration;

class m180306_005919_create_state extends Migration
{

    public function up()
    {
        $this->createTable('{{%state}}', [
            'id_user' => $this->integer()->notNull(),
            'state' => $this->string()->notNull(),
            'menu' => $this->string()->notNull(),
            'date' => $this->timestamp()->notNull(),
        ], null);
        $this->addPrimaryKey('id_user_index', 'state', 'id_user');

        $components = Yii::getAlias('@frontend') . '/components';
        $tbot = Yii::getAlias('@frontend') . '/components/tbot';
        $config = Yii::getAlias('@frontend') . '/components/tbot/config';
        $controllers = Yii::getAlias('@frontend') . '/components/tbot/controllers';

        $configMenu = Yii::getAlias('@frontend') . '/components/tbot/config/menu.php';
        $configParams = Yii::getAlias('@frontend') . '/components/tbot/config/params.php';

        $controllersExample = Yii::getAlias('@frontend') . '/components/tbot/controllers/DefaultController.php';

        if (!file_exists($components)){
            mkdir($components);
        }
        if (!file_exists($tbot)){
            mkdir($tbot);
        }
        if (!file_exists($config)){
            mkdir($config);
        }
        if (!file_exists($controllers)){
            mkdir($controllers);
        }

        if (!file_exists($configMenu)){
            $file = "<?php\n" . "return [\n" . "\t'noneMenuFunctions' => [\n" . "\t\t['/start' => 'Default/start']\n" .
                "\t],\n" . "\t'default' => [\n" . "\t\t['The Button start' => 'Default/start']\n" .
                "\t],\n" . "];";
            file_put_contents($configMenu, $file);
        }
        if (!file_exists($configParams)){
            $file = "<?php\n" . "return [\n" . "    'token' => ''\n" . "];";
            file_put_contents($configParams, $file);
        }

        if (!file_exists($controllersExample)){
            $file = "<?php\n" . "namespace frontend\\components\\tbot\\controllers;\n\n" .
            "use devgoeth\\tbot\\Controller;\n\n" .
            "class DefaultController extends Controller\n{\n" . "\tpublic function start(){\n" .
            "\t\treturn [\n" . "\t\t\t'message' => 'Welcome to bot',\n" .
            "\t\t\t'keyboard' => 'default'\n" . "\t\t];\n" . "\t}\n" . "};";
            file_put_contents($controllersExample, $file);
        }
        
    }

    public function down()
    {
        echo "m180306_005919_create_state cannot be reverted.\n";

        return false;
    }

}
