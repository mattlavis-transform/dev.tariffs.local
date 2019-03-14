<?php
/* The paramters are
	- the name of the script file, which determines which cells to hypelink or not
	- goods_nomenclature_item_id
	- measure_type_id
	- base_regulation_id
	- quota_order_number_id
*/
	$current_file_name = basename($_SERVER['PHP_SELF']);
	$productline_suffix         = get_querystring("productline_suffix");
	$goods_nomenclature_item_id = get_querystring("goods_nomenclature_item_id");
	$geographical_area_id       = get_querystring("geographical_area_id");
	$measure_type_id            = get_querystring("measure_type_id");
	if ($productline_suffix == "") {
		$productline_suffix = "80";
	}
	#if (1 > 0) {
	if ($productline_suffix == "80") {
?>        

<form action="/actions/goods_nomenclature_actions.html" method="get" class="inline_form">
			<h3>Filter results</h3>
			<!--<input type="hidden" name="goods_nomenclature_item_id" value="<?=$goods_nomenclature_item_id?>" />//-->
			<input type="hidden" name="phase" value="goods_nomenclature_item_view_filter" />
			<input type="hidden" name="productline_suffix" value="<?=$productline_suffix?>" />
			<div class="column-one-third" style="width:200px">
				<div class="govuk-form-group">
					<fieldset class="govuk-fieldset" aria-describedby="base_regulation_hint" role="group">
						<span id="base_regulation_hint" class="govuk-hint">Filter by commodity code</span>
						<div class="govuk-date-input" id="measure_start">
							<div class="govuk-date-input__item">
								<div class="govuk-form-group" style="padding:0px;margin:0px">
									<input value="<?=$goods_nomenclature_item_id?>" class="govuk-input govuk-date-input__input govuk-input--width-8" id="goods_nomenclature_item_id" maxlength="14" name="goods_nomenclature_item_id" type="text">
								</div>
							</div>
						</div>
					</fieldset>
				</div>
			</div>
			<div class="column-one-third" style="width:200px">
				<div class="govuk-form-group">
					<fieldset class="govuk-fieldset" aria-describedby="base_regulation_hint" role="group">
						<span id="base_regulation_hint" class="govuk-hint">Filter by measure type ID</span>
						<div class="govuk-date-input" id="measure_start">
							<div class="govuk-date-input__item">
								<div class="govuk-form-group" style="padding:0px;margin:0px">
									<input value="<?=$measure_type_id?>" class="govuk-input govuk-date-input__input govuk-input--width-8" id="measure_type_id" maxlength="12" name="measure_type_id" type="text">
								</div>
							</div>
						</div>
					</fieldset>
				</div>
			</div>
			<div class="column-one-third" style="width:200px">
				<div class="govuk-form-group">
					<fieldset class="govuk-fieldset" aria-describedby="base_regulation_hint" role="group">
						<span id="base_regulation_hint" class="govuk-hint">Filter by geo. area ID</span>
						<div class="govuk-date-input" id="measure_start">
							<div class="govuk-date-input__item">
								<div class="govuk-form-group" style="padding:0px;margin:0px">
									<input value="<?=$geographical_area_id?>" class="govuk-input govuk-date-input__input govuk-input--width-8" id="geographical_area_id" maxlength="4" name="geographical_area_id" type="text">
								</div>
							</div>
						</div>
					</fieldset>
				</div>
			</div>
			<div class="column-one-third" style="width:200px">
				<div class="govuk-form-group" style="padding:0px;margin:0px">
					<button type="submit" class="govuk-button" style="margin-top:36px">Search</button>
				</div>
			</div>
			<div class="clearer"><!--&nbsp;//--></div>
			</form>


			<table class="govuk-table" cellspacing="0">
				<tr class="govuk-table__row">
					<th class="govuk-table__header">Measure SID</th>
					<th class="govuk-table__header">Commodity</th>
					<th class="govuk-table__header">Measure type ID</th>
					<th class="govuk-table__header l" colspan="2">Geographical area</th>
					<th class="govuk-table__header c">Additional code</th>
					<th class="govuk-table__header">Regulation</th>
					<th class="govuk-table__header l">Start date</th>
					<th class="govuk-table__header l">End&nbsp;date</th>
					<th class="govuk-table__header c">Order number</th>
					<th class="govuk-table__header r">Duty</th>
				</tr>
<?php
	// Firstly, get all the duties to put in the duty column
		$sql = "SELECT m.additional_code_type_id, m.additional_code_id, m.measure_type_id,
		mc.measure_sid, duty_expression_id, duty_amount, monetary_unit_code, measurement_unit_code, measurement_unit_qualifier_code
		FROM measures m, measure_components mc WHERE m.measure_sid = mc.measure_sid ";
		if ($goods_nomenclature_item_id != "") {
			$sql .= " AND m.goods_nomenclature_item_id = '" . $goods_nomenclature_item_id . "' ";
		}
		if ($geographical_area_id != "") {
			$sql .= " AND m.geographical_area_id = '" . $geographical_area_id . "' ";
		}
		$sql .= "ORDER BY m.measure_sid, mc.duty_expression_id";
		#print ($sql);
		$result = pg_query($conn, $sql);
		$duty_list = array();
		if  (($result) && (pg_num_rows($result) > 0)) {
			while ($row = pg_fetch_array($result)) {
				$measure_sid                        = $row['measure_sid'];
				$additional_code_type_id            = $row['additional_code_type_id'];
				$additional_code_id                 = $row['additional_code_id'];
				$measure_type_ix                    = $row['measure_type_id'];
				$duty_expression_id                 = $row['duty_expression_id'];
				$duty_amount                        = $row['duty_amount'];
				$monetary_unit_code                 = $row['monetary_unit_code'];
				$measurement_unit_code              = $row['measurement_unit_code'];
				$measurement_unit_qualifier_code    = $row['measurement_unit_qualifier_code'];
				
				$duty = new duty;
				$duty->set_properties($goods_nomenclature_item_id, $additional_code_type_id, $additional_code_id, $measure_type_ix,
				$duty_expression_id, $duty_amount, $monetary_unit_code, $measurement_unit_code,
				$measurement_unit_qualifier_code, $measure_sid, "", "", "", "");
				array_push($duty_list, $duty);
			}
		}

	// Secondly, get the measure components explicitly related to SIVs
	$sql = "SELECT mc.measure_sid, mcc.duty_amount FROM measure_conditions mc, measure_condition_components mcc, measures m
	WHERE mcc.measure_condition_sid = mc.measure_condition_sid
	AND m.measure_sid = mc.measure_sid
	AND mcc.duty_expression_id = '01'
	AND m.goods_nomenclature_item_id = '" . $goods_nomenclature_item_id . "'";
	if ($geographical_area_id != "") {
		$sql .= " AND m.geographical_area_id = '" . $geographical_area_id . "' ";
	}
	$sql .= "ORDER BY m.measure_sid, component_sequence_number";
	$result = pg_query($conn, $sql);
	$siv_component_list = array();
	if  (($result) && (pg_num_rows($result) > 0)) {
		while ($row = pg_fetch_array($result)) {
			$measure_sid    = $row['measure_sid'];
			$duty_amount	= $row['duty_amount'];
			$siv_component = new siv_component;
			$siv_component->set_properties($measure_sid, $duty_amount);
			array_push($siv_component_list, $siv_component);
		}
	}


	// Thirdly, get the measures
		#echo ($geographical_area_id);
		$sql = "SELECT m.*, mtd.description as measure_type_description, g.description as geographical_area_description
		FROM measures m, measure_type_descriptions mtd, ml.ml_geographical_areas g
		WHERE m.measure_type_id = mtd.measure_type_id
		AND m.geographical_area_sid = g.geographical_area_sid";
		if ($goods_nomenclature_item_id != "") {
			$sql .= " AND m.goods_nomenclature_item_id = '" . $goods_nomenclature_item_id . "'";
		}
		if ($geographical_area_id != "") {
			$sql .= " AND m.geographical_area_id = '" . $geographical_area_id . "'";
		}
		if ($measure_type_id != "") {
			if (strpos($measure_type_id, ",") > 0) {
				$measure_type_id = str_replace(" ", "", $measure_type_id);
				$ar = explode(",", $measure_type_id);
				$measure_type_string = "";
				foreach ($ar as $m) {
					$measure_type_string .= "'" . $m . "', ";
				}
				$measure_type_string = trim($measure_type_string);
				$measure_type_string = trim($measure_type_string, ",");
				$sql .= " AND m.measure_type_id IN (" . $measure_type_string . ")";

			} else {
				$sql .= " AND m.measure_type_id = '" . $measure_type_id . "'";
			}
		}
		$sql .= " ORDER BY m.validity_start_date DESC";
		#p ($sql);
		#exit();

		$result = pg_query($conn, $sql);
		if  (($result) && (pg_num_rows($result) > 0)){
			$measure_list = array();
			while ($row = pg_fetch_array($result)) {
				$measure_sid                	= $row['measure_sid'];
				$goods_nomenclature_item_ix 	= $row['goods_nomenclature_item_id'];
				$measure_type_id            	= $row['measure_type_id'];
				$measure_type_description   	= $row['measure_type_description'];
				$geographical_area_id       	= $row['geographical_area_id'];
				$geographical_area_description	= $row['geographical_area_description'];
				$additional_code_type_id    	= $row['additional_code_type_id'];
				$additional_code_id         	= $row['additional_code_id'];
				$quota_order_number_id      	= $row['ordernumber'];
				$regulation_id_full         	= $row['measure_generating_regulation_id'];
				$validity_start_date        	= short_date($row['validity_start_date']);
				$validity_end_date          	= short_date($row['validity_end_date']);

				$measure = new measure;
				$measure->set_properties($measure_sid, $goods_nomenclature_item_id, $quota_order_number_id, $validity_start_date,
				$validity_end_date, $geographical_area_id, $measure_type_id, $additional_code_type_id,
				$additional_code_id, $regulation_id_full, $measure_type_description);
				$measure->geographical_area_description = $geographical_area_description;

				// Assign the relevant duties to the measures
				if (count($duty_list) > 0) {
					foreach ($duty_list as $d){
						if ($d->measure_sid == $measure_sid) {
							array_push($measure->duty_list, $d);
						}
					}
				}
				$measure->combine_duties();

				if ($measure->combined_duty == "") {
					// Assign the relevant SIV components to the measures
					if (count($siv_component_list) > 0) {
						foreach ($siv_component_list as $s){
							if ($s->measure_sid == $measure_sid) {
								array_push($measure->siv_component_list, $s);
							}
						}
					}
					$measure->get_siv_specific();
				}
				array_push($measure_list, $measure);
			}
			

			// Only show the duty for duty measures
			$duty_array = array("142", "143", "144", "146");

			foreach ($measure_list as $m){
				if (in_array($m->measure_type_id, $duty_array) == False) {
					$m->combined_duty = "&nbsp;";
				}
				$rowclass                   = rowclass($m->validity_start_date, $m->validity_end_date);
?>
				<tr class="<?=$rowclass?>">
					<td class="govuk-table__cell"><a href="measure_view.html?measure_sid=<?=$m->measure_sid?>"><?=$m->measure_sid?></a></td>
<!-- Show nomenclature cell //-->
<?php
	if ($current_file_name != "goods_nomenclature_item_view.html") {
?>
					<td class="govuk-table__cell"><a href="goods_nomenclature_item_view.html?goods_nomenclature_item_id=<?=$m->goods_nomenclature_item_id?>"><?=$goods_nomenclature_item_id?></a></td>
<?php
	} else {
?>
					<td class="govuk-table__cell"><?=$goods_nomenclature_item_id?></td>
<?php        
	}
?>
<!-- End show nomenclature cell //-->


<!-- Start show measure type cell //-->
					<td class="govuk-table__cell"><a href="measure_type_view.html?measure_type_id=<?=$m->measure_type_id?>"><?=$m->measure_type_id?>&nbsp;<?=$m->measure_type_description?></a></td>
<!-- End show measure type cell //-->


<!-- Start show geographical area cell //-->
<?php
    if ($current_file_name != "geographical_area_view.html") {
?>
					<td class="govuk-table__cell"><a href="geographical_area_view.html?geographical_area_id=<?=$m->geographical_area_id?>"><?=$m->geographical_area_id?></td>
					<td class="govuk-table__cell"><?=$m->geographical_area_description?></a></td>
<?php        
    } else {
?>
					<td class="govuk-table__cell"><?=$m->geographical_area_id?></td>
					<td class="govuk-table__cell"><?=$m->geographical_area_description?></td>
<?php        
    }
?>
<!-- End show geographical area cell //-->


					<td class="govuk-table__cell c"><?=$m->additional_code_type_id?><?=$m->additional_code_id?></td>
					<td class="govuk-table__cell"><a href="regulation_view.html?base_regulation_id=<?=$m->regulation_id_full?>"><?=$m->regulation_id_full?></a></td>
					<td nowrap class="govuk-table__cell l"><?=$m->validity_start_date?></td>
					<td nowrap class="govuk-table__cell l"><?=$m->validity_end_date?></td>
					<td nowrap class="govuk-table__cell c"><a href="/quota_order_number_view.html?quota_order_number_id=<?=$m->quota_order_number_id?>"><?=$m->quota_order_number_id?></a></td>
					<td class="govuk-table__cell r"><?=$m->combined_duty?></td>
				</tr>
<?php
			}
		}
	?>
			</table>
			<p class="back_to_top"><a href="#top">Back to top</a></p>
<?php
	} else {
		echo ("<p>No measures, as this does not have a product line suffix of '80'.</p>");
	}
?>