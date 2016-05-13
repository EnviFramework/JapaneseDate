<?php
/**
 * 日本語/和暦日付クラスメインファイル
 *
 * @package JapaneseDate
 * @version 2.0
 * @since 0.1
 * @author Akito<akito-artisan@five-foxes.com>
 */


require dirname(__FILE__).DIRECTORY_SEPARATOR.'JapaneseDate'.DIRECTORY_SEPARATOR.'LunarCalendar.php';

/**
 * 日本語/和暦日付クラス
 *
 * @package JapaneseDate
 * @version 2.0
 * @since 0.1
 * @author Akito<akito-artisan@five-foxes.com>
 */
class JapaneseDate
{

    /**
     * +-- 旧暦クラス名
     */
    const LC_CLASS_NAME =  '\JapaneseDate\LunarCalendar';


    /**
     * +-- 祝日定数
     */
    const NO_HOLIDAY =  0;
    const NEW_YEAR_S_DAY =  1;
    const COMING_OF_AGE_DAY =  2;
    const NATIONAL_FOUNDATION_DAY =  3;
    const THE_SHOWA_EMPEROR_DIED =  4;
    const VERNAL_EQUINOX_DAY =  5;
    const DAY_OF_SHOWA =  6;
    const GREENERY_DAY =  7;
    const THE_EMPEROR_S_BIRTHDAY =  8;
    const CROWN_PRINCE_HIROHITO_WEDDING =  9;
    const CONSTITUTION_DAY =  10;
    const NATIONAL_HOLIDAY =  11;
    const CHILDREN_S_DAY =  12;
    const COMPENSATING_HOLIDAY =  13;
    const CROWN_PRINCE_NARUHITO_WEDDING =  14;
    const MARINE_DAY =  15;
    const AUTUMNAL_EQUINOX_DAY =  16;
    const RESPECT_FOR_SENIOR_CITIZENS_DAY =  17;
    const SPORTS_DAY =  18;
    const CULTURE_DAY =  19;
    const LABOR_THANKSGIVING_DAY =  20;
    const REGNAL_DAY =  21;
    const MOUNTAIN_DAY =  22;

    /**
     * +-- 特定月定数
     */
    const VERNAL_EQUINOX_DAY_MONTH   =  3;
    const AUTUMNAL_EQUINOX_DAY_MONTH =  9;

    /**
     * +-- 曜日定数
     */
    const SUNDAY =     0;
    const MONDAY =     1;
    const TUESDAY =    2;
    const WEDNESDAY =  3;
    const THURSDAY =   4;
    const FRIDAY =     5;
    const SATURDAY =   6;

    /**
     * +-- 旧暦クラスオブジェクト
     * @var japaneseDate_lunarCalendar
     */
    private $lc;

    /**#@+
     * @access private
     */
    private $_holiday_name = array(
        0 => '',
        1 => '元旦',
        2 => '成人の日',
        3 => '建国記念の日',
        4 => '昭和天皇の大喪の礼',
        5 => '春分の日',
        6 => '昭和の日',
        7 => 'みどりの日',
        8 => '天皇誕生日',
        9 => '皇太子明仁親王の結婚の儀',
        10 => '憲法記念日',
        11 => '国民の休日',
        12 => 'こどもの日',
        13 => '振替休日',
        14 => '皇太子徳仁親王の結婚の儀',
        15 => '海の日',
        16 => '秋分の日',
        17 => '敬老の日',
        18 => '体育の日',
        19 => '文化の日',
        20 => '勤労感謝の日',
        21 => '即位礼正殿の儀',
        22 => '山の日',
    );

    private $_weekday_name = array('日', '月', '火', '水', '木', '金', '土');

    private $_during_the_war_period_weekday_name = array('月', '月', '火', '水', '木', '金', '金');

    private $_month_name = array('', '睦月', '如月', '弥生', '卯月', '皐月', '水無月', '文月', '葉月', '長月', '神無月', '霜月', '師走');

    private $_six_weekday = array('大安', '赤口', '先勝', '友引', '先負', '仏滅');

    private $_oriental_zodiac = array('亥', '子', '丑', '寅', '卯', '辰', '巳', '午', '未', '申', '酉', '戌', );

    private $_era_name = array('昭和', '平成');

    private $_era_calc = array(1925, 1988);

    private $_24_sekki = array();

    private $_use_luna = false;


    /**#@-*/

    /**
     * +-- コンストラクタ
     *
     * @access public
     * @params
     * @return void
     */
    public function __construct()
    {
        // 旧暦取り扱いクラス
        $lc = self::LC_CLASS_NAME;
        $this->lc = new $lc();
    }
    /* ----------------------------------------- */

