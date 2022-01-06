<?php
/*签到配置*/
namespace Transformers;
use League\Fractal\TransformerAbstract;
use Models\Platform\GamePackage;
use Models\Platform\GameSignIn;

class GameSignInTransformer extends TransformerAbstract
{
    protected $availableIncludes = ['package'];

    public function transform(GameSignIn $item)
    {
        return [
            'SignID'            => $item->SignID,
            'TypeID'            => $item->TypeID,
            'PackageID'         => $item->PackageID,
            'Probability'       => $item->Probability,
            'NeedDay'           => $item->NeedDay,
            'SortID'            => $item->SortID,
            'Nullity'           => $item->Nullity,
            'CollectDate'       => $item->CollectDate,
            'TypeIDText'        => $item->TypeIDText,
            'NullityText'       => $item->NullityText,
        ];
    }

    /*关联签到礼包*/
    public function includePackage(GameSignIn $item)
    {
        if(isset($item->package)){
            return $this->item($item->package,new GamePackageTransformer());
        }else{
            return $this->primitive(new GamePackage(),new GamePackageTransformer);
        }
    }

}