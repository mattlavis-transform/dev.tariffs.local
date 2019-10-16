<?php
    $title = "Create quota suspension period";
	require ("includes/db.php");
    $application        		= new application;
    $phase              		= get_querystring("phase");
    $edit_mode					= get_querystring("edit_mode");
    $quota_suspension_period	= new quota_suspension_period;

    $quota_order_number = new quota_order_number;
    $quota_order_number->quota_order_number_id = $_COOKIE["quota_order_number_id"];
    $quota_order_number->get_quota_definitions();

    $err                = get_querystring("err");

    if ($phase == "edit") {
        $footnote->footnote_id = $footnote_id;
        $footnote->populate_from_db();
        $phase = "association_edit";
    } else {
        if ($err != "") {
            $quota_suspension_period->populate_from_cookies();
        }
        $phase = "association_create";
    }

	$error_handler = new error_handler;
	require ("includes/header.php");

    if ($edit_mode == 1) {
        // Edit mode
        $quota_suspension_period->quota_order_number_id = "090006";
        $disabled   = " disabled";
        $title      = "Edit suspension period";
        $msg        = '';
        $on_msg     = '';
		$quota_suspension_period->suspension_period_start_day	= "1";
		$quota_suspension_period->suspension_period_start_month = "7";
		$quota_suspension_period->suspension_period_start_year	= "2019";
		$quota_suspension_period->suspension_period_end_day		= "31";
		$quota_suspension_period->suspension_period_end_month	= "7";
		$quota_suspension_period->suspension_period_end_year	= "2019";
		$quota_suspension_period->description					= "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec viverra cursus ante quis ultrices. Quisque tincidunt et odio iaculis volutpat. Nullam porttitor porttitor ex.";
    } else {
        // Create mode
        $disabled   = "";
        $title      = "Create suspension period";
        $msg        = 'Use this functionality to create a suspension period for a given quota.<br /><br />
        Alternatively, please click here to view <a href="quota_suspension_periods.html">existing quota suspension periods</a>.';
        $on_msg     = 'Please ensure that you select an existing quota order number ID with 6 numeric digits beginning &quot;09&quot;. Only select
		<abbr title="First Come First Served">FCFS</abbr> quotas that do not start with the characters &quot;094&quot;.';
		$quota_suspension_period->suspension_period_start_day	= "";
		$quota_suspension_period->suspension_period_start_month = "";
		$quota_suspension_period->suspension_period_start_year	= "";
		$quota_suspension_period->suspension_period_end_day		= "";
		$quota_suspension_period->suspension_period_end_month	= "";
		$quota_suspension_period->suspension_period_end_year	= "";
		$quota_suspension_period->description					= "";
    }
?>
<!-- Start breadcrumbs //-->
<div id="wrapper" class="direction-ltr">
	<div class="gem-c-breadcrumbs govuk-breadcrumbs " data-module="track-click">
	<ol class="govuk-breadcrumbs__list">
		<li class="govuk-breadcrumbs__list-item"><a class="govuk-breadcrumbs__link" href="/">Main menu</a></li>
		<li class="govuk-breadcrumbs__list-item"><a class="govuk-breadcrumbs__link" href="/quota_order_numbers.html">Quotas</a></li>
		<li class="govuk-breadcrumbs__list-item"><?=$title?></li>
	</ol>
</div>
<!-- End breadcrumbs //-->

<div class="app-content__header">
	<h1 class="govuk-heading-xl"><?=$title?> on quota 090006</h1>
</div>

<form class="tariff" method="post" action="/quota_suspension_period_confirm.html">
<input type="hidden" name="phase" value="<?=$phase?>" />
<?php
    if ($phase == "footnote_edit") {
        echo ('<input type="hidden" name="footnote_id" value="' . $footnote->footnote_id . '" />');
    }
?>
<!-- Start error handler //-->
<?=$error_handler->get_primary_error_block() ?>
<!-- End error handler //-->


<!-- Begin quota definition period field //-->
<div class="govuk-form-group <?=$error_handler->get_error("footnote_type_id");?>">
	<legend class="govuk-fieldset__legend govuk-fieldset__legend--xl">
        <h1 id="heading_trade_movement_code" class="govuk-fieldset__heading" style="max-width:100%;">
            <label for="footnote_type_id">Select the quota definition period to suspend</label>
        </h1>
	</legend>
    <span class="govuk-hint">This is the quota definition to which the quota suspension period will be assigned. This list only includes future and current definition periods.</span>
	<?=$error_handler->display_error_message("footnote_type_id");?>
	<select <?=$disabled?> class="govuk-select" id="footnote_type_id" name="footnote_type_id">
		<option value="">- Select quota definition period - </option>
<?php
	foreach ($quota_order_number->quota_definitions as $obj) {
        if ($obj->footnote_type_id == $footnote->footnote_type_id) {
            echo ("<option selected value='" . $obj->quota_definition_sid . "'>" . short_date($obj->validity_start_date) . " to " . short_date($obj->validity_end_date) . "</option>");
        } else {
            echo ("<option value='" . $obj->quota_definition_sid . "'>" . short_date($obj->validity_start_date) . " to " . short_date($obj->validity_end_date) . "</option>");
        }
	}
