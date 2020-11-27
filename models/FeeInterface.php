<?php

namespace restFee\models;

interface FeeInterface
{
    /**
     * @return array ['recommendedFee' => float]
     *
     */
    public static function getRecommendedFee();

    /**
     * @return array ['currentLoad' => string]
     */
    public static function getCurrentLoad();

    /**
     * @return float
     */
    public function getRecommendedFeeFromApi();

    /**
     * @return string
     */
    public function getCurrentLoadFromApi();
}
