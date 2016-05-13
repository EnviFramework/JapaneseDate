<?php
/**
 *
 *
 *
 * PHP versions 5
 *
 *
 *
 * @category   %%project_category%%
 * @package    %%project_name%%
 * @subpackage %%subpackage_name%%
 * @author     %%your_name%% <%%your_email%%>
 * @copyright  %%your_project%%
 * @license    %%your_license%%
 * @version    GIT: $Id$
 * @link       %%your_link%%
 * @see        http://www.enviphp.net/c/man/v3/core/unittest
 * @since      File available since Release 1.0.0
 * @doc_ignore
 */


/**
 *
 *
 *
 *
 * @category   %%project_category%%
 * @package    %%project_name%%
 * @subpackage %%subpackage_name%%
 * @author     %%your_name%% <%%your_email%%>
 * @copyright  %%your_project%%
 * @license    %%your_license%%
 * @version    GIT: $Id$
 * @link       %%your_link%%
 * @see        http://www.enviphp.net/c/man/v3/core/unittest
 * @since      File available since Release 1.0.0
 * @doc_ignore
 */
class JapaneseDateTest extends testCaseBase
{
    /**
     * +-- 初期化
     *
     * @access public
     * @return void
     */
    public function initialize()
    {
        $this->free();
    }
    /* ----------------------------------------- */

    /**
     * +--
     *
     * @access      public
     * @return      void
     * @cover         JapaneseDate::__construct
     * @cover         JapaneseDate::getWorkingDayBySpan
     * @cover         JapaneseDate::withoutLuna
     * @cover         JapaneseDate::withLuna
     * @cover         JapaneseDate::toTimeStamp
     */
    public function getWorkingDayBySpanNoneTest()
    {
        $jd = new JapaneseDate;
        $jd->withoutLuna();
        $res = $jd->getWorkingDayBySpan(
            strtotime('2016-06-30 00:00:00'),
            strtotime('2016-06-01 00:00:00'),
            true,
            [],
            []
        );
        $this->assertArray($res);
        $this->assertCount(0, $res);
    }
    /* ----------------------------------------- */

    /**
     * +--
     *
     * @access      public
     * @return      void
     * @cover         JapaneseDate::__construct
     * @cover         JapaneseDate::getWorkingDayBySpan
     * @cover         JapaneseDate::withoutLuna
     * @cover         JapaneseDate::withLuna
     * @cover         JapaneseDate::toTimeStamp
     */
    public function getWorkingDayBySpanTest()
    {
        $jd = new JapaneseDate;
        $jd->withoutLuna();
        $res = $jd->getWorkingDayBySpan(
            strtotime('2016-01-01 00:00:00'),
            strtotime('2016-12-31 00:00:00'),
            false,
            [],
            []
        );
        $this->assertArray($res);
        $this->assertCount(366, $res);
    }
    /* ----------------------------------------- */

    /**
     * +--
     *
     * @access      public
     * @return      void
     * @cover         JapaneseDate::__construct
     * @cover         JapaneseDate::getWorkingDayBySpan
     * @cover         JapaneseDate::withoutLuna
     * @cover         JapaneseDate::withLuna
     * @cover         JapaneseDate::toTimeStamp
     */
    public function getWorkingDayByWithWeekAndDayTest()
    {
        $jd = new JapaneseDate;
        $jd->withoutLuna();
        $res = $jd->getWorkingDayBySpan(
            strtotime('2016-01-01 00:00:00'),
            strtotime('2016-12-31 00:00:00'),
            true,
            [JapaneseDate::SUNDAY, JapaneseDate::SATURDAY],
            ['2016-05-02', strtotime('2016-05-06')]
        );
        $this->assertArray($res);

        $this->assertCount(245-2, $res);
    }
    /* ----------------------------------------- */

    /**
     * +--
     *
     * @access      public
     * @return      void
     * @cover         JapaneseDate::__construct
     * @cover         JapaneseDate::getWorkingDayBySpan
     * @cover         JapaneseDate::withoutLuna
     * @cover         JapaneseDate::withLuna
     * @cover         JapaneseDate::toTimeStamp
     */
    public function getWorkingDayByWithWeekAndDayUseStringDateTest()
    {
        $jd = new JapaneseDate;
        $jd->withLuna();
        $res = $jd->getWorkingDayBySpan(
            ('2016-04-29 00:00:00'),
            ('2016-05-09 00:00:00'),
            true,
            [JapaneseDate::SUNDAY, JapaneseDate::SATURDAY],
            [(string)strtotime('2016-05-02'), strtotime('2016-05-06')]
        );
        $this->assertArray($res);
        $this->assertCount(1, $res);
    }
    /* ----------------------------------------- */

    /**
     * +--
     *
     * @access      public
     * @return      void
     * @cover         JapaneseDate::__construct
     * @cover         JapaneseDate::getWorkingDayBySpan
     * @cover         JapaneseDate::withoutLuna
     * @cover         JapaneseDate::withLuna
     * @cover         JapaneseDate::toTimeStamp
     */
    public function getWorkingDayByWithWeekAndDayUseStringDateEmptyTest()
    {
        $jd = new JapaneseDate;
        $jd->withLuna();
        $res = $jd->getWorkingDayBySpan(
            ('2016-04-29 00:00:00'),
            ('2016-05-08 00:00:00'),
            true,
            [JapaneseDate::SUNDAY, JapaneseDate::SATURDAY],
            [(string)strtotime('2016-05-02'), strtotime('2016-05-06')]
        );
        $this->assertArray($res);
        $this->assertCount(0, $res);
    }
    /* ----------------------------------------- */



    /**
     * +-- 終了処理
     *
     * @access public
     * @return void
     */
    public function shutdown()
    {
    }

}
