<?php

namespace app\common\service;

use think\Exception;

/**
 * 雪花算法生成唯一ID
 * 创建雪花算法实例
 * $datacenterId = 1; // 数据中心ID
 * $machineId = 1;    // 机器ID
 * $snowflake = new Snowflake($datacenterId, $machineId);
 * $userId = $snowflake->nextId();
 */
class Snowflake
{
    private int $datacenterId; // 数据中心ID
    private int $machineId;    // 机器ID
    private int $sequence = 0; // 序列号
    private int $lastTimestamp = -1; // 上一个时间戳

    private const DATA_CENTER_ID_BITS = 5; // 数据中心ID所占位数
    private const MACHINE_ID_BITS = 5;      // 机器ID所占位数
    private const SEQUENCE_BITS = 12;        // 序列号所占位数

    private const MAX_DATA_CENTER_ID = -1 ^ (-1 << self::DATA_CENTER_ID_BITS);
    private const MAX_MACHINE_ID = -1 ^ (-1 << self::MACHINE_ID_BITS);
    
    private const TIMESTAMP_LEFT_SHIFT = self::SEQUENCE_BITS + self::MACHINE_ID_BITS + self::DATA_CENTER_ID_BITS;
    private const SEQUENCE_LEFT_SHIFT = self::MACHINE_ID_BITS + self::DATA_CENTER_ID_BITS;

    private int $epoch; // 起始时间戳

    public function __construct(int $datacenterId, int $machineId)
    {
        if ($datacenterId < 0 || $datacenterId > self::MAX_DATA_CENTER_ID) {
            throw new Exception("数据中心ID超出范围");
        }

        if ($machineId < 0 || $machineId > self::MAX_MACHINE_ID) {
            throw new Exception("机器ID超出范围");
        }

        $this->datacenterId = $datacenterId;
        $this->machineId = $machineId;
        $this->epoch = 1609430400000; // 自定义起始时间（例如2021年1月1日）
    }

    public function nextId(): int
    {
        $timestamp = $this->currentTimeMillis();

        if ($timestamp < $this->lastTimestamp) {
            throw new Exception("错误：系统时钟发生回拨");
        }

        if ($this->lastTimestamp === $timestamp) {
            $this->sequence = ($this->sequence + 1) & ((1 << self::SEQUENCE_BITS) - 1);
            if ($this->sequence === 0) {
                $timestamp = $this->waitNextMillis($timestamp);
            }
        } else {
            $this->sequence = 0;
        }

        $this->lastTimestamp = $timestamp;

        return (($timestamp - $this->epoch) << self::TIMESTAMP_LEFT_SHIFT) |
               ($this->datacenterId << self::SEQUENCE_LEFT_SHIFT) |
               ($this->machineId << self::SEQUENCE_BITS) |
               $this->sequence;
    }

    private function waitNextMillis(int $lastTimestamp): int
    {
        $timestamp = $this->currentTimeMillis();
        while ($timestamp <= $lastTimestamp) {
            $timestamp = $this->currentTimeMillis();
        }
        return $timestamp;
    }

    private function currentTimeMillis(): int
    {
        return (int)(microtime(true) * 1000);
    }
}
