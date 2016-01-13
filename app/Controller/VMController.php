<?php
/**
 * Created by PhpStorm.
 * User: raghav
 * Date: 06/01/16
 * Time: 2:22 PM
 */

App::uses('Ravello', 'Lib');

class VMController extends AppController
{

    public function create(){
        $this->autoRender = false;
        $ravello = new Ravello();
        $ipAddress = $ravello->createVM();
        echo $ipAddress;
    }
}