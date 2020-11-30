<?php
declare(strict_types=1);

namespace restFee\components;

use LogicException;
use restFee\models\Config as ConfigModel;
use UnexpectedValueException;
use yii\base\Component;

class Config extends Component
{
    /** @var array|string[] */
    private $params;

    public function init()
    {
        parent::init();
        $this->params = ConfigModel::find()
            ->select(['val', 'name'] )
            ->indexBy('name')
            ->column();
    }

    /**
     * @param string $paramName
     * @return string param value
     */
    public function get(string $paramName): string
    {
        if (!isset($this->params[$paramName])) {
            throw new UnexpectedValueException("Param with name $paramName not found");
        }
        return $this->params[$paramName];
    }

    /**
     * @param string $paramName
     * @param string $value
     */
    public function set(string $paramName, string $value)
    {
        $param = ConfigModel::findOne(['name' => $paramName]);
        if (!$param) {
            throw new UnexpectedValueException("Param with name $paramName not found");
        }
        $this->params[$paramName] = $value;
        $param->val = $value;
        if (!$param->save()) {
            throw  new LogicException('Error save.');
        }
    }
}