?>
	</select>
</div>
<!-- End quota definition period field //-->


<!-- Begin validity start date fields //-->
<div class="govuk-form-group <?=$error_handler->get_error("suspension_period_start_date");?>">
	<fieldset class="govuk-fieldset" aria-describedby="suspension_period_start_hint" role="group">
		<legend class="govuk-fieldset__legend govuk-fieldset__legend--xl">
			<h1 id="heading_suspension_period_start_date" class="govuk-fieldset__heading" style="max-width:100%;">Suspension period start date</h1>
		</legend>
		<span class="govuk-hint">Please ensure that this date falls on or after the definition start date.</span>
		<?=$error_handler->display_error_message("suspension_period_start_date");?>
		<div class="govuk-date-input" id="suspension_period_start">
			<div class="govuk-date-input__item">
				<div class="govuk-form-group">
					<label class="govuk-label govuk-date-input__label" for="suspension_period_start_day">Day</label>
					<input value="<?=$quota_suspension_period->suspension_period_start_day?>" class="govuk-input govuk-date-input__input govuk-input--width-2" id="suspension_period_start_day" maxlength="2" name="suspension_period_start_day" type="text" pattern="[0-9]*">
				</div>
			</div>
			<div class="govuk-date-input__item">
				<div class="govuk-form-group">
					<label class="govuk-label govuk-date-input__label" for="suspension_period_start_month">Month</label>
					<input value="<?=$quota_suspension_period->suspension_period_start_month?>" class="govuk-input govuk-date-input__input govuk-input--width-2" id="suspension_period_start_month" maxlength="2" name="suspension_period_start_month" type="text" pattern="[0-9]*">
				</div>
			</div>
			<div class="govuk-date-input__item">
				<div class="govuk-form-group">
					<label class="govuk-label govuk-date-input__label" for="suspension_period_start_year">Year</label>
					<input value="<?=$quota_suspension_period->suspension_period_start_year?>" class="govuk-input govuk-date-input__input govuk-input--width-4" id="suspension_period_start_year" maxlength="4" name="suspension_period_start_year" type="text" pattern="[0-9]*">
				</div>
			</div>
		</div>
	</fieldset>
</div>
<!-- End validity start date fields //-->


<!-- Begin validity end date fields //-->
<div class="govuk-form-group <?=$error_handler->get_error("suspension_period_end_date");?>">
	<fieldset class="govuk-fieldset" aria-describedby="suspension_period_end_hint" role="group">
		<legend class="govuk-fieldset__legend govuk-fieldset__legend--xl">
			<h1 id="heading_suspension_period_end_date" class="govuk-fieldset__heading" style="max-width:100%;">Suspension period end date</h1>
		</legend>
		<span class="govuk-hint">Please ensure that this date falls on or before the definition end date.</span>
		<?=$error_handler->display_error_message("suspension_period_end_date");?>
		<div class="govuk-date-input" id="suspension_period_end">
			<div class="govuk-date-input__item">
				<div class="govuk-form-group">
					<label class="govuk-label govuk-date-input__label" for="suspension_period_end_day">Day</label>
					<input value="<?=$quota_suspension_period->suspension_period_end_day?>" class="govuk-input govuk-date-input__input govuk-input--width-2" id="suspension_period_end_day" maxlength="2" name="suspension_period_end_day" type="text" pattern="[0-9]*">
				</div>
			</div>
			<div class="govuk-date-input__item">
				<div class="govuk-form-group">
					<label class="govuk-label govuk-date-input__label" for="suspension_period_end_month">Month</label>
					<input value="<?=$quota_suspension_period->suspension_period_end_month?>" class="govuk-input govuk-date-input__input govuk-input--width-2" id="suspension_period_end_month" maxlength="2" name="suspension_period_end_month" type="text" pattern="[0-9]*">
				</div>
			</div>
			<div class="govuk-date-input__item">
				<div class="govuk-form-group">
					<label class="govuk-label govuk-date-input__label" for="suspension_period_end_year">Year</label>
					<input value="<?=$quota_suspension_period->suspension_period_end_year?>" class="govuk-input govuk-date-input__input govuk-input--width-4" id="suspension_period_end_year" maxlength="4" name="suspension_period_end_year" type="text" pattern="[0-9]*">
				</div>
			</div>
		</div>
	</fieldset>
</div>
<!-- End validity end date fields //-->



<!-- Begin description field //-->
<div class="govuk-form-group">
	<legend class="govuk-fieldset__legend govuk-fieldset__legend--xl">
        <h1 id="heading_workbasket_name" class="govuk-fieldset__heading" style="max-width:100%;"><label for="description">Please enter a description for this suspension period</label></h1>
	</legend>
    <span class="govuk-hint">This field is for informational purposes only. It is not mandatory.</span>

    <textarea class="govuk-textarea" name="description" id="description" rows="5"><?=$quota_suspension_period->description?></textarea>
</div>
<!-- End description field //-->



		<button type="submit" class="govuk-button">Continue</button>
	</form>
</div>

<?php
	require ("includes/footer.php")
?>