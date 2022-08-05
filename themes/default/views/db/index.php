<script language="javascript">

/** show more menus **/
function showMoreMenus(link) {
	var obj = $(link);
	setManualPosition(".menu", obj.position().left, obj.position().top + obj.height() + 4);
}


/** show manual links **/
function setManualPosition(className, x, y) {
	if ($(className).is(":visible")) {
		$(className).hide();
	} else {
		window.setTimeout(function () {
			$(className).show();
			$(className).css("left", x);
			$(className).css("top", y)
		}, 100);
		$(className).find("a").click(function () {
			hideMenus();
		});
	}
}

/** hide menus **/
function hideMenus() {
	$(".menu").hide();
}

$(function () {
	$(document).click(hideMenus);
});
</script>

<h3><?php render_navigation($db); ?></h3>

<div class="operation">
	<?php render_db_menu($db) ?>
</div>

<table bgcolor="#cccccc" cellpadding="2" cellspacing="1" width="800">
	<tr>
		<td colspan="2" style="text-align:center;font-weight:bold"><a href="http://docs.mongodb.org/manual/reference/command/dbStats/#dbcmd.dbStats" target="_blank">Database Statistics</a> ({dbStats:1})</td>
	</tr>
	<?php foreach ($stats as $param=>$value):?>
	<tr bgcolor="#fffeee">
		<td width="120" valign="top"><?php h($param);?></td>
		<td><?php h($value);?></td>
	</tr>
	<?php endforeach; ?>
</table>
<div class="gap"></div>

<table bgcolor="#cccccc" cellpadding="2" cellspacing="1" width="800">
	<tr>
		<td colspan="7" style="text-align:center;font-weight:bold"><a href="http://docs.mongodb.org/manual/reference/command/dbStats/#dbcmd.dbStats" target="_blank">Collections Statistics</a> ({collStats:1})</td>
	</tr>
	<tr>
		<th><?php hm("name"); ?></th>
		<th><?php hm("size"); ?></th>
		<th nowrap><?php hm("storagesize"); ?></th>
		<th nowrap><?php hm("datasize"); ?></th>
		<th nowrap><?php hm("indexsize"); ?></th>
		<th><?php hm("Indexs"); ?></th>
		<th><?php hm("objects"); ?></th>
	</tr>
	<?php foreach ($colls_stats as $db):?>
	<tr bgcolor="#fffeee">
		<td width="120" valign="top"><a href="<?php h(url("db.index", array("db"=>$db["Name"]))); ?>"><?php h($db["Name"]);?></a></td>
		<td width="80"><?php h($db["Size"]);?></td>
		<td width="80"><?php h($db["DiskSize"]);?></td>
		<td width="80"><?php h($db["ObjSize"]);?></td>
		<td width="80"><?php h($db["IdxSize"]);?></td>
		<td width="80"><?php h($db["IdxCount"]);?></td>
		<td><?php h($db["Count"]);?></td>
	</tr>
	<?php endforeach; ?>
</table>