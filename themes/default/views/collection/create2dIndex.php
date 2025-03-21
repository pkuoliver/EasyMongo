<script language="javascript" src="js/collection.js"></script>
<script language="javascript" src="js/jquery-ui-1.8.4.custom.min.js"></script>
<link rel="stylesheet" href="<?php render_theme_path() ?>/css/jquery-ui-1.8.4.smoothness.css" media="all"/>

<script language="javascript">
function clickUniqueKey(box) {
	if (box.checked) {
		$("#duplicate_tr").show();
	} else {
		$("#duplicate_tr").hide();
	}
}

function addNewField() {
	$("#fields").append("<p style=\"margin:0;padding:0\"><input type=\"text\" name=\"field[]\" size=\"30\"/> <select name=\"order[]\"><option value=\"asc\">ASC</option><option value=\"desc\">DESC</option></select> <input type=\"button\" value=\"+\" onclick=\"addNewField()\"/><input type=\"button\" value=\"-\" onclick=\"removeNewField(this)\"/></p>");
	$("input[name='field[]']").autocomplete({ source:currentFields, delay:100 });
}

function removeNewField(btn) {
	$(btn).parent().remove();
}

var currentFields = new Array();
<?php foreach ($nativeFields as $field): if($field == "_id") {continue;}  ?>
currentFields.push("<?php h(addslashes($field));?>");
<?php endforeach;?>

$(function() {
	$("input[name='field[]']").autocomplete({ source:currentFields, delay:100 });
});
</script>

<h3><?php render_navigation($db,$collection); ?> &raquo; <a href="<?php
				h(url("collection.collectionIndexes", array(
					"db" => $db,
					"collection" => $collection
				)));
			?>"><?php hm("indexes");?></a> &raquo; <?php hm("create"); ?></h3>

<?php if(isset($message)): ?>
<p class="error"><?php h($message);?></p>
<?php endif; ?>

<p>
	[<a href="http://docs.mongodb.org/manual/core/2d/" target="_blank">Here is official documents</a>]
</p>

<form method="post">
<table width="600">
	<tr>
		<td width="130"><?php hm("name"); ?></td>
		<td><input type="text" name="name"/></td>
	</tr>
	<tr>
		<td valign="top"><?php hm("2d_index_location_field") ?></td>
		<td>
			<input type="text" name="location_field" size="30"/> 
			<select name="location_index_type">
				<option value="2dsphere">2dsphere</option>
				<option value="2d">2d</option>
			</select> 
		</td>
	</tr>
	<tr>
		<td valign="top"><?php hm("other_fields"); ?></td>
		<td><div id="fields">
			<input type="text" name="field[]" size="30"/> 
			<select name="order[]">
				<option value="asc">ASC</option>
				<option value="desc">DESC</option>
			</select> 
			<input type="button" value="+" onclick="addNewField()"/>
		</div></td>
	</tr>
	<tr>
		<td><?php hm("2d_index_min_bound") ?></td>
		<td><input type="text" name="min_bound" size="15"/> * <?php hm("default") ?>: -180</td>
	</tr>
	<tr>
		<td><?php hm("2d_index_max_bound") ?></td>
		<td><input type="text" name="max_bound" size="15"/> * <?php hm("default") ?>: 180</td>
	</tr>
	<tr>
		<td><?php hm("2d_index_bit_precision") ?></td>
		<td><input type="text" name="bits" size="15"/> * <?php hm("default") ?>: 26</td>
	</tr>
	<tr>
		<td colspan="2"><input type="submit" value="<?php hm("create"); ?>"/></td>
	</tr>
</table>
</form>