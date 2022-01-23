<?php

class A {
    function __call($method,$params) {
        echo "\tVirtual call on $method\n";
        if ($method == 'roar') {
            echo "\tAARR\n";
        } else {
            echo "\toops\n";
        }
    }

} 


class B extends A {


    public function roar() {
        echo "\tBARR\n";
        parent::roar();
    }
}

$a = new A();
echo "A roars like:\n";
$a->roar();
$b = new B();
echo "B roars like:\n";
$b->roar();