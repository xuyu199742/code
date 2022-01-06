<?php
namespace libs;

class Time
{
    /**
     * 返回指定日期的开始和结束时间戳
     *
     * @param string $date
     * @param int $day
     * @return array|int
     */
    public static function getDayTime($date,$day = 1)
    {
        return [
            strtotime($date),
            strtotime($date) + self::daysToSecond($day)
        ];
    }

    /**
     * 返回今日开始和结束的时间戳
     * @access public
     * @param int $timeType 时间类型
     * @return array
     */
    public static function today()
    {
        return [
            mktime(0, 0, 0, date('m'), date('d'), date('Y')),
            mktime(23, 59, 59, date('m'), date('d'), date('Y'))
        ];
    }

    /**
     * 返回昨日开始和结束的时间戳
     *
     * @return array
     */
    public static function yesterday()
    {
        $yesterday = date('d') - 1;
        return [
            mktime(0, 0, 0, date('m'), $yesterday, date('Y')),
            mktime(23, 59, 59, date('m'), $yesterday, date('Y'))
        ];
    }

    /**
     * 返回本周开始和结束的时间戳
     *
     * @return array
     */
    public static function week()
    {
        $timestamp = time();
        return [
            strtotime(date('Y-m-d', strtotime("+0 week Monday", $timestamp))),
            strtotime(date('Y-m-d', strtotime("+0 week Sunday", $timestamp))) + 24 * 3600 - 1
        ];
    }

    /**
     * 返回上周开始和结束的时间戳
     *
     * @return array
     */
    public static function lastWeek()
    {
        $timestamp = time();
        return [
            strtotime(date('Y-m-d', strtotime("last week Monday", $timestamp))),
            strtotime(date('Y-m-d', strtotime("last week Sunday", $timestamp))) + 24 * 3600 - 1
        ];
    }

    /**
     * 返回本月开始和结束的时间戳
     *
     * @return array
     */
    public static function month($everyDay = false)
    {
        return [
            mktime(0, 0, 0, date('m'), 1, date('Y')),
            mktime(23, 59, 59, date('m'), date('t'), date('Y'))
        ];
    }

    /**
     * 返回上个月开始和结束的时间戳
     *
     * @return array
     */
    public static function lastMonth()
    {
        $begin = mktime(0, 0, 0, date('m') - 1, 1, date('Y'));
        $end = mktime(23, 59, 59, date('m') - 1, date('t', $begin), date('Y'));
        return [$begin, $end];
    }

    /**
     * 返回任意年的任意月开始和结束的时间戳
     * @param int $month 月份
     * @param string $year 年份
     * @return array
     */
    public static function anyMonth($month=null,$year=null)
    {
        $month=empty($month)?date('m'):$month;//默认当前月
        $year=empty($year)?date('Y'):$year;//默认当前年
        $begin = mktime(0, 0, 0, $month, 1, $year);
        $end = mktime(23, 59, 59, $month, date('t', $begin), $year);
        return [$begin, $end];
    }

    /**
     * 返回任意年的开始和结束的时间戳
     * @param string $year 年份
     * @return array
     */
    public static function anyYear($year=null)
    {
        $year = empty($year)?date('Y'):$year;//默认当前年
        $begin = strtotime($year.'-01-01');
        $end = strtotime($year.'-12-31 23:59:59');
        return [$begin, $end];
    }

    /**
     * 返回本季度开始和结束的时间戳
     *
     * @return array
     */
    public static function qtr()
    {
        $season = ceil(date('n') /3); //获取月份的季度
        $day=date('t', mktime(0, 0, 0, $season * 3, 1, date('Y')));//当前季度最后一个月天数
        return [
            mktime(0,0,0,($season - 1) *3 +1,1,date('Y')),
            mktime(23,59,59,$season * 3,$day,date('Y'))
        ];
    }

    /**
     * 返回上季度开始和结束的时间戳
     *
     * @return array
     */
    public static function lastQtr()
    {
        $season = ceil(date('n') /3); //获取月份的季度
        $day=date('t', mktime(0, 0, 0, ($season - 1) * 3, 1, date('Y')));//上一季度最后一个月天数
        return [
            mktime(0,0,0,($season - 2) * 3 +1,1,date('Y')),
            mktime(23,59,59,($season - 1) * 3,$day,date('Y'))
        ];
    }

