<?php
declare(strict_types=1);

namespace tests\unit\models;

class BchBlockchairTest extends AbstractFeeTest
{
    public function _before()
    {
        $this->feeServiceClassName = 'restFee\models\BchBlockchair';
        parent::_before();
    }
}