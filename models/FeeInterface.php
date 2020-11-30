<?php
declare(strict_types=1);

namespace restFee\models;

interface FeeInterface
{
    /**
     * @return array ['recommendedFee' => int]
     */
    public function getRecommendedFee(): array;

    /**
     * @return array ['currentLoad' => int]
     */
    public function getCurrentLoad(): array;

    /**
     * @return integer
     */
    public function getRecommendedFeeFromApi(): int;

    /**
     * @return array
     */
    public function getMempoolFromApi(): array;

    /**
     * @return float
     */
    public function getCurrentMempoolWeight(): float;

    /**
     * @return array
     */
    public function getBlocksMinFee(): array;
}