    /**
     * +-- 期間内の営業日を取得する
     *
     * @param integer|string $time_stamp 取得開始日
     * @param integer|string $time_stamp_end 取得終了日
     * @param boolean $is_bypass_holiday 祝日を無視するかどうか (optional)
     * @param array $bypass_week_arr 無視する曜日 (optional)
     * @param array $bypass_date_arr 無視する日 (optional)
     * @return array
     */
    public function getWorkingDayBySpan($time_stamp, $time_stamp_end, $is_bypass_holiday = true, array $bypass_week_arr = array(), array $bypass_date_arr = array() )
    {
        if (!empty($bypass_week_arr)) {
            $bypass_week_arr   = array_flip($bypass_week_arr);
        }
        if (!empty($bypass_date_arr)) {
            $gc = array();
            foreach ($bypass_date_arr as $value) {
                $value = $this->toTimeStamp($value);
                $gc[mktime(0, 0, 0, date('m', $value), date('d', $value), date('Y', $value))] = 1;
            }
            $bypass_date_arr = $gc;
        }
        $time_stamp     = $this->toTimeStamp($time_stamp);
        $time_stamp_end = $this->toTimeStamp($time_stamp_end);

        // 終了日タイムスタンプを丸める
        $year  = date('Y', $time_stamp_end);
        $month = date('m', $time_stamp_end);
        $day   = date('d', $time_stamp_end);
        $time_stamp_end = mktime(0, 0, 0, $month, $day, $year);

        $res = array();
        $i = 0;
        $year  = date('Y', $time_stamp);
        $month = date('m', $time_stamp);
        $day   = date('d', $time_stamp);
        while ($time_stamp < $time_stamp_end) {
            $time_stamp = mktime(0, 0, 0, $month, $day + $i, $year);
            $gc = $this->purseTime($time_stamp);
            if ((array_key_exists($gc['week'], $bypass_week_arr) === false) &&
                (array_key_exists($gc['time_stamp'], $bypass_date_arr) === false) &&
                ($is_bypass_holiday ? $gc['holiday'] == self::NO_HOLIDAY : true)) {
                $res[] = $gc;
            }
            $i++;
        }
        return $res;
    }
    /* ----------------------------------------- */


    /**
     * +-- 営業日を取得します
     *
     * getWorkingDayByLimitへのエイリアスです。
     *
     * @param integer integer $time_stamp 取得開始日
     * @param integer integer $lim_day 取得日数
     * @param boolean $is_bypass_holiday 祝日を無視するかどうか (optional)
     * @param array $bypass_week_arr 無視する曜日 (optional)
     * @param array $bypass_date_arr 無視する日 (optional)
     * @return array
     */
    public function getWorkingDay($time_stamp, $lim_day, $is_bypass_holiday = true, array $bypass_week_arr = NULL, array $bypass_date_arr = NULL )
    {
        return $this->getWorkingDayByLimit($time_stamp, $lim_day, $is_bypass_holiday, $bypass_week_arr, $bypass_date_arr);
    }
    /* ----------------------------------------- */

    /**
     * +-- 営業日を取得します
     *
     * @param integer integer $time_stamp 取得開始日
     * @param integer integer $lim_day 取得日数
     * @param boolean $is_bypass_holiday 祝日を無視するかどうか (optional)
     * @param array $bypass_week_arr 無視する曜日 (optional)
     * @param array $bypass_date_arr 無視する日 (optional)
     * @return array
     */
    public function getWorkingDayByLimit($time_stamp, $lim_day, $is_bypass_holiday = true, array $bypass_week_arr = array(), array $bypass_date_arr = array())
    {
        if (!empty($bypass_week_arr)) {
            $bypass_week_arr   = array_flip($bypass_week_arr);
        }
        if (!empty($bypass_date_arr)) {
            $gc = array();
            foreach ($bypass_date_arr as $value) {
                $value     = $this->toTimeStamp($value);
                $gc[mktime(0, 0, 0, date('m', $value), date('d', $value), date('Y', $value))] = 1;
            }
            $bypass_date_arr = $gc;
        }


        $time_stamp     = $this->toTimeStamp($time_stamp);

        $res = array();
        $i = 0;
        $year  = date('Y', $time_stamp);
        $month = date('m', $time_stamp);
        $day   = date('d', $time_stamp);
        while (count($res) != $lim_day) {
            $time_stamp = mktime(0, 0, 0, $month, $day + $i, $year);
            $gc = $this->purseTime($time_stamp);
            if ((array_key_exists($gc['week'], $bypass_week_arr) === false) &&
                (array_key_exists($gc['time_stamp'], $bypass_date_arr) === false) &&
                ($is_bypass_holiday ? $gc['holiday'] == self::NO_HOLIDAY : true)) {
                $res[] = $gc;
            }
            $i++;
        }
        return $res;
    }
    /* ----------------------------------------- */

