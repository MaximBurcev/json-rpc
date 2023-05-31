<?php

namespace App;

class JsonRPC {



    public function substract($params) {
        return [
            'result' => $params[0] - $params[1]
        ];
    }

    public function add($params) {
        return [
            'result' => $params[0] + $params[1]
        ];
    }
}
