<?php
/**
 * 旧暦日付クラス
 *
 * 高野英明氏による「旧暦計算サンプルスクリプト」を参考にしています。<br />
 * @link(http://www.vector.co.jp/soft/dos/personal/se016093.html)<br />
 * お手数ですが、再配布ご利用の際は、高野英明氏の「旧暦計算サンプルスクリプト」をDLし、
 * 規定に従ってください。<br />
 *
 * @package    %%project_name%%
 * @subpackage %%subpackage_name%%
 * @author     Suzunone <suzunone.eleven@gmail.com>
 * @copyright  %%your_project%%
 * @license    %%your_license%%
 * @link       %%your_link%%
 * @see        %%your_see%%
 * @sinse Class available since Release 1.0.0
 */
namespace JapaneseDate;



/**
 * 旧暦日付クラス
 *
 * @package    %%project_name%%
 * @subpackage %%subpackage_name%%
 * @author     Suzunone <suzunone.eleven@gmail.com>
 * @copyright  %%your_project%%
 * @license    %%your_license%%
 * @link       %%your_link%%
 * @see        %%your_see%%
 * @sinse Class available since Release 1.0.0
 */
class LunarCalendar
{
    private $k;
    private $tdt;

    /**
     * +-- 旧暦定数
     */
    const JD_BEFORE_NIBUN  = 90.0;
    const JD_CHU           = 30.0;
    const JD_SETU          = 15.0;
    const JD_KEY_TIMESTAMP = 1;
    const JD_KEY_ORDERED   = 2;

    /**
     * +-- コンストラクタ
     *
     * @access      public
     * @return      void
     */
    public function __construct()
    {
        $this->k = M_PI / 180;
        $this->tdt = (gmmktime(0,0,0,1,1,2000) - mktime(0,0,0,1,1,2000)) / 86400;
    }
    /* ----------------------------------------- */

    /**
     * +-- オブジェクトの生成
     *
     * @access      public
     * @static
     * @return      \JapaneseDate\LunarCalendar
     */
    public static function factory()
    {
        static $instance;
        if (!$instance) {
            $instance = new \JapaneseDate\LunarCalendar;
        }
        return $instance;
    }
    /* ----------------------------------------- */

    /**
     * +-- 旧暦カレンダー取得
     *
     *
     * @access      public
     * @param       int $time_stamp ユニックスタイムスタンプ
     * @return      array
     */
    public function getLunarCalendar($time_stamp)
    {
        $date = explode('-', date('H-i-s-m-d-Y', $time_stamp));

        return $this->getLunarCalendarByMktime(
            $date[0],
            $date[1],
            $date[2],
            $date[3],
            $date[4],
            $date[5]
        );
    }
    /* ----------------------------------------- */


    /**
     * +-- タイムスタンプからユリウス暦を取得します。
     *
     * @access      public
     * @param       int $time_stamp ユニックスタイムスタンプ
     * @return      int
     */
    public function time2JD($time_stamp)
    {
        $date = explode('-', date('m-d-Y', $time_stamp));
        return gregoriantojd(
            $date[0],
            $date[1],
            $date[2]
        );
    }
    /* ----------------------------------------- */


    /**
     * +-- 旧暦カレンダーリストを取得
     *
     * @access      public
     * @param       array $time_stamp_array
     * @param       int    $mode データ取得モード
     * @return      array
     */
    public function getLunaCalendarList(array $time_array, $mode = self::JD_KEY_ORDERED)
    {
        sort($time_array);
        $max_key = count($time_array) - 1;
        $lim  = (date("Y", $time_array[$max_key]) - date("Y", $time_array[0])) * 12;
        $lim += (date("m", $time_array[$max_key]) - date("m", $time_array[0]));

        $tm = $this->time2JD($time_array[0]);
        $m = $this->getTsuitachiList($tm, $lim + 6);
        if ($mode == self::JD_KEY_ORDERED) {
            foreach ($time_array as $key => $time) {
                $res[$key]         = $this->getCalendarByTList($this->time2JD($time), $m);
                $res[$key]['time'] = $time;
            }
        } else {
            foreach ($time_array as $key => $time) {
                $res[$time]         = $this->getCalendarByTList($this->time2JD($time), $m);
                $res[$time]['time'] = $time;
            }
        }
        return $res;

    }
    /* ----------------------------------------- */