    /**
     * +-- 旧暦情報を取得するようにモード変更
     *
     * @access public
     * @params
     * @return void
     */
    public function withLuna()
    {
        $this->_use_luna = true;
    }
    /* ----------------------------------------- */

    /**
     * +-- 旧暦情報を取得しないようにモード変更
     *
     * @access public
     * @params
     * @return void
     */
    public function withoutLuna()
    {
        $this->_use_luna = false;
    }
    /* ----------------------------------------- */

    /**
     * +-- 指定月の祝日リストを取得する
     *
     * @param integer $time_stamp タイムスタンプ
     * @return array
     */
    public function getHolidayList($time_stamp)
    {
        switch ($this->getMonth($time_stamp)) {
        case 1:
            return $this->getJanuaryHoliday($this->getYear($time_stamp));
        case 2:
            return $this->getFebruaryHoliday($this->getYear($time_stamp));
        case 3:
            return $this->getMarchHoliday($this->getYear($time_stamp));
        case 4:
            return $this->getAprilHoliday($this->getYear($time_stamp));
        case 5:
            return $this->getMayHoliday($this->getYear($time_stamp));
        case 6:
            return $this->getJuneHoliday($this->getYear($time_stamp));
        case 7:
            return $this->getJulyHoliday($this->getYear($time_stamp));
        case 8:
            return $this->getAugustHoliday($this->getYear($time_stamp));
        case 9:
            return $this->getSeptemberHoliday($this->getYear($time_stamp));
        case 10:
            return $this->getOctoberHoliday($this->getYear($time_stamp));
        case 11:
            return $this->getNovemberHoliday($this->getYear($time_stamp));
        case 12:
            return $this->getDecemberHoliday($this->getYear($time_stamp));
        }
    }
    /* ----------------------------------------- */

    /**
     * +-- 干支キーを返す
     *
     * @param integer $time_stamp タイムスタンプ
     * @return int
     */
    public function getOrientalZodiac($time_stamp)
    {
        $res = ($this->getYear($time_stamp)+9)%12;

        return $res;
    }
    /* ----------------------------------------- */

    /**
     * +-- 年号キーを返す
     *
     * @param integer $time_stamp タイムスタンプ
     * @return int
     */
    public function getEraName($time_stamp)
    {
        $time_stamp     = $this->toTimeStamp($time_stamp);
        if (mktime(0, 0, 0, 1 , 7, 1989) >= $time_stamp) {
            //昭和
            return 0;
        }

        //平成
        return 1;
    }
    /* ----------------------------------------- */

    /**
     * +-- 和暦を返す
     *
     * @param integer $time_stamp タイムスタンプ
     * @param integer 和暦モード(空にすると、自動取得)
     * @return int
     */
    public function getEraYear($time_stamp, $key = NULL)
    {
        $time_stamp     = $this->toTimeStamp($time_stamp);
        if (empty($key)) {
            $key = $this->getEraName($time_stamp);
        }
        return $this->getYear($time_stamp)-$this->_era_calc[$key];
    }
    /* ----------------------------------------- */

    /**
     * +-- 日本語フォーマットされた休日名を返す
     *
     * @param integer $key 休日キー
     * @return string
     */
    public function viewHoliday($key)
    {
        return $this->_holiday_name[$key];
    }
    /* ----------------------------------------- */

    /**
     * +-- 日本語フォーマットされた曜日名を返す
     *
     * @param integer $key 曜日キー
     * @return string
     */
    public function viewWeekday($key)
    {
        return $this->_weekday_name[$key];
    }
    /* ----------------------------------------- */


    /**
     * +-- 日本語フォーマットされた旧暦月名を返す
     *
     * @param integer $key 月キー
     * @return string
     */
    public function viewMonth($key)
    {
        return $this->_month_name[$key];
    }
    /* ----------------------------------------- */


    /**
     * +-- 日本語フォーマットされた六曜名を返す
     *
     * @param integer $key 六曜キー
     * @return string
     */
    public function viewSixWeekday($key)
    {
        return array_key_exists($key, $this->_six_weekday) ? $this->_six_weekday[$key] : '';
    }
    /* ----------------------------------------- */


    /**
     * +-- 日本語フォーマットされた戦争中曜日名を返す
     *
     * @param integer $key 曜日キー
     * @return string
     */
    public function viewWarWeekday($key)
    {
        return $this->during_the_war_period_weekday_name[$key];
    }
    /* ----------------------------------------- */

    /**
     * +-- 日本語フォーマットされた干支を返す
     *
     * @param integer $key 干支キー
     * @return string
     */
    public function viewOrientalZodiac($key)
    {
        return $this->_oriental_zodiac[$key];
    }
    /* ----------------------------------------- */

