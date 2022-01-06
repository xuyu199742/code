<?php
/* 轮播网址*/

namespace Transformers;

use League\Fractal\TransformerAbstract;
use Models\AdminPlatform\CarouselWebsite;


class CarouselWebsiteTransformer extends TransformerAbstract
{
    public function transform(CarouselWebsite $item)
    {
        return $item->toArray();
    }

}