    /**
     * +-- mktimeと同じインターフェイスで、旧暦変換を行う
     *
     * @param       int $hour 時
     * @param       int $minute 分
     * @param       int $second 秒
     * @param       int $month 月
     * @param       int $day 日
     * @param       int $year 年
     * @return      array
     */
    public function getLunarCalendarByMDY($month, $day, $year)
    {
        static $tsuitachi_list,$lunar_calendar;

        $lim = 14;
        $tm = gregoriantojd($month, $day, $year);
        if (isset($lunar_calendar[$tm])) {
            return $lunar_calendar[$tm];
        } elseif (!empty($tsuitachi_list)) {
            foreach ($tsuitachi_list as  $k => $val) {
                $kyureki_id = $this->getKyurekiID($tm, $val);
                if ($kyureki_id <= $lim && $kyureki_id >= 1) {
                    $m = $val;
                    break;
                }
            }
        }
        if (!isset($m)) {
            $m                   = $this->getTsuitachiList($tm - 60, $lim + 2);
            $tsuitachi_list[$tm] = $m;
            $kyureki_id          = $this->getKyurekiID($tm, $m);
        }

        return $lunar_calendar[$tm] = $this->getCalendarByTList($tm, $m, $kyureki_id);
    }
    /* ----------------------------------------- */




    /**
     * +-- mktimeと同じインターフェイスで、旧暦変換を行う
     *
     * @param       int $hour 時
     * @param       int $minute 分
     * @param       int $second 秒
     * @param       int $month 月
     * @param       int $day 日
     * @param       int $year 年
     * @return      array
     */
    public function getLunarCalendarByMktime($hour, $minute, $second, $month, $day, $year)
    {
        $lim = 12;
        $tm = $this->makeJD($hour, $minute, $second, $month, $day, $year);
        $m = $this->getTsuitachiList($tm, $lim + 6);
        return $this->getCalendarByTList($tm, $m);
    }
    /* ----------------------------------------- */


    /**
     * +-- 朔の一覧取得
     *
     * @access      protected
     * @param       int $tm0
     * @param       int $lim
     * @return      array
     */
    protected function getTsuitachiList($tm0, $lim)
    {
        // 計算対象の直前にあたる二分二至の時刻を求める
        list($nibun[0][0], $nibun[0][1]) = $this->getChu($tm0, self::JD_BEFORE_NIBUN);

        // 計算対象の直前にあたる二分二至の直前の朔の時刻を求める
        $Tsuitachi[0] = $this->getTsuitachi($nibun[0][0]);
        if ($nibun[0][1] % 30) {
            list($chu[0][0], $chu[0][1]) = $this->getChu($nibun[0][0]);
        } else {
            $chu[0] = $nibun[0];
        }

        // 中気の時刻を計算
        for ($i = 1; $i <= $lim ; $i++) {
            list($chu[$i][0], $chu[$i][1]) = $this->getChu($chu[$i - 1][0] + 32.0);
        }

        // 朔の時刻を求める
        for ($i = 1; $i <= $lim; $i++) {
            $tm = $Tsuitachi[$i - 1];
            $tm += 30.0;
            $Tsuitachi[$i] = $this->getTsuitachi($tm);
            // 前と同じ時刻を計算した場合（両者の差が26日以内）には、初期値を
            // +35日にして再実行させる。
            if (abs((int)($Tsuitachi[$i - 1]) - (int)($Tsuitachi[$i])) <= 26.0) {
                $Tsuitachi[$i] = $this->getTsuitachi($Tsuitachi[$i - 1] + 35.0);
            }
        }

        if ((int)($Tsuitachi[1]) <= (int)($nibun[0][0])) {
            // 二分二至の時刻以前になってしまった場合には、朔の時刻を繰り下げて修正する。
            for ($i = 0; $i < $lim; $i++) {
                $Tsuitachi[$i] = $Tsuitachi[$i + 1];
            }
            $Tsuitachi[4] = $this->getTsuitachi($Tsuitachi[3] + 35.0);
         } elseif ((int)($Tsuitachi[0]) > (int)($nibun[0][0])) {
            // 二分二至の時刻以後になってしまった場合、朔の時刻を繰り上げて修正する。
             for ($i = 4 ; $i > 0; $i--) {
                $Tsuitachi[$i] = $Tsuitachi[$i - 1];
            }
            $Tsuitachi[0] = $this->getTsuitachi($Tsuitachi[0] - 27.0);
        }


        // 閏月カウント
        $uruu_count = 0;
        foreach ($Tsuitachi as $key => $value) {
            $res[$key]['jd']    = (int)($value);
            if ($key == 0) {
                $res[$key]['month'] = (int)($chu[0][1] / 30.0) + 2;
                if ($res[$key]['month'] > 12) {
                    $res[$key]['month']-=12;
                }
                $res[$key]['uruu']  = false;
                $res[$key]['chuki'] = $chu[$key];
                continue;
            }

            $a = $key - $uruu_count;
            $b = $key + 1;

            if (!isset($Tsuitachi[$b])) {
                continue;
            }

            if ((int)($chu[$a][0]) < (int)($Tsuitachi[$b]) &&
            (int)($chu[$a][0]) >= (int)($value)) {
                $res[$key]['month'] = $res[$key -1]['month'] + 1;
                if ($res[$key]['month'] > 12) {
                    $res[$key]['month']-=12;
                }
                $res[$key]['uruu']  = false;
                $res[$key]['chuki'] = $chu[$key + 1 - $uruu_count];
            } else {
                $res[$key]['month'] = $res[$key -1]['month'];
                $res[$key]['uruu']  = true;
                $res[$key]['chuki'] = false;
                $uruu_count++;
            }
        }

        return $res;
    }
    /* ----------------------------------------- */

