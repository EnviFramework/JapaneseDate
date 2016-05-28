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
class LunarCalendarTest extends testCaseBase
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

    /* ----------------------------------------- */

    /**
     * +-- dataProvider
     *
     * @access      public
     * @return      array
     */
    public function createTestObject()
    {
        $LunarCalendar = new \JapaneseDate\LunarCalendar();
        $JapaneseDateTime = new JapaneseDateTime();
        return array($LunarCalendar, $JapaneseDateTime);
    }
    /* ----------------------------------------- */

    /**
     * +-- getChu
     *
     * @access      public
     * @return      void
     * @dataProvider createTestObject
     */
    public function getChuTest($LunarCalendar, $JapaneseDateTime)
    {
        $res = $this->call($LunarCalendar, 'getChu', array(gregoriantojd(06, 21, 2016), \JapaneseDate\LunarCalendar::JD_BEFORE_NIBUN));
        $this->assertEquals('2016-06-21', date('Y-m-d', $LunarCalendar->JD2Timestamp($res[0])));

    }
    /* ----------------------------------------- */
    /**
     * +-- makeJD
     *
     * @access      public
     * @return      void
     * @dataProvider createTestObject
     */
    public function makeJDTest($LunarCalendar, $JapaneseDateTime)
    {
        $this->assertEquals(
            gregoriantojd(06, 21, 2016),
            $LunarCalendar->toIntJD($this->call($LunarCalendar, 'makeJD', array(9, 0, 0, 06, 21, 2016, 12)))
        );
    }

    /**
     * +-- getTsuitachiList
     *
     * @access      public
     * @return      void
     * @dataProvider createTestObject
     */
    public function getTsuitachiListTest($LunarCalendar, $JapaneseDateTime)
    {
        $res = $this->call($LunarCalendar, 'getTsuitachiList', array(gregoriantojd(06, 21, 2016), 12));
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
