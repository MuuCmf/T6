<?php
namespace app\common\model;

class Address extends Base {
    protected $autoWriteTimestamp = true;
    function __construct(array $data = [])
    {
        parent::__construct($data);
    }
}