<?php
declare(strict_types=1);

namespace tests\unit\models;

class BitcoinCashNodeFeeTest extends AbstractFeeTest
{
    public function _before()
    {
        $this->feeServiceClassName = 'restFee\models\BitcoinCashNodeFee';
        parent::_before();
    }
}