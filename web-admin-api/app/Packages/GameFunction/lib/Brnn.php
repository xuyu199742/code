<?php
/*
 |--------------------------------------------------------------------------
 | 八人牛牛
 |--------------------------------------------------------------------------
 | Notes:
 | Class Brnn
 | User: Administrator
 | Date: 2019/7/22
 | Time: 18:48
 |
 |  * @return
 |  |
 |
 */

namespace App\Packages\GameFunction\lib;


use App\Packages\GameFunction\formatInterface;
use Models\Treasure\RecordDrawScore;

class Brnn implements formatInterface
{

    const SQUARE      = [0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0A, 0x0B, 0x0C, 0x0D];//方块 A - K
    const PLUM        = [0x11, 0x12, 0x13, 0x14, 0x15, 0x16, 0x17, 0x18, 0x19, 0x1A, 0x1B, 0x1C, 0x1D];//梅花 A - K
    const RED_HEART   = [0x21, 0x22, 0x23, 0x24, 0x25, 0x26, 0x27, 0x28, 0x29, 0x2A, 0x2B, 0x2C, 0x2D];//红桃 A - K
    const BLACK_HEART = [0x31, 0x32, 0x33, 0x34, 0x35, 0x36, 0x37, 0x38, 0x39, 0x3A, 0x3B, 0x3C, 0x3D];//黑桃 A - K
    const KING        = [0x4E, 0x4F]; //大小王
    const PIKER       = ['A', '2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K'];

    public function format(RecordDrawScore $recordDrawScore)
    {
        //hexdec
        $pikers = explode(',', rtrim(trim($recordDrawScore->CardResult), ','));
        $data   = [];
        foreach ($pikers as $piker) {
            if ($piker) {
                $hex = sprintf('%X', $piker);
                if (in_array($hex, self::SQUARE)) {
                    $data[] = '方块' . self::PIKER[array_search($hex, self::SQUARE)];
                }
                if (in_array($hex, self::PLUM)) {
                    $data[] = '梅花' . self::PIKER[array_search($hex, self::PLUM)];
                }
                if (in_array($hex, self::RED_HEART)) {
                    $data[] = '红桃' . self::PIKER[array_search($hex, self::RED_HEART)];
                }
                if (in_array($hex, self::BLACK_HEART)) {
                    $data[] = '黑桃' . self::PIKER[array_search($hex, self::BLACK_HEART)];
                }
                if (in_array($hex, self::KING)) {
                    $data[] = '大小王' . self::PIKER[array_search($hex, self::KING)];
                }
            }
        }
        dd($data);

    }
}