    /**
     * +--
     *
     * @access      protected
     * @param       var_text $m
     * @return      int
     */
    protected function getKyurekiID($jd, $m)
    {
        $state = false;
        $c = count($m);
        for ($i = 0; $i < $c; $i++) {
            if ($jd < $m[$i]['jd']) {
                $state = true;
                break;
            } elseif ($jd == $m[$i]['jd']) {
                $state = true;
                break;
            }
        }

        $i--;

        if (!$state) {
            return 0;
        }

        return max($i, 0);
    }
    /* ----------------------------------------- */


    /**
     * +-- 朔配列から旧暦を求める。
     *
     * @access      protected
     * @param       int $jd
     * @param       array $m
     * @param       int $i OPTIONAL:NULL
     * @return      void
     */
    protected function getCalendarByTList($jd, array $m, $i = NULL)
    {
        if ($i === NULL) {
            $i = $this->getKyurekiID($jd, $m);
        }

        $kyureki = $m[$i];


        // 旧暦年の計算
        $gc = $this->JD2DateArray($jd);
        $kyureki['year'] = $gc[0];
        if ($kyureki['month'] > 9 && $kyureki['month'] > $gc[1]) {
            $kyureki['year']--;
        }

        $kyureki['day'] = $jd - $kyureki['jd'];
        $kyureki['time_stamp'] = mktime(0, 0, 0, $kyureki['month'], $kyureki['day'], $kyureki['year']);

        // 月齢を求める
        $kyureki['mage'] = $jd - $kyureki['jd'];
        if($kyureki['mage'] < 0) {
            $kyureki['mage'] = $jd - $m[$i - 1]['jd'];
        }
        $kyureki['magenoon'] = $jd + 0.5 - $kyureki['jd'];
        if($kyureki['magenoon'] < 0) {
            $kyureki['magenoon'] = $jd + .5 - $m[$i - 1]['jd'];
        }
        // 輝面比を求める
        $tm1 = $jd;
        $tm2 = $jd - $tm1;
        // JST ==> DT （補正時刻=0.0sec と仮定して計算）
        $tm2-=$this->tdt;
        $t = ($tm2 + 0.5) / 36525.0;
        $t = $t + ($tm1-2451545.0) / 36525.0;
        $rm_sun = $this->celestialLongitudeOfTheSun($t);
        $rm_moon = $this->celestialLongitudeOfTheMoon($t);
        $rm_angle = $this->angleNormalize($rm_moon - $rm_sun);
        $kyureki['illumi'] = (1 - cos($this->k * $rm_angle)) * 50;

        // 月相を求める（輝面比の計算で求めた変数 t を使用）
        $kyureki['mphase'] = (int)($rm_angle / 360 * 28 + .5);
        if($kyureki['mphase'] == 28) {
            $kyureki['mphase'] = 0;
        }

        // 朔
        $kyureki['tsuitachi_jd'] = $kyureki['jd'];


        // ユリウス暦
        $kyureki['jd'] = $jd;
        $kyureki['chuki'][0] = (int)($kyureki['chuki'][0]);

        // 中気かどうか
        if ($kyureki['chuki'][0] && $kyureki['chuki'][0] == $kyureki['jd']) {
            $kyureki['is_chuki'] = $kyureki['chuki'][1];
        } else {
            $kyureki['is_chuki'] = false;
        }
        $kyureki['chuki'] = $kyureki['chuki'][0];
        return $kyureki;
    }
    /* ----------------------------------------- */