    /**
     * +-- 日本語フォーマットされた年号を返す
     *
     * @param integer $key 年号キー
     * @return string
     */
    public function viewEraName($key)
    {
        return $this->_era_name[$key];
    }
    /* ----------------------------------------- */

    /**
     * +-- 春分の日を取得
     *
     * @param integer $time_stamp タイムスタンプ
     * @return integer タイムスタンプ
     */
    public function getVernalEquinoxDay($year)
    {
        if ($year <= 1979) {
            $day = floor(20.8357 + (0.242194 * ($year - 1980)) - floor(($year - 1980) / 4));
        } elseif ($year <= 2099) {
            $day = floor(20.8431 + (0.242194 * ($year - 1980)) - floor(($year - 1980) / 4));
        } elseif ($year <= 2150) {
            $day = floor(21.851 + (0.242194 * ($year - 1980)) - floor(($year - 1980) / 4));
        } else {
            return false;
        }
        return mktime(0, 0, 0, self::VERNAL_EQUINOX_DAY_MONTH, $day, $year);
    }
    /* ----------------------------------------- */

    /**
     * +-- 秋分の日を取得
     *
     * @param integer $time_stamp タイムスタンプ
     * @return integer タイムスタンプ
     */
    public function getAutumnEquinoxDay($year)
    {
        if ($year <= 1979) {
            $day = floor(23.2588 + (0.242194 * ($year - 1980)) - floor(($year - 1980) / 4));
        } elseif ($year <= 2099) {
            $day = floor(23.2488 + (0.242194 * ($year - 1980)) - floor(($year - 1980) / 4));
        } elseif ($year <= 2150) {
            $day = floor(24.2488 + (0.242194 * ($year - 1980)) - floor(($year - 1980) / 4));
        } else {
            return false;
        }
        return mktime(0, 0, 0, self::AUTUMNAL_EQUINOX_DAY_MONTH, $day, $year);
    }
    /* ----------------------------------------- */

    /**
     * +-- タイムスタンプを展開して、日付の詳細配列を取得する
     *
     * @param integer $time_stamp タイムスタンプ
     * @return integer タイムスタンプ
     */
    public function makeDateArray($time_stamp)
    {
        $time_stamp     = $this->toTimeStamp($time_stamp);
        $res = array(
            'Year'    => $this->getYear($time_stamp),
            'Month'   => $this->getMonth($time_stamp),
            'Day'     => $this->getDay($time_stamp),
            'Weekday' => $this->getWeekday($time_stamp),
        );

        $holiday_list = $this->getHolidayList($time_stamp);
        $res['Holiday'] = isset($holiday_list[$res['Day']]) ? $holiday_list[$res['Day']] : self::NO_HOLIDAY;
        return $res;
    }
    /* ----------------------------------------- */

    /**
     * +-- 第○ ■曜日の日付を取得します。
     *
     * @param integer $year 年
     * @param integer $month 月
     * @param integer $weekly 曜日
     * @param integer $renb 何週目か
     * @return int
     */
    public function getDayByWeekly($year, $month, $weekly, $renb = 1)
    {
        switch ($weekly) {
            case 0:
                $map = array(7, 1, 2, 3, 4, 5, 6, );
            break;
            case 1:
                $map = array(6, 7, 1, 2, 3, 4, 5, );
            break;
            case 2:
                $map = array(5, 6, 7, 1, 2, 3, 4, );
            break;
            case 3:
                $map = array(4, 5, 6, 7, 1, 2, 3, );
            break;
            case 4:
                $map = array(3, 4, 5, 6, 7, 1, 2, );
            break;
            case 5:
                $map = array(2, 3, 4, 5, 6, 7, 1, );
            break;
            case 6:
                $map = array(1, 2, 3, 4, 5, 6, 7, );
            break;
        }

        $renb = 7*$renb+1;
        return $renb - $map[$this->getWeekday(mktime(0, 0, 0, $month, 1, $year))];
    }
    /* ----------------------------------------- */

    /**
     * +-- 指定月のカレンダー配列を取得します
     *
     * @param integer $year 年
     * @param integer $month 月
     */
    public function getCalendar($year, $month)
    {
        $lim = date('t', mktime(0, 0, 0, $month, 1, $year));
        return $this->getSpanCalendar($year, $month, 1, $lim);
    }
    /* ----------------------------------------- */


