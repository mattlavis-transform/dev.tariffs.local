<?php
    $title = "View regulation";
	require ("includes/db.php");
	require ("includes/header.php");
	$regulation_id = get_querystring("base_regulation_id");
?>
<div id="wrapper" class="direction-ltr">
<div class="gem-c-breadcrumbs govuk-breadcrumbs " data-module="track-click">
	<ol class="govuk-breadcrumbs__list">
		<li class="govuk-breadcrumbs__list-item">
			<a class="govuk-breadcrumbs__link" href="/">Home</a>
		</li>
		<li class="govuk-breadcrumbs__list-item">
			<a class="govuk-breadcrumbs__link" href="/regulations.html">Regulations</a>
		</li>
	</ol>
	</div>
	<div class="app-content__header">
		<h1 class="govuk-heading-xl">View regulation <?=$regulation_id?></h1>
	</div>
			<h2 class="nomargin">Regulation details</h2>
			<p style="max-width:100%">The European Union often splits a regulation into multiple parts for ease of management. There may
			be multiple regulation records here to represent just a single actual regulation.</p>
<?php
	$sql = "SELECT 'Base' as regulation_type, b.base_regulation_id as regulation_id, b.information_text, b.regulation_group_id,
	rgd.description as regulation_group_description, b.validity_start_date, b.validity_end_date, b.effective_end_date
	FROM base_regulations b, regulation_group_descriptions rgd WHERE b.regulation_group_id = rgd.regulation_group_id
	AND base_regulation_id LIKE '" . $regulation_id . "%' 
	UNION
	SELECT 'Modification' as regulation_type, m.modification_regulation_id as regulation_id, m.information_text, b.regulation_group_id,
	rgd.description, m.validity_start_date, m.validity_end_date, m.effective_end_date
	FROM modification_regulations m, base_regulations b, regulation_group_descriptions rgd
	WHERE m.base_regulation_id = b.base_regulation_id
	AND b.regulation_group_id = rgd.regulation_group_id
	AND m.modification_regulation_id LIKE '" . $regulation_id . "%'
	ORDER BY 1";
	#echo ($sql);
	$result = pg_query($conn, $sql);
	if  ($result) {
?>
			<table class="govuk-table" cellspacing="0">
				<tr class="govuk-table__row">
					<th class="govuk-table__header" style="width:12%">Regulation&nbsp;ID</th>
					<th class="govuk-table__header" style="width:10%">Type</th>
					<th class="govuk-table__header" style="width:28%">Information text</th>
					<th class="govuk-table__header" style="width:20%">Regulation group</th>
					<th class="govuk-table__header" style="width:10%">Start</th>
					<th class="govuk-table__header" style="width:10%">End</th>
					<th class="govuk-table__header" style="width:10%">Effective end</th>
				</tr>
<?php
		while ($row = pg_fetch_array($result)) {
			$regulation_type				= $row['regulation_type'];
			$regulation_idx					= $row['regulation_id'];
			$information_text				= $row['information_text'];
			$regulation_group_id			= $row['regulation_group_id'];
			$regulation_group_description   = $row['regulation_group_description'];
			$validity_start_date			= $row['validity_start_date'];
			$validity_end_date				= $row['validity_end_date'];
			$effective_end_date				= $row['effective_end_date'];
?>
				<tr class="govuk-table__row">
					<td class="govuk-table__cell"><?=$regulation_idx?></td>
					<td class="govuk-table__cell"><?=$regulation_type?></td>
					<td class="govuk-table__cell"><?=$information_text?></td>
					<td class="govuk-table__cell"><?=$regulation_group_id?> - <?=$regulation_group_description?></td>
					<td class="govuk-table__cell"><?=string_to_date($validity_start_date)?></td>
					<td class="govuk-table__cell"><?=string_to_date($validity_end_date)?></td>
					<td class="govuk-table__cell"><?=string_to_date($effective_end_date)?></td>
				</tr>
<?php
		}
	}
?>

			</table>
			
			<h2>Measure details</h2>
			<p style="max-width:100%">The list below identifies any measures that have come into force as a result of this regulation.
			Any measures that are shaded in grey are now in the past and closed. Any highlighted in blue
			are due to start on or around Brexit and have been created by DIT.</p>
