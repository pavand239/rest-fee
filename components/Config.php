<?php
declare(strict_types=1);

namespace restFee\components;

use UnexpectedValueException;
use yii\base\Component;
use yii\helpers\ArrayHelper;

class Config extends Component
{
    private $params;

    public function init()
    {
        parent::init();
        $this->params = ArrayHelper::map(\restFee\models\Config::find()->all(),'name','val');
    }

    /**
     * @param string $paramName
     * @return string param value
     */
    public function get(string $paramName): string {
        if (!isset($this->params[$paramName])) {
            throw new UnexpectedValueException("Param with name $paramName not found");
        }
        return $this->params[$paramName];
    }

    /**
     * @param string $paramName
     * @param string $value
     */
    public function set(string $paramName, string $value) {
        $param = \restFee\models\Config::findOne(['name' => $paramName]);
        if (!$param) {
            throw new UnexpectedValueException("Param with name $paramName not found");
        }
        $this->params[$paramName] = $value;
        $param->val = $value;
        $param->save();
    }
}