    /**
     * +-- 指定範囲のカレンダー配列を取得します
     *
     * @param integer $year 年
     * @param integer $month 月
     * @param integer $str 開始日
     * @param integer $lim 期間(日)
     * @return array
     */
    public function getSpanCalendar($year, $month, $str, $lim)
    {
        $luna = $this->_use_luna;
        if ($lim <= 0) {
            return array();
        }

        $time_stamp = mktime(0, 0, 0, $month, $str-1, $year);
        if ($luna === false) {
            while ($lim != 0) {
                $time_stamp = mktime(0, 0, 0, date('m', $time_stamp), date('d', $time_stamp) + 1, date('Y', $time_stamp));
                $gc = $this->purseTime($time_stamp, false);
                $res[] = $gc;
                $lim--;
            }
            return $res;
        } else {
            // 期間リスト
            $time_array = array();
            while ($lim != 0) {
                $time_stamp = mktime(0, 0, 0, date('m', $time_stamp), date('d', $time_stamp) + 1, date('Y', $time_stamp));
                $time_array[] = $time_stamp;
                $lim--;
            }
            // 旧暦
            $luna_array = $this->getLunaCalendarList($time_array, self::KEY_TIMESTAMP);
            foreach ($time_array as $time_stamp) {
                $gc = $this->purseTime($time_stamp, $luna_array[$time_stamp]);
                $res[] = $gc;
            }
        }
        return $res;
    }
    /* ----------------------------------------- */

    /**
     * +-- タイムスタンプを展開して、日付情報を返します
     *
     * @param integer $time_stamp タイムスタンプ
     * @return array
     */
    public function purseTime($time_stamp)
    {
        $time_stamp     = $this->toTimeStamp($time_stamp);
        $luna = $this->_use_luna;
        $holiday = $this->getHolidayList($time_stamp);

        $day = date('j', $time_stamp);
        $res = array(
            'time_stamp' => $time_stamp,
            'day'        => $day,
            'strday'     => date('d', $time_stamp),
            'holiday'    => isset($holiday[$day]) ? $holiday[$day] : self::NO_HOLIDAY,
            'week'       => $this->getWeekday($time_stamp),
            'month'      => date('m', $time_stamp),
            'year'       => date('Y', $time_stamp),
        );
        if (!$luna) {
            return $res;
        }
        $luna = $this->getLunarCalendar($time_stamp);

        $res['sixweek']         = $this->getSixWeekdayByLuna($luna['month'], $luna['day']);
        $res['luna_time_stamp'] = $luna['time_stamp'];
        $res['is_chuki']     = $luna['is_chuki'];
        $res['chuki']        = $luna['chuki'];
        $res['tuitachi_jd']  = $luna['tuitachi_jd'];
        $res['jd']           = $luna['jd'];
        $res['luna_year']    = $luna['year'];
        $res['luna_month']   = $luna['month'];
        $res['luna_day']     = $luna['day'];
        $res['uruu']         = $luna['uruu'];

        return $res;
    }
    /* ----------------------------------------- */

    /**
     * +-- 旧暦・月齢を取得する
     *
     * @param integer $time_stamp タイムスタンプ
     * @see japaneseDate_lunarCalendar::getLunarCalendar()
     * @return array
     */
    public function getLunarCalendar($time_stamp)
    {
        $time_stamp     = $this->toTimeStamp($time_stamp);
        return $this->lc->getLunarCalendar($time_stamp);
    }
    /* ----------------------------------------- */

    /**
     * +-- 旧暦・月齢リストを取得する
     *
     * @param array $time_stamp_array タイムスタンプのリスト
     * @param array $mode self::KEY_TIMESTAMP|self::KEY_ORDERD
     * @see japaneseDate_lunarCalendar::getLunaCalendarList()
     * @return array
     */
    public function getLunaCalendarList($time_stamp_array, $mode = self::KEY_ORDERD)
    {
        return $this->lc->getLunaCalendarList($time_stamp_array, $mode);
    }
    /* ----------------------------------------- */

    /**
     * +-- ユニックスタイムスタンプから、ユリウス暦を取得します。
     *
     * @param integer $time_stamp タイムスタンプ
     * @see japaneseDate_lunarCalendar::time2JD()
     * @return float
     */
    public function time2JD($time_stamp)
    {
        $time_stamp     = $this->toTimeStamp($time_stamp);
        return $this->lc->time2JD($time_stamp);
    }
    /* ----------------------------------------- */