    /**
     * +-- 中気の取得
     *
     * @access      protected
     * @param       int $tm
     * @param       int $longitude OPTIONAL:self::JD_CHU
     * @return      array
     */
    protected function getChu($tm, $longitude = self::JD_CHU)
    {
        // 時刻引数を分解する
        $tm1 = (int)($tm);
        $tm2 = $tm - $tm1;
        // JST ==> DT （補正時刻=0.0sec と仮定して計算）
        $tm2 -= $this->tdt;
        // 中気の黄経 Tsun0 を求める
        $t = ($tm2 + 0.5) / 36525.0;
        $t = $t + ($tm1 - 2451545.0) / 36525.0;
        $rm_sun  = $this->celestialLongitudeOfTheSun($t);
        $rm_sun0 = $longitude * (int)($rm_sun / $longitude);
        // 繰り返し計算によって中気の時刻を計算する
        // （誤差が±1.0 sec以内になったら打ち切る。）
        $delta_t1 = 0;
        $delta_t2 = 1.0;
        for (; abs($delta_t1 + $delta_t2) > (1.0 / 86400.0) ;) {
            // Tsun を計算
            $t = ($tm2 + 0.5) / 36525.0;
            $t = $t + ($tm1-2451545.0) / 36525.0;
            $rm_sun = $this->celestialLongitudeOfTheSun($t);
            // 黄経差 △T=Tsun -Tsun0
            $delta_rm = $rm_sun - $rm_sun0 ;
            // △Tの引き込み範囲（±180°）を逸脱した場合には、補正を行う
            if ($delta_rm > 180.0) {
                $delta_rm -= 360.0;
            } elseif ($delta_rm < -180.0) {
                $delta_rm += 360.0;
            }
            // 時刻引数の補正値 △t
            // delta_t = delta_rm * 365.2 / 360.0;
            $delta_t1 = (int)($delta_rm * 365.2 / 360.0);
            $delta_t2 = $delta_rm * 365.2 / 360.0;
            $delta_t2 -= $delta_t1;
            // 時刻引数の補正
            // tm -= delta_t;
            $tm1 = $tm1 - $delta_t1;
            $tm2 = $tm2 - $delta_t2;
            if ($tm2 < 0) {
                $tm2 += 1.0;
                $tm1 -= 1.0;
            }
        }
        // 戻り値の作成
        // chu[i, 0]:時刻引数を合成するのと、DT ==> JST 変換を行い、戻り値とする
        // （補正時刻=0.0sec と仮定して計算）
        // chu[i, 1]:黄経
        $temp[0] = $tm2 + $this->tdt;
        $temp[0] += $tm1;
        $temp[1] = $rm_sun0;
        return array($temp[0], $temp[1]);
    }
    /* ----------------------------------------- */



