<?php
/* 轮播广告*/

namespace Transformers;

use League\Fractal\TransformerAbstract;
use Models\AdminPlatform\CarouselAffiche;


class CarouselAfficheTransformer extends TransformerAbstract
{
    public function transform(CarouselAffiche $item)
    {
        return [
            'id'            => $item->id,
            'type'          => $item->type,
            'sort'          => $item->sort,
            'image'         => $item->image,
            'link'          => $item->link,
            'created_at'    => date('Y-m-d H:i:s',strtotime($item->created_at)) ?? '',
            'updated_at'    => date('Y-m-d H:i:s',strtotime($item->updated_at)) ?? '',

            'image_text'    => asset('storage/'.$item->image),
            'type_text'     => $item->type_text,
        ];
    }

}