<?php
/**
 * Created by PhpStorm.
 * User: raghav
 * Date: 08/01/16
 * Time: 11:41 PM
 */

//App::uses('Users', 'Lib');
App::uses('Ravello', 'Lib');

class HelloShell extends AppShell {

    public function main() {

        $ravello =& new Ravello();
        Cache::write('bufferVM', $ravello->createVM());
    }
}