    /**
     * +-- 朔の取得
     *
     * @access      protected
     * @param       int $tm
     * @return      int
     */
    protected function getTsuitachi($tm)
    {
        // ループカウンタのセット
        $lc = 1;
        // 時刻引数を分解する
        $tm1 = (int)($tm);
        $tm2 = $tm - $tm1;
        // JST ==> DT （補正時刻=0.0sec と仮定して計算）
        $tm2 -= $this->tdt;
        // 繰り返し計算によって朔の時刻を計算する
        // （誤差が±1.0 sec以内になったら打ち切る。）
        $delta_t1 = 0;
        $delta_t2 = 1.0;
        for (; abs($delta_t1 + $delta_t2) > (1.0 / 86400.0) ; $lc++) {
            // 太陽の黄経Tsun , 月の黄経Tmoon を計算
            // t = (tm - 2451548.0 + 0.5)/36525.0;
            $t = ($tm2 + 0.5) / 36525.0;
            $t = $t + ($tm1-2451545.0) / 36525.0;
            $rm_sun  = $this->celestialLongitudeOfTheSun($t);
            $rm_moon = $this->celestialLongitudeOfTheMoon($t);
            // 月と太陽の黄経差△T
            // △T=Tmoon-Tsun
            $delta_rm = $rm_moon - $rm_sun ;
            // ループの１回目（lc=1）で delta_rm < 0.0 の場合には引き込み範囲に
            // 入るように補正する
            if ($lc === 1 && $delta_rm < 0.0) {
                $delta_rm = $this->angleNormalize($delta_rm);
            } elseif ($rm_sun >= 0 && $rm_sun <= 20 && $rm_moon >= 300) {
                // 春分の近くで朔がある場合（0 ≦Tsun≦ 20）で、月の黄経Tmoon≧300 の
                // 場合には、△T= 360.0 - △T と計算して補正する
                $delta_rm = $this->angleNormalize($delta_rm);
                $delta_rm = 360.0 - $delta_rm;
            } elseif (abs($delta_rm) > 40.0) {
                // △Tの引き込み範囲（±40°）を逸脱した場合には、補正を行う
                $delta_rm = $this->angleNormalize($delta_rm);
            }
            // 時刻引数の補正値 △t
            // delta_t = delta_rm * 29.530589 / 360.0;
            $delta_t1 = (int)($delta_rm * 29.530589 / 360.0);
            $delta_t2 = $delta_rm * 29.530589 / 360.0;
            $delta_t2 -= $delta_t1;
            // 時刻引数の補正
            // tm -= delta_t;
            $tm1 = $tm1 - $delta_t1;
            $tm2 = $tm2 - $delta_t2;
            if ($tm2 < 0.0) {
                $tm2+=1.0;
                $tm1-=1.0;
            }

            if ($lc == 15 && abs($delta_t1 + $delta_t2) > (1.0 / 86400.0)) {
                // ループ回数が15回になったら、初期値 tm を tm-26 とする。
                $tm1 = (int)($tm-26);
                $tm2 = 0;
            } elseif ($lc > 30 && abs($delta_t1 + $delta_t2) > (1.0 / 86400.0)) {
                // 初期値を補正したにも関わらず、振動を続ける場合には初期値を答えとして
                // 返して強制的にループを抜け出して異常終了させる。
                $tm1 = $tm;
                $tm2 = 0;
                break;
            }
        }
        // 時刻引数を合成するのと、DT ==> JST 変換を行い、戻り値とする
        // （補正時刻=0.0sec と仮定して計算）
        return($tm2 + $tm1 + $this->tdt);
    }
    /* ----------------------------------------- */


    /**
     * +-- 角度正規化
     *
     * @access      protected
     * @param       float $angle 角度
     * @return      float 角度
     */
    protected function angleNormalize($angle)
    {
        if ($angle < 0) {
            $angle1 = -$angle;
            $angle1 -= 360 * (int)($angle1 / 360);
            $angle1 = 360 - $angle1;
        } else{
            $angle1 = $angle - 360 * (int)($angle / 360);
        }
        return $angle1;
    }
    /* ----------------------------------------- */


