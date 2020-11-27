<?php

namespace restFee\controllers;

use yii\rest\Controller;

class FeeController extends Controller
{
    public function actionIndex() {
        return ['message' => 'Hello World'];
    }
}