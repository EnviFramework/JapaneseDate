<?php
// +----------------------------------------------------------------------+
// |                          Japanese Date                               |
// +----------------------------------------------------------------------+
// | PHP Version 4・5                                                     |
// +----------------------------------------------------------------------+
// | Copyright (c) 2002-2006 The Artisan Member                           |
// +----------------------------------------------------------------------+
// | Authors: Akito<akito-artisan@five-foxes.com>                         |
// +----------------------------------------------------------------------+
//
//サンプルコード
require_once("libs/JapaneseDate.php");
// オブジェクトの作成
$jd = new JapaneseDate();

?>
<html>
<style type="text/css"><!--
.holiday{
	text-align:right;
	background-color: #ffd2d2;
	font-size : 90%;
}

.sunday{
	text-align:right;
	background-color: #ff7575;
	font-size : 90%;
}

.saturday{
	text-align:right;
	background-color: #9b9bff;
	font-size : 90%;

}

.weekday{
	text-align:right;
	font-size : 90%;

}

.calendar_sunday{
	font-size: 13px;
}

.calendar_weekday{
	font-size: 13px;
}

.calendar_saturday{
	font-size: 13px;
}
--></style>

<body>
<?php


// 年のフォームデータの受け取り
if (isset($_GET["y"]) ? (strlen($_GET["y"]) == 4 && is_numeric($_GET["y"]) ? (int)$_GET["y"] < 1980 : true) : true) {
	$year = date("Y");
} else {
	$year = (int)$_GET["y"];
}
?>
<form>
<input type="text" name="y" value="<?php echo $year; ?>">年
<input type="submit" value="表示">
</form>
////////////////////////////mb_strftimeのサンプル////////////////////////////<br />
<br />
<?php
echo $jd->mb_strftime("
%%%Y-%m-%d%%<table>
<tr><td>西暦</td><td>%Y</td></tr>
<tr><td>月1</td><td>%m</td></tr>
<tr><td>日1</td><td>%d</td></tr>
<tr><td>月2</td><td>%N</td></tr>
<tr><td>日2</td><td>%J</td></tr>
<tr><td>月3</td><td>%G</td></tr>
<tr><td>日3</td><td>%g</td></tr>
<tr><td>干支番号</td><td>%o</td></tr>
<tr><td>干支</td><td>%O</td></tr>
<tr><td>祝日番号</td><td>%l</td></tr>
<tr><td>祝日</td><td>%L</td></tr>
<tr><td>7曜番号</td><td>%w</td></tr>
<tr><td>7曜表示</td><td>%K</td></tr>
<tr><td>6曜番号</td><td>%6</td></tr>
<tr><td>6曜表示</td><td>%k</td></tr>
<tr><td>年号ID</td><td>%f</td></tr>
<tr><td>年号</td><td>%F</td></tr>
<tr><td>和暦</td><td>%E</td></tr>
</table>
", mktime(0,0,0,5,3,2005));

?>
<br />
<?php
echo $jd->mb_strftime("
%%%Y-%m-%d%%<table>
<tr><td>西暦</td><td>%Y</td></tr>
<tr><td>月1</td><td>%m</td></tr>
<tr><td>日1</td><td>%d</td></tr>
<tr><td>月2</td><td>%N</td></tr>
<tr><td>日2</td><td>%J</td></tr>
<tr><td>月3</td><td>%G</td></tr>
<tr><td>日3</td><td>%g</td></tr>
<tr><td>干支番号</td><td>%o</td></tr>
<tr><td>干支</td><td>%O</td></tr>
<tr><td>祝日番号</td><td>%l</td></tr>
<tr><td>祝日</td><td>%L</td></tr>
<tr><td>7曜番号</td><td>%w</td></tr>
<tr><td>7曜表示</td><td>%K</td></tr>
<tr><td>6曜番号</td><td>%6</td></tr>
<tr><td>6曜表示</td><td>%k</td></tr>
<tr><td>年号ID</td><td>%f</td></tr>
<tr><td>年号</td><td>%F</td></tr>
<tr><td>和暦</td><td>%E</td></tr>
</table>
");

?>
<br />
////////////////////////////カレンダー出力のサンプル////////////////////////////<br />
<br />

<?php
$month_array = range(1, 12);
foreach ($month_array as $month) {
?>
<?php echo $year; ?>年
(
<?php echo $jd->viewEraName($jd->getEraName(mktime(0, 0, 0, 1,$month, $year))).$jd->getEraYear(mktime(0, 0, 0, 1,$month, $year)); ?>年
/<?php echo $jd->viewOrientalZodiac($jd->getOrientalZodiac(mktime(0, 0, 0, 1,$month, $year))); ?>
)<br />
<?php
echo $month;
?>
月(<?php echo $jd->viewMonth($month);?>)

<?php
$noday = "-";
$_from = $jd->getCalendar($year, $month);
if (!is_array($_from) && !is_object($_from)) {
	settype($_from, 'array');
}
$_foreach['calendar'] = array('total' => count($_from), 'iteration' => 0);

if ($_foreach['calendar']['total'] > 0):
	foreach ($_from as $key => $value):
	$_foreach['calendar']['iteration']++;
		if (($_foreach['calendar']['iteration'] <= 1)):
?>

<table border="1" cellspacing="0" cellpadding="0" summary="カレンダー" width="700">
<tr class="calendarhead">
<th class="calendar_sunday" align="middle" abbr="sunday" width="100">
日
</th>
<th class="calendar_weekday" align="middle" abbr="monday" width="100">
月
</th>
<th class="calendar_weekday" align="middle" abbr="tuesday" width="100">
火
</th>
<th class="calendar_weekday" align="middle" abbr="wednesday" width="100">
水
</th>
<th class="calendar_weekday" align="middle" abbr="thursday" width="100">
木
</th>
<th class="calendar_weekday" align="middle" abbr="friday" width="100">
金
</th>
<th class="calendar_saturday" align="middle" abbr="saturday" width="100">
土
</th>
</tr>
<?php endif; ?>
<?php if ($value['week'] == 0): ?>
<tr class="calendarday">
<td class="<?php if ($value['holiday'] == 0): ?>sunday<?php else: ?>holiday<?php endif; ?>">
<?php echo $value['day']; ?><br />
<?php echo $jd->viewSixWeekday($value["sixweek"]);?><br />
<small><?php echo $value['luna_year']; ?>/
<?php if ($value["uruu"]) : ?>
(閏)
<?php endif; ?>
<?php echo $jd->viewMonth($value['luna_month']); ?>
<?php echo $value['luna_day']; ?><br /></small><?php if ($value["holiday"] != 0) : ?>
<?php echo $jd->viewHoliday($value["holiday"]); ?>
<?php endif; ?>
</td>
<?php elseif ($value['week'] > 0 && ($_foreach['calendar']['iteration'] <= 1)): ?>
<tr class="calendarday">
<td class="weekday"><?php echo $noday;?></td>
<?php endif;  if ($value['week'] > 1 && ($_foreach['calendar']['iteration'] <= 1)): ?>
<td class="weekday"><?php echo $noday;?></td>
<?php endif;  if ($value['week'] > 2 && ($_foreach['calendar']['iteration'] <= 1)): ?>
<td class="weekday"><?php echo $noday;?></td>
<?php endif;  if ($value['week'] > 3 && ($_foreach['calendar']['iteration'] <= 1)): ?>
<td class="weekday"><?php echo $noday;?></td>
<?php endif;  if ($value['week'] > 4 && ($_foreach['calendar']['iteration'] <= 1)): ?>
<td class="weekday"><?php echo $noday;?></td>
<?php endif;  if ($value['week'] > 5 && ($_foreach['calendar']['iteration'] <= 1)): ?>
<td class="weekday"><?php echo $noday;?></td>
<?php endif;  if ($value['week'] == 6): ?>
<td class="<?php if ($value['holiday'] == 0): ?>saturday<?php else: ?>holiday<?php endif; ?>">
<?php echo $value['day']; ?><br />
<?php echo $jd->viewSixWeekday($value["sixweek"]);?><br />
<small><?php echo $value['luna_year']; ?>/
<?php if ($value["uruu"]) : ?>
(閏)
<?php endif; ?>
<?php echo $jd->viewMonth($value['luna_month']); ?>
<?php echo $value['luna_day']; ?><br /></small>
<?php if ($value["holiday"] != 0) : ?>
<?php echo $jd->viewHoliday($value["holiday"]); ?>
<?php endif; ?>
</td>
</tr>

<?php elseif ($value['week'] > 0): ?>
<td class="<?php if ($value['holiday'] == 0): ?>weekday<?php else: ?>holiday<?php endif; ?>">
<?php echo $value['day']; ?><br />
<?php echo $jd->viewSixWeekday($value["sixweek"]);?><br />
<small><?php echo $value['luna_year']; ?>/
<?php if ($value["uruu"]) : ?>
(閏)
<?php endif; ?>
<?php echo $jd->viewMonth($value['luna_month']); ?>
<?php echo $value['luna_day']; ?><br /></small><?php if ($value["holiday"] != 0) : ?>
<?php echo $jd->viewHoliday($value["holiday"]); ?>
<?php endif; ?>
</td>
<?php endif;  if (($_foreach['calendar']['iteration'] == $_foreach['calendar']['total'])):  if ($value['week'] < 1): ?>
<td class="weekday"><?php echo $noday;?></td>
<?php endif;  if ($value['week'] < 2): ?>
<td class="weekday"><?php echo $noday;?></td>
<?php endif;  if ($value['week'] < 3): ?>
<td class="weekday"><?php echo $noday;?></td>
<?php endif;  if ($value['week'] < 4): ?>
<td class="weekday"><?php echo $noday;?></td>
<?php endif;  if ($value['week'] < 5): ?>
<td class="weekday"><?php echo $noday;?></td>
<?php endif;  if ($value['week'] < 6): ?>
<td class="weekday"><?php echo $noday;?></td>
<?php endif; ?>
</table>
<?php endif;  endforeach; endif; unset($_from); ?>
<hr>
<?php
}
?>

</body>
</html>