    /**
     * +--
     *
     * @access      protected
     * @param       var_text $t
     * @return      float
     */
    protected function celestialLongitudeOfTheSun($t)
    {
        $ang = $this->angleNormalize(31557.0 * $t + 161.0);
        $th  = 0.0004 * cos($this->k * $ang);
        $ang = $this->angleNormalize(29930.0 * $t + 48.0);
        $th  = $th + 0.0004 * cos($this->k * $ang);
        $ang = $this->angleNormalize(2281.0 * $t + 221.0);
        $th  = $th + 0.0005 * cos($this->k * $ang);
        $ang = $this->angleNormalize(155.0 * $t + 118.0);
        $th  = $th + 0.0005 * cos($this->k * $ang);
        $ang = $this->angleNormalize(33718.0 * $t + 316.0);
        $th  = $th + 0.0006 * cos($this->k * $ang);
        $ang = $this->angleNormalize(9038.0 * $t + 64.0);
        $th  = $th + 0.0007 * cos($this->k * $ang);
        $ang = $this->angleNormalize(3035.0 * $t + 110.0);
        $th  = $th + 0.0007 * cos($this->k * $ang);
        $ang = $this->angleNormalize(65929.0 * $t + 45.0);
        $th  = $th + 0.0007 * cos($this->k * $ang);
        $ang = $this->angleNormalize(22519.0 * $t + 352.0);
        $th  = $th + 0.0013 * cos($this->k * $ang);
        $ang = $this->angleNormalize(45038.0 * $t + 254.0);
        $th  = $th + 0.0015 * cos($this->k * $ang);
        $ang = $this->angleNormalize(445267.0 * $t + 208.0);
        $th  = $th + 0.0018 * cos($this->k * $ang);
        $ang = $this->angleNormalize(19.0 * $t + 159.0);
        $th  = $th + 0.0018 * cos($this->k * $ang);
        $ang = $this->angleNormalize(32964.0 * $t + 158.0);
        $th  = $th + 0.0020 * cos($this->k * $ang);
        $ang = $this->angleNormalize(71998.1 * $t + 265.1);
        $th  = $th + 0.0200 * cos($this->k * $ang);
        $ang = $this->angleNormalize(35999.05 * $t + 267.52);
        $th  = $th - 0.0048 * $t * cos($this->k * $ang) ;
        $th  = $th + 1.9147 * cos($this->k * $ang) ;
        // 比例項の計算
        $ang = $this->angleNormalize(36000.7695 * $t);
        $ang = $this->angleNormalize($ang + 280.4659);
        $th  = $this->angleNormalize($th + $ang);
        return($th);
    }
    /* ----------------------------------------- */