    /**
     * +-- 日本語カレンダー対応したstrftime()
     *
     * <pre>{@link http://php.five-foxes.com/module/php_man/index.php?web=public function.strftime strftimeの仕様}
     * に加え、
     * %J 1～31の日
     * %g 1～9なら先頭にスペースを付ける、1～31の日
     * %K 和名曜日
     * %k 六曜番号
     * %6 六曜
     * %K 曜日
     * %l 祝日番号
     * %L 祝日
     * %o 干支番号
     * %O 干支
     * %N 1～12の月
     * %E 旧暦年
     * %G 旧暦の月
     * %F 年号
     * %f 年号ID
     *
     * が使用できます。</pre>
     *
     * @since 1.1
     * @param string $format フォーマット
     * @param integer $time_stamp 変換したいタイムスタンプ(デフォルトは現在のロケール時間)
     * @return string
     */
    public function mb_strftime($format, $time_stamp = NULL)
    {
        $luna = $this->_use_luna;
        if (empty($time_stamp)) {
            $time_stamp = time();
        } else {
            $time_stamp     = $this->toTimeStamp($time_stamp);
        }
        $jtime = $this->purseTime($time_stamp);
        $OrientalZodiac = $this->getOrientalZodiac($time_stamp);
        $jd_token = array(
            '%o' => $OrientalZodiac,
            '%O' => $this->viewOrientalZodiac($OrientalZodiac),
            '%l' => $jtime['holiday'],
            '%L' => $this->viewHoliday($jtime['holiday']),
            '%K' => $this->viewWeekday($jtime['week']),
            '%k' => $luna ? $this->viewSixWeekday($jtime['sixweek']) : '',
            '%6' => $luna ? $jtime['sixweek'] : '',
            '%g' => strlen($jtime['day']) == 1 ? ' '.$jtime['day'] : $jtime['day'],
            '%J' => $jtime['day'],
            '%G' => $this->viewMonth($this->getMonth($time_stamp)),
            '%N' => $this->getMonth($time_stamp),
            '%F' => $this->viewEraName($this->getEraName($time_stamp)),
            '%f' => $this->getEraName($time_stamp),
            '%E' => $this->getEraYear($time_stamp)
        );

        $resstr = '';
        $format_array = explode('%', $format);
        $count = count($format_array)-1;
        $i = 0;
        while (isset($format_array[$i])) {
            if (($i == 0 || $i == $count) && $format_array[$i] == '') {
                $i++;
                continue;
            } elseif ($format_array[$i] == '') {
                $resstr .= '%%';
                $i++;
                if (isset($format_array[$i])) {
                    $resstr .= $format_array[$i];
                }
                $i++;
                continue;
            } else {
                $token = '%'.mb_substr($format_array[$i], 0, 1);
                if (isset($jd_token[$token])) {
                    $token = $jd_token[$token];
                }
                if (mb_strlen($format_array[$i]) > 1) {
                    $token .= mb_substr($format_array[$i], 1);
                }
                $resstr .= $token;
                $i++;
            }
        }
        return strftime($resstr, $time_stamp);
    }
    /* ----------------------------------------- */



    /**
     * +-- 六曜を数値化して返します
     *
     * @param integer $time_stamp タイムスタンプ
     */
    public function getSixWeekday($time_stamp)
    {
        $luna = $this->getLunarCalendar($time_stamp);
        return $this->getSixWeekdayByLuna($luna['month'], $luna['day']);
    }
    /* ----------------------------------------- */


    /**
     * +-- 六曜を数値化して返します
     *
     * @param integer $time_stamp タイムスタンプ
     */
    protected function getSixWeekdayByLuna($month, $day)
    {
        return ($month+$day) % 6;
    }
    /* ----------------------------------------- */


    /**
     * +-- 七曜を数値化して返します
     *
     * @param integer $time_stamp タイムスタンプ
     */
    protected function getWeekday($time_stamp)
    {
        $time_stamp     = $this->toTimeStamp($time_stamp);
        return date('w', $time_stamp);
    }
    /* ----------------------------------------- */

    /**
     * +-- 年を数値化して返します
     *
     * @param integer $time_stamp タイムスタンプ
     */
    protected function getYear($time_stamp)
    {
        $time_stamp     = $this->toTimeStamp($time_stamp);
        return date('Y', $time_stamp);
    }
    /* ----------------------------------------- */

    /**
     * +-- 月を数値化して返します
     *
     * @param integer $time_stamp タイムスタンプ
     */
    protected function getMonth($time_stamp)
    {
        $time_stamp     = $this->toTimeStamp($time_stamp);
        return date('n', $time_stamp);
    }
    /* ----------------------------------------- */

    /**
     * +-- 日を数値化して返します
     *
     * @param integer $time_stamp タイムスタンプ
     */
    protected function getDay($time_stamp)
    {
        $time_stamp     = $this->toTimeStamp($time_stamp);
        return date('j', $time_stamp);
    }
    /* ----------------------------------------- */

    /**
     * +-- 日を表示用フォーマットで返します
     *
     * @param integer $time_stamp タイムスタンプ
     */
    protected function getStrDay($time_stamp)
    {
        $time_stamp     = $this->toTimeStamp($time_stamp);
        return date('d', $time_stamp);
    }
    /* ----------------------------------------- */

