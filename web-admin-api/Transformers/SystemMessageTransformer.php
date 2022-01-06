<?php
/*系统消息列表*/
namespace Transformers;

use League\Fractal\TransformerAbstract;
use Models\Platform\SystemMessage;


class SystemMessageTransformer extends  TransformerAbstract
{
    public function transform(SystemMessage $item)
    {
        return $item->toArray();
    }
}