    /**
     * +--
     *
     * @access      protected
     * @param       var_text $t
     * @return      float
     */
    protected function celestialLongitudeOfTheMoon($t)
    {
        // 摂動項の計算
        $ang = $this->angleNormalize(2322131.0 * $t + 191.0);
        $th  = 0.0003 * cos($this->k * $ang);
        $ang = $this->angleNormalize(4067.0 * $t + 70.0);
        $th  = $th + 0.0003 * cos($this->k * $ang);
        $ang = $this->angleNormalize(549197.0 * $t + 220.0);
        $th  = $th + 0.0003 * cos($this->k * $ang);
        $ang = $this->angleNormalize(1808933.0 * $t + 58.0);
        $th  = $th + 0.0003 * cos($this->k * $ang);
        $ang = $this->angleNormalize(349472.0 * $t + 337.0);
        $th  = $th + 0.0003 * cos($this->k * $ang);
        $ang = $this->angleNormalize(381404.0 * $t + 354.0);
        $th  = $th + 0.0003 * cos($this->k * $ang);
        $ang = $this->angleNormalize(958465.0 * $t + 340.0);
        $th  = $th + 0.0003 * cos($this->k * $ang);
        $ang = $this->angleNormalize(12006.0 * $t + 187.0);
        $th  = $th + 0.0004 * cos($this->k * $ang);
        $ang = $this->angleNormalize(39871.0 * $t + 223.0);
        $th  = $th + 0.0004 * cos($this->k * $ang);
        $ang = $this->angleNormalize(509131.0 * $t + 242.0);
        $th  = $th + 0.0005 * cos($this->k * $ang);
        $ang = $this->angleNormalize(1745069.0 * $t + 24.0);
        $th  = $th + 0.0005 * cos($this->k * $ang);
        $ang = $this->angleNormalize(1908795.0 * $t + 90.0);
        $th  = $th + 0.0005 * cos($this->k * $ang);
        $ang = $this->angleNormalize(2258267.0 * $t + 156.0);
        $th  = $th + 0.0006 * cos($this->k * $ang);
        $ang = $this->angleNormalize(111869.0 * $t + 38.0);
        $th  = $th + 0.0006 * cos($this->k * $ang);
        $ang = $this->angleNormalize(27864.0 * $t + 127.0);
        $th  = $th + 0.0007 * cos($this->k * $ang);
        $ang = $this->angleNormalize(485333.0 * $t + 186.0);
        $th  = $th + 0.0007 * cos($this->k * $ang);
        $ang = $this->angleNormalize(405201.0 * $t + 50.0);
        $th  = $th + 0.0007 * cos($this->k * $ang);
        $ang = $this->angleNormalize(790672.0 * $t + 114.0);
        $th  = $th + 0.0007 * cos($this->k * $ang);
        $ang = $this->angleNormalize(1403732.0 * $t + 98.0);
        $th  = $th + 0.0008 * cos($this->k * $ang);
        $ang = $this->angleNormalize(858602.0 * $t + 129.0);
        $th  = $th + 0.0009 * cos($this->k * $ang);
        $ang = $this->angleNormalize(1920802.0 * $t + 186.0);
        $th  = $th + 0.0011 * cos($this->k * $ang);
        $ang = $this->angleNormalize(1267871.0 * $t + 249.0);
        $th  = $th + 0.0012 * cos($this->k * $ang);
        $ang = $this->angleNormalize(1856938.0 * $t + 152.0);
        $th  = $th + 0.0016 * cos($this->k * $ang);
        $ang = $this->angleNormalize(401329.0 * $t + 274.0);
        $th  = $th + 0.0018 * cos($this->k * $ang);
        $ang = $this->angleNormalize(341337.0 * $t + 16.0);
        $th  = $th + 0.0021 * cos($this->k * $ang);
        $ang = $this->angleNormalize(71998.0 * $t + 85.0);
        $th  = $th + 0.0021 * cos($this->k * $ang);
        $ang = $this->angleNormalize(990397.0 * $t + 357.0);
        $th  = $th + 0.0021 * cos($this->k * $ang);
        $ang = $this->angleNormalize(818536.0 * $t + 151.0);
        $th  = $th + 0.0022 * cos($this->k * $ang);
        $ang = $this->angleNormalize(922466.0 * $t + 163.0);
        $th  = $th + 0.0023 * cos($this->k * $ang);
        $ang = $this->angleNormalize(99863.0 * $t + 122.0);
        $th  = $th + 0.0024 * cos($this->k * $ang);
        $ang = $this->angleNormalize(1379739.0 * $t + 17.0);
        $th  = $th + 0.0026 * cos($this->k * $ang);
        $ang = $this->angleNormalize(918399.0 * $t + 182.0);
        $th  = $th + 0.0027 * cos($this->k * $ang);
        $ang = $this->angleNormalize(1934.0 * $t + 145.0);
        $th  = $th + 0.0028 * cos($this->k * $ang);
        $ang = $this->angleNormalize(541062.0 * $t + 259.0);
        $th  = $th + 0.0037 * cos($this->k * $ang);
        $ang = $this->angleNormalize(1781068.0 * $t + 21.0);
        $th  = $th + 0.0038 * cos($this->k * $ang);
        $ang = $this->angleNormalize(133.0 * $t + 29.0);
        $th  = $th + 0.0040 * cos($this->k * $ang);
        $ang = $this->angleNormalize(1844932.0 * $t + 56.0);
        $th  = $th + 0.0040 * cos($this->k * $ang);
        $ang = $this->angleNormalize(1331734.0 * $t + 283.0);
        $th  = $th + 0.0040 * cos($this->k * $ang);
        $ang = $this->angleNormalize(481266.0 * $t + 205.0);
        $th  = $th + 0.0050 * cos($this->k * $ang);
        $ang = $this->angleNormalize(31932.0 * $t + 107.0);
        $th  = $th + 0.0052 * cos($this->k * $ang);
        $ang = $this->angleNormalize(926533.0 * $t + 323.0);
        $th  = $th + 0.0068 * cos($this->k * $ang);
        $ang = $this->angleNormalize(449334.0 * $t + 188.0);
        $th  = $th + 0.0079 * cos($this->k * $ang);
        $ang = $this->angleNormalize(826671.0 * $t + 111.0);
        $th  = $th + 0.0085 * cos($this->k * $ang);
        $ang = $this->angleNormalize(1431597.0 * $t + 315.0);
        $th  = $th + 0.0100 * cos($this->k * $ang);
        $ang = $this->angleNormalize(1303870.0 * $t + 246.0);
        $th  = $th + 0.0107 * cos($this->k * $ang);
        $ang = $this->angleNormalize(489205.0 * $t + 142.0);
        $th  = $th + 0.0110 * cos($this->k * $ang);
        $ang = $this->angleNormalize(1443603.0 * $t + 52.0);
        $th  = $th + 0.0125 * cos($this->k * $ang);
        $ang = $this->angleNormalize(75870.0 * $t + 41.0);
        $th  = $th + 0.0154 * cos($this->k * $ang);
        $ang = $this->angleNormalize(513197.9 * $t + 222.5);
        $th  = $th + 0.0304 * cos($this->k * $ang);
        $ang = $this->angleNormalize(445267.1 * $t + 27.9);
        $th  = $th + 0.0347 * cos($this->k * $ang);
        $ang = $this->angleNormalize(441199.8 * $t + 47.4);
        $th  = $th + 0.0409 * cos($this->k * $ang);
        $ang = $this->angleNormalize(854535.2 * $t + 148.2);
        $th  = $th + 0.0458 * cos($this->k * $ang);
        $ang = $this->angleNormalize(1367733.1 * $t + 280.7);
        $th  = $th + 0.0533 * cos($this->k * $ang);
        $ang = $this->angleNormalize(377336.3 * $t + 13.2);
        $th  = $th + 0.0571 * cos($this->k * $ang);
        $ang = $this->angleNormalize(63863.5 * $t + 124.2);
        $th  = $th + 0.0588 * cos($this->k * $ang);
        $ang = $this->angleNormalize(966404.0 * $t + 276.5);
        $th  = $th + 0.1144 * cos($this->k * $ang);
        $ang = $this->angleNormalize(35999.05 * $t + 87.53);
        $th  = $th + 0.1851 * cos($this->k * $ang);
        $ang = $this->angleNormalize(954397.74 * $t + 179.93);
        $th  = $th + 0.2136 * cos($this->k * $ang);
        $ang = $this->angleNormalize(890534.22 * $t + 145.7);
        $th  = $th + 0.6583 * cos($this->k * $ang);
        $ang = $this->angleNormalize(413335.35 * $t + 10.74);
        $th  = $th + 1.2740 * cos($this->k * $ang);
        $ang = $this->angleNormalize(477198.868 * $t + 44.963);
        $th  = $th + 6.2888 * cos($this->k * $ang);
        // 比例項の計算
        $ang = $this->angleNormalize(481267.8809 * $t);
        $ang = $this->angleNormalize($ang + 218.3162);
        $th  = $this->angleNormalize($th + $ang);
        return($th);
    }
    /* ----------------------------------------- */