    /**
     * +-- 祝日判定ロジック一月
     *
     * @param integer $year 年
     * @return array
     */
    protected function getJanuaryHoliday($year)
    {
        $res[1] = self::NEW_YEAR_S_DAY;
        //振替休日確認
        if ($this->getWeekDay(mktime(0, 0, 0, 1, 1, $year)) == self::SUNDAY) {
            $res[2] = self::COMPENSATING_HOLIDAY;
        }
        if ($year >= 2000) {
            //2000年以降は第二月曜日に変更
            $second_monday = $this->getDayByWeekly($year, 1, self::MONDAY, 2);
            $res[$second_monday] = self::COMING_OF_AGE_DAY;

        } else {
            $res[15] = self::COMING_OF_AGE_DAY;
            //振替休日確認
            if ($this->getWeekDay(mktime(0, 0, 0, 1, 15, $year)) == self::SUNDAY) {
                $res[16] = self::COMPENSATING_HOLIDAY;
            }
        }
        return $res;
    }
    /* ----------------------------------------- */

    /**
     * +-- 祝日判定ロジック二月
     *
     * @param integer $year 年
     * @return array
     */
    protected function getFebruaryHoliday($year)
    {
        $res[11] = self::NATIONAL_FOUNDATION_DAY;
        //振替休日確認
        if ($this->getWeekDay(mktime(0, 0, 0, 2, 11, $year)) == self::SUNDAY) {
            $res[12] = self::COMPENSATING_HOLIDAY;
        }
        if ($year == 1989) {
            $res[24] = self::THE_SHOWA_EMPEROR_DIED;
        }
        return $res;
    }
    /* ----------------------------------------- */

    /**
     * +-- 祝日判定ロジック三月
     *
     * @param integer $year 年
     * @return array
     */
    protected function getMarchHoliday($year)
    {
        $VernalEquinoxDay = $this->getVernalEquinoxDay($year);
        $res[$this->getDay($VernalEquinoxDay)] = self::VERNAL_EQUINOX_DAY;
        //振替休日確認
        if ($this->getWeekDay($VernalEquinoxDay) == self::SUNDAY) {
            $res[$this->getDay($VernalEquinoxDay)+1] = self::COMPENSATING_HOLIDAY;
        }
        return $res;
    }
    /* ----------------------------------------- */

    /**
     * +-- 祝日判定ロジック四月
     *
     * @param integer $year 年
     * @return array
     */
    protected function getAprilHoliday($year)
    {
        if ($year == 1959) {
            $res[10] = self::CROWN_PRINCE_HIROHITO_WEDDING;
        }
        if ($year >= 2007) {
            $res[29] = self::DAY_OF_SHOWA;
        } elseif ($year >= 1989) {
            $res[29] = self::GREENERY_DAY;
        } else {
            $res[29] = self::THE_EMPEROR_S_BIRTHDAY;
        }
        //振替休日確認
        if ($this->getWeekDay(mktime(0, 0, 0, 4, 29, $year)) == self::SUNDAY) {
            $res[30] = self::COMPENSATING_HOLIDAY;
        }
        return $res;
    }
    /* ----------------------------------------- */

    /**
     * +-- 祝日判定ロジック五月
     *
     * @param integer $year 年
     * @return array
     */
    protected function getMayHoliday($year)
    {
        $res[3] = self::CONSTITUTION_DAY;
        if ($year >= 2007) {
            $res[4] = self::GREENERY_DAY;
        } elseif ($year >= 1986) {
            // 5/4が日曜日の場合はそのまま､月曜日の場合は『憲法記念日の振替休日』(2006年迄)
            if ($this->getWeekday(mktime(0, 0, 0, 5, 4, $year)) > self::MONDAY) {
                $res[4] = self::NATIONAL_HOLIDAY;
            } elseif ($this->getWeekday(mktime(0, 0, 0, 5, 4, $year)) == self::MONDAY)  {
                $res[4] = self::COMPENSATING_HOLIDAY;
            }
        }
        $res[5] = self::CHILDREN_S_DAY;
        if ($this->getWeekDay(mktime(0, 0, 0, 5, 5, $year)) == self::SUNDAY) {
            $res[6] = self::COMPENSATING_HOLIDAY;
        }
        if ($year >= 2007) {
            // [5/3, 5/4が日曜]なら、振替休日
            if (($this->getWeekday(mktime(0, 0, 0, 5, 4, $year)) == self::SUNDAY) || ($this->getWeekday(mktime(0, 0, 0, 5, 3, $year)) == self::SUNDAY)) {
                $res[6] = self::COMPENSATING_HOLIDAY;
            }
        }
        return $res;
    }
    /* ----------------------------------------- */

    /**
     * +-- 祝日判定ロジック六月
     *
     * @param integer $year 年
     * @return array
     */
    protected function getJuneHoliday($year)
    {
        if ($year == '1993') {
            $res[9] = self::CROWN_PRINCE_NARUHITO_WEDDING;
        } else {
            $res = array();
        }
        return $res;
    }
    /* ----------------------------------------- */