    /**
     * 返回任意年的任意季度开始和结束的时间戳
     * @param int $qtr 季度
     * @param string $year 年份
     * @return array
     */
    public static function anyQtr($qtr=null,$year=null)
    {
        $year=empty($year)?date('Y'):$year;//默认当前年
        $qtr = empty($qtr)?ceil(date('n') /3):$qtr; //默认当前季度
        $day=date('t', mktime(0, 0, 0, $qtr * 3, 1, $year));//任意季度最后一个月天数
        return [
            mktime(0,0,0,($qtr - 1) * 3 +1,1,$year),
            mktime(23,59,59,$qtr * 3,$day,$year)
        ];
    }

    /**
     * 返回今年开始和结束的时间戳
     *
     * @return array
     */
    public static function year()
    {
        return [
            mktime(0, 0, 0, 1, 1, date('Y')),
            mktime(23, 59, 59, 12, 31, date('Y'))
        ];
    }

    /**
     * 返回去年开始和结束的时间戳
     *
     * @return array
     */
    public static function lastYear()
    {
        $year = date('Y') - 1;
        return [
            mktime(0, 0, 0, 1, 1, $year),
            mktime(23, 59, 59, 12, 31, $year)
        ];
    }

    /**
     * 获取几天前零点到现在/昨日结束的时间戳
     *
     * @param int $day 天数
     * @param bool $now 返回现在或者昨天结束时间戳
     * @return array
     */
    public static function dayToNow($day = 1, $now = true)
    {
        $end = time();
        if (!$now) {
            list($foo, $end) = self::yesterday();
        }

        return [
            mktime(0, 0, 0, date('m'), date('d') - $day, date('Y')),
            $end
        ];
    }

    /**
     * 返回几天前的时间戳
     *
     * @param int $day
     * @return int
     */
    public static function daysAgo($day = 1)
    {
        $nowTime = time();
        return $nowTime - self::daysToSecond($day);
    }

    /**
     * 返回几天后的时间戳
     *
     * @param int $day
     * @return int
     */
    public static function daysAfter($day = 1)
    {
        $nowTime = time();
        return $nowTime + self::daysToSecond($day);
    }

    /**
     * 天数转换成秒数
     *
     * @param int $day
     * @return int
     */
    public static function daysToSecond($day = 1)
    {
        return $day * 86400;
    }

    /**
     * 周数转换成秒数
     *
     * @param int $week
     * @return int
     */
    public static function weekToSecond($week = 1)
    {
        return self::daysToSecond() * 7 * $week;
    }

    /**
     * 获取半年前的时间戳
     *
     * @param int $week
     * @return int
     */
   public static function halfYearAgo()
   {
       return mktime(0, 0, 0, date('m')-6, date('d'), date('Y'));
   }

    /**
     * 获取一年前的时间戳
     *
     * @param int $week
     * @return int
     */
    public static function oneYearAgo()
    {
        return mktime(0, 0, 0, date('m'), date('d'), date('Y')-1);
    }

    /**
     * 获取任意年中的每月开始和结束时间
     * @param int $year
     *
     */
    public static function getPerMonth($year)
    {
        $arr = [
            '1' => self::anyMonth(1,$year),//1月开始时间
            '2' => self::anyMonth(2,$year),//2月开始时间
            '3' => self::anyMonth(3,$year),//3月开始时间
            '4' => self::anyMonth(4,$year),//4月开始时间
            '5' => self::anyMonth(5,$year),//5月开始时间
            '6' => self::anyMonth(6,$year),//6月开始时间
            '7' => self::anyMonth(7,$year),//7月开始时间
            '8' => self::anyMonth(8,$year),//8月开始时间
            '9' => self::anyMonth(9,$year),//9月开始时间
            '10' => self::anyMonth(10,$year),//10月开始时间
            '11' => self::anyMonth(11,$year),//11月开始时间
            '12' => self::anyMonth(12,$year),//12月开始时间
        ];
        return $arr;
    }
}
