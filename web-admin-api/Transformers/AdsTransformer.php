<?php
/*  广告管理 */

namespace Transformers;

use League\Fractal\TransformerAbstract;
use Models\AdminPlatform\Ads;


class AdsTransformer extends TransformerAbstract
{
    //广告管理
    public function transform(Ads $item)
    {
        if (isset($item->resource_url)) {
            $item->img_url = asset('storage/' . $item->resource_url);
        }
        if (!is_numeric($item->link_url)) {
            $item->url = $item->link_url;
            $item->link_url = Ads::WEBSITE;
        }
        return $item->toArray();
    }

}