    /**
     * +--
     *
     * @access      protected
     * @param       var_text $hour
     * @param       var_text $minute
     * @param       var_text $second
     * @param       var_text $month
     * @param       var_text $day
     * @param       var_text $year
     * @return      float
     */
    protected function makeJD($hour, $minute, $second, $month, $day, $year)
        {
        if ($month < 3.0) {
            $year -= 1.0;
            $month += 12.0;
        }
        $jd  = $this->flce(365.25 * $year);
        $jd += $this->flce($year / 400.0);
        $jd -= $this->flce($year / 100.0);
        $jd += $this->flce(30.59 * ($month-2.0));
        $jd += 1721088;
        $jd += $day;
        $t   = $second / 3600.0;
        $t  += $minute /60.0;
        $t  += $hour;
        $t   = $t / 24.0;
        $jd += $t;
        return($jd)+1;
    }
    /* ----------------------------------------- */


    protected function JD2DateArray($JD)
    {
        $x0 = (int)($JD + 68570.0);
        $x1 = (int)($x0 / 36524.25);
        $x2 = $x0 - (int)(36524.25 * $x1 + 0.75);
        $x3 = (int)(($x2 + 1) / 365.2425);
        $x4 = $x2 - (int)(365.25 * $x3) + 31.0;
        $x5 = (int)((int)($x4) / 30.59);
        $x6 = (int)((int)($x5) / 11.0);
        $res[2] = $x4 - (int)(30.59 * $x5);
        $res[1] = $x5 - 12 * $x6 + 2;
        $res[0] = 100 * ($x1 - 49) + $x3 + $x6;
        // 2月30日の補正
        if ($res[1]==2 && $res[2] > 28) {
            if ($res[0] % 100 == 0 && $res[0] % 400 == 0) {
                $res[2] = 29;
            } elseif ($res[0] % 4 == 0) {
                $res[2] = 29;
            } else{
                $res[2] = 28;
            }
        }
        return array($res[0], $res[1], $res[2]);

        // $tm = 86400.0 * ($JD - (int)($JD));

        // $res[3] = (int)($tm / 3600.0);
        // $res[4] = (int)(($tm - 3600.0 * $res[3]) / 60.0);
        // $res[5] = (int)($tm - 3600.0 * $res[3] - 60 * $res[4]);
        // return array($res[0], $res[1], $res[2], $res[3], $res[4], $res[5]);
    }

}