    /**
     * +-- 祝日判定ロジック七月
     *
     * @param integer $year 年
     * @return array
     */
    protected function getJulyHoliday($year)
    {
        if ($year >= 2003) {
            $third_monday = $this->getDayByWeekly($year, 7, self::MONDAY, 3);
            $res[$third_monday] = self::MARINE_DAY;
        } elseif ($year >= 1996) {
            $res[20] = self::MARINE_DAY;
            //振替休日確認
            if ($this->getWeekDay(mktime(0, 0, 0, 7, 20, $year)) == self::SUNDAY) {
                $res[21] = self::COMPENSATING_HOLIDAY;
            }
        } else {
            $res = array();
        }
        return $res;
    }
    /* ----------------------------------------- */

    /**
     * +-- 祝日判定ロジック八月
     *
     * @param integer $year 年
     * @return array
     */
    protected function getAugustHoliday($year)
    {
        $res = array();
        if ($year >= 2016) {
            $res[11] = self::MOUNTAIN_DAY;
        }
        return $res;
    }
    /* ----------------------------------------- */

    /**
     * +-- 祝日判定ロジック九月
     *
     * @param integer $year 年
     * @return array
     */
    protected function getSeptemberHoliday($year)
    {
        $autumnEquinoxDay = $this->getAutumnEquinoxDay($year);
        $res[$this->getDay($autumnEquinoxDay)] = self::AUTUMNAL_EQUINOX_DAY;
        //振替休日確認
        if ($this->getWeekDay($autumnEquinoxDay) == 0) {
            $res[$this->getDay($autumnEquinoxDay)+1] = self::COMPENSATING_HOLIDAY;
        }

        if ($year >= 2003) {
            $third_monday = $this->getDayByWeekly($year, 9, self::MONDAY, 3);
            $res[$third_monday] = self::RESPECT_FOR_SENIOR_CITIZENS_DAY;

            //敬老の日と、秋分の日の間の日は休みになる
            if (($this->getDay($autumnEquinoxDay) - 1) == ($third_monday + 1)) {
                $res[($this->getDay($autumnEquinoxDay) - 1)] = self::NATIONAL_HOLIDAY;
            }

        } elseif ($year >= 1966) {
            $res[15] = self::RESPECT_FOR_SENIOR_CITIZENS_DAY;
        }
        return $res;
    }
    /* ----------------------------------------- */

    /**
     * +-- 祝日判定ロジック十月
     *
     * @param integer $year 年
     * @return array
     */
    protected function getOctoberHoliday($year)
    {
        if ($year >= 2000) {
            //2000年以降は第二月曜日に変更
            $second_monday = $this->getDayByWeekly($year, 10, self::MONDAY, 2);
            $res[$second_monday] = self::SPORTS_DAY;
        } elseif ($year >= 1966) {
            $res[10] = self::SPORTS_DAY;
            //振替休日確認
            if ($this->getWeekDay(mktime(0, 0, 0, 10, 10, $year)) == self::SUNDAY) {
                $res[11] = self::COMPENSATING_HOLIDAY;
            }
        }
        return $res;
    }
    /* ----------------------------------------- */

    /**
     * +-- 祝日判定ロジック十一月
     *
     * @param integer $year 年
     * @return array
     */
    protected function getNovemberHoliday($year)
    {
        $res[3] = self::CULTURE_DAY;
        //振替休日確認
        if ($this->getWeekDay(mktime(0, 0, 0, 11, 3, $year)) == self::SUNDAY) {
            $res[4] = self::COMPENSATING_HOLIDAY;
        }

        if ($year == 1990) {
            $res[12] = self::REGNAL_DAY;
        }

        $res[23] = self::LABOR_THANKSGIVING_DAY;
        //振替休日確認
        if ($this->getWeekDay(mktime(0, 0, 0, 11, 23, $year)) == self::SUNDAY) {
            $res[24] = self::COMPENSATING_HOLIDAY;
        }
        return $res;
    }
    /* ----------------------------------------- */

    /**
     * +-- 祝日判定ロジック十二月
     *
     * @param integer $year 年
     * @return array
     */
    protected function getDecemberHoliday($year)
    {
        if ($year >= 1989) {
            $res[23] = self::THE_EMPEROR_S_BIRTHDAY;
        }
        if ($this->getWeekDay(mktime(0, 0, 0, 12, 23, $year)) == self::SUNDAY) {
            $res[24] = self::COMPENSATING_HOLIDAY;
        }
        return $res;
    }
    /* ----------------------------------------- */

    /**
     * +-- UNIXタイムスタンプ化
     *
     * @access      protected
     * @param       var_text $time_stamp
     * @return      int
     */
    protected function toTimeStamp($time_stamp)
    {
        if (is_string($time_stamp) && !ctype_digit($time_stamp)) {
            $time_stamp = strtotime($time_stamp);
        }
        return $time_stamp;
    }
    /* ----------------------------------------- */

}
