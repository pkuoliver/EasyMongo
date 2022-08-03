<div class="operation">
	<?php render_server_menu("replication"); ?>
</div>

<table bgcolor="#cccccc" cellpadding="2" cellspacing="1" width="600">
	<tr>
		<th colspan="2"><?php hm("repstatus"); ?> (replSetGetStatus)</th>
	</tr>
	<?php foreach ($status as $param=>$value):?>
	<tr bgcolor="#fffeee">
		<td width="120" valign="top"><?php h($param);?></td>
		<td><?php h($value);?></td>
	</tr>
	<?php endforeach; ?>
</table>

<?php if(!empty($me)): ?>
<div class="gap"></div>

<table bgcolor="#cccccc" cellpadding="2" cellspacing="1" width="600">
	<tr>
		<th colspan="2"><?php hm("me"); ?> (<a href="<?php h(url("collection.index", array( "db" => "local", "collection" => "me" ))); ?>">local.me</a>)</th>
	</tr>
	<?php foreach ($me as $param => $value):?>
	<tr bgcolor="#fffeee">
		<td width="120" valign="top"><?php h($param);?></td>
		<td><?php h($value);?></td>
	</tr>
	<?php endforeach; ?>
</table>
<?php endif; ?>

<?php if(!empty($members)): ?>
<div class="gap"></div>

<table bgcolor="#cccccc" cellpadding="2" cellspacing="1" width="600px">
	<tr>
		<th colspan="2"><?php hm("members"); ?> (<a href="<?php h(url("collection.index", array( "db" => "local", "collection" => "slaves" ))); ?>">replSetGetStatus[members]</a>)</th>
	</tr>
	<?php foreach ($members as $member):?>
	<tr bgcolor="#cfffff">
		<td colspan="2"><?php h($member["name"]); ?></td>
	</tr>
		<?php foreach ($member as $param => $value):?>
		<tr bgcolor="#fffeee">
			<td width="120" valign="top"><?php h($param);?></td>
			<td><?php h($value);?></td>
		</tr>
		<?php endforeach; ?>
	<?php endforeach; ?>
</table>
<?php endif; ?>

<div class="gap"></div>