<?php
	$sql = "select measure_sid, goods_nomenclature_item_id, m.validity_start_date, m.validity_end_date, m.geographical_area_id,
	m.measure_type_id, measure_generating_regulation_id, mtd.description as measure_type_description,
	g.description as geographical_area_description, m.additional_code_type_id, m.additional_code_id
	from ml.measures_real_end_dates m, measure_type_descriptions mtd, ml.ml_geographical_areas g
	where m.measure_type_id = mtd.measure_type_id
	and m.geographical_area_id = g.geographical_area_id
	and measure_generating_regulation_id LIKE '" . $regulation_id . "%'
	ORDER BY goods_nomenclature_item_id, additional_code_type_id, additional_code_id";
	
	$result = pg_query($conn, $sql);
	echo ("<p>There are <strong>" . pg_num_rows($result) . "</strong> measures that have been generated by this regulation.");
	if  ($result) {
?>
			<table class="govuk-table" cellspacing="0">
				<tr class="govuk-table__row">
					<th class="govuk-table__header" style="width:8%">SID</th>
					<th class="govuk-table__header" style="width:10%">Commodity</th>
					<th class="govuk-table__header c" style="width:8%">Add code</th>
					<th class="govuk-table__header" style="width:8%">Start date</th>
					<th class="govuk-table__header" style="width:8%">End date</th>
					<th class="govuk-table__header" style="width:20%">Geographical area</th>
					<th class="govuk-table__header" style="width:28%">Type</th>
					<th class="govuk-table__header" style="width:10%">Regulation&nbsp;ID</th>
				</tr>

<?php
		while ($row = pg_fetch_array($result)) {
			$measure_sid                = $row['measure_sid'];
			$additional_code            = $row['additional_code_id'];
			$additional_code_type       = $row['additional_code_type_id'];
			$goods_nomenclature_item_id = $row['goods_nomenclature_item_id'];
			$validity_start_date        = trim($row['validity_start_date'] . "");
			$validity_end_date          = trim($row['validity_end_date'] . "");

			$my_add_code = $additional_code_type . $additional_code;
			if ($my_add_code == "") {
				$my_add_code = "-";
			}

			/*
			$validity_start_date        = DateTime::createFromFormat('Y-m-d H:i:s', $validity_start_date)->format('Y-m-d');
			if ($validity_end_date != "") {
				$validity_end_date      = ""; # DateTime::createFromFormat('Y-m-d H:i:s', $validity_end_date)->format('Y-m-d');
			} else {
				$validity_end_date = "";
			}
			*/

			$measure_type_id                	= $row['measure_type_id'];
			$geographical_area_id           	= $row['geographical_area_id'];
			$measure_generating_regulation_id	= $row['measure_generating_regulation_id'];
			$geographical_area_description  	= $row['geographical_area_description'];
			$measure_type_description       	= $row['measure_type_description'];

			$rowclass = rowclass($validity_start_date, $validity_end_date);
?>
				<tr class="govuk-table__row <?=$rowclass?>">
					<td class="govuk-table__cell"><a href="measure_view.html?measure_sid=<?=$measure_sid?>"><?=$measure_sid?></a></td>
					<td class="govuk-table__cell"><a class="nodecorate" href="goods_nomenclature_item_view.html?goods_nomenclature_item_id=<?=$goods_nomenclature_item_id?>"><?=format_commodity_code($goods_nomenclature_item_id)?></a></td>
					<td class="govuk-table__cell c"><?=$my_add_code?></td>
					<td class="govuk-table__cell"><?=$validity_start_date?></td>
					<td class="govuk-table__cell"><?=$validity_end_date?></td>
					<td class="govuk-table__cell"><a href="geographical_area_view.html?geographical_area_id=<?=$geographical_area_id?>"><?=$geographical_area_id?> - <?=$geographical_area_description?></a></td>
					<td class="govuk-table__cell"><a href="measure_type_view.html?measure_type_id=<?=$measure_type_id?>"><?=$measure_type_id?> - <?=$measure_type_description?></a></td>
					<td class="govuk-table__cell"><?=$measure_generating_regulation_id?></td>
				</tr>

<?php
		}
	}
?>
	</table>
</div>
<?php
	require ("includes/footer.php")
?>