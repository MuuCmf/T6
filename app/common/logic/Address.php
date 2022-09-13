<?php
namespace app\common\logic;

class Address extends Base{
    public function formatData($data){
        $data = $this->setTimeAttr($data);
        $data = $this->setStatusAttr($data);
        return $data;
    }
}