<?php

namespace App\Packages\GameFunction;


use Models\Treasure\RecordDrawScore;

class Gf
{
    private $map;

    private $object;

    public function __construct()
    {
        $this->map = config('game_map_function.map');
    }

    public function format(RecordDrawScore $recordDrawScore)
    {
        $kind_id = $recordDrawScore->darwInfo->KindID;
        $class   = $this->map[$kind_id];
        if (class_exists($class)) {
            $object       = new $class;
            $this->object = $object->format($recordDrawScore);
        }
        return $this;
    }

    public function getJetton()
    {
        return $this->object->getJetton();
    }

    public function getArea()
    {
        return $this->object->getArea();
    }

}
