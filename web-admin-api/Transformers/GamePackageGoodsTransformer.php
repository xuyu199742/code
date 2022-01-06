<?php
/*签到礼包配置表*/
namespace Transformers;
use League\Fractal\TransformerAbstract;
use Models\Platform\GamePackage;
use Models\Platform\GamePackageGoods;

class GamePackageGoodsTransformer extends TransformerAbstract
{
    protected $availableIncludes = ['package'];

    public function transform(GamePackageGoods $item)
    {
        return [
            'GoodsID'           => $item->GoodsID,
            'PackageID'         => $item->PackageID,
            'TypeID'            => $item->TypeID,
            'PropertyID'        => $item->PropertyID,
            'GoodsNum'          => realCoins($item->GoodsNum),
            'ResourceURL'       => $item->ResourceURL,
            'CollectDate'       => $item->CollectDate,
            'TypeIDText'        => $item->TypeIDText,
        ];
    }

    /*关联签到礼包*/
    public function includePackage(GamePackageGoods $item)
    {
        if(isset($item->package)){
            return $this->item($item->package,new GamePackageTransformer());
        }else{
            return $this->primitive(new GamePackage(),new GamePackageTransformer);
        }
    }

}