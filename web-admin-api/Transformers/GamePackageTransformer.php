<?php
/*签到礼包配置表*/
namespace Transformers;
use League\Fractal\TransformerAbstract;
use Models\Platform\GamePackage;

class GamePackageTransformer extends TransformerAbstract
{
    public function transform(GamePackage $item)
    {
        return [
            'PackageID'         => $item->PackageID,
            'Name'              => $item->Name,
            'TypeID'            => $item->TypeID,
            'SortID'            => $item->SortID,
            'Nullity'           => $item->Nullity,
            'PlatformKind'      => $item->PlatformKind,
            'Describe'          => $item->Describe,
            'CollectDate'       => $item->CollectDate,
            'TypeIDText'        => $item->TypeIDText,
            'NullityText'       => $item->NullityText,
            'PlatformKindText'  => $item->PlatformKindText,
        ];
    }

}