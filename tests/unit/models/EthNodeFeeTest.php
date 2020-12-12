<?php
declare(strict_types=1);

namespace tests\unit\models;

class EthNodeFeeTest extends AbstractFeeTest
{
    public function _before()
    {
        $this->feeServiceClassName = 'restFee\models\EthNodeFee';
        parent::_before();
    }
}