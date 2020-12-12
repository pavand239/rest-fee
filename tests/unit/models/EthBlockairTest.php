<?php
declare(strict_types=1);

namespace tests\unit\models;

class EthBlockchairTest extends AbstractFeeTest
{
    public function _before()
    {
        $this->feeServiceClassName = 'restFee\models\EthBlockchair';
        parent::_before();
    }
}