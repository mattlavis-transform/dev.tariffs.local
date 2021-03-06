<?php
class measure_activity
{
    /* Start prototype-specific fields */

    public $measure_activity_sid = null;
    public $workbasket_id = null;
    public $certificate_code = null;
    public $certificates = null;
    public $activity_name = null;
    public $reduction_indicator = null;
    public $edit_activity_option = null;
    public $remove_existing_footnotes = null;
    public $measure_sid_list = array();
    public $measure_count = null;
    public $show_duties_form = true;
    public $order_number_capture_code = null;
    public $measure_component_applicable_code = null;
    public $applicable_duty = null;
    public $applicable_duty_permutation = null;
    public $suppress_additional_codes_field = false;
    public $introductory_period_option = null;

    public $activity_options = array();
    /* End prototype-specific fields */

    public function __construct()
    {
        global $application;

        $this->residual_additional_code = "";
        $this->geographical_area_description = "";
        $this->geographical_area_id = "";
        $this->assigned = False;
        $this->combined_duty = "";
        $this->duty_list = array();
        $this->measure_components = array();
        $this->measure_condition_components = array();
        $this->footnote_association_measures = array();
        $this->mega_list = array();
        $this->suppress = False;
        $this->marked = False;
        $this->significant_children = False;
        $this->measure_count = 0;
        $this->measure_type_count = 0;
        $this->additional_code_id = "";
        $this->additional_code_type_id = "";
        $this->measure_heading = "Temp";
        $this->siv_component_list = array();
        $this->measure_generating_regulation_id = "";
        $this->measure_generating_regulation_role = "";
        $this->goods_nomenclature_item_id = "";
        $this->additional_code_type_id = "";
        $this->additional_code_id = "";
        $this->validity_start_date = "";
        $this->validity_end_date = "";
        $this->entry_price_string = "";
        $this->perceived_value = 0;
        $this->measure_type_id = null;
        $this->commodity_codes = null;
        $this->additional_codes = null;
        $this->duty = null;
        $this->footnote_id = null;
        $this->commodity_code_list = array();
        $this->additional_code_list = array();
        $this->ordernumber = null;

        $this->activity_name = "";
        $this->measure_components_xml = "";
        $this->measure_excluded_geographical_areas_xml = "";
        $this->measure_conditions_xml = "";
        $this->measure_partial_temporary_stops_xml = "";
        $this->footnote_association_measures_xml = "";
        $this->footnote_list = array();
        $this->condition_list = array();

        $this->activity_name_complete = false;
        $this->core_data_complete = false;
        $this->commodity_data_complete = false;
        $this->duty_data_complete = false;
        $this->condition_data_complete = false;
        $this->footnote_data_complete = false;
    }

    public function get_parameters($description = false)
    {
        global $application;
        global $error_handler;

        $this->quota_order_number_sid = trim(get_querystring("quota_order_number_sid"));
        $this->quota_order_number_id = trim(get_querystring("quota_order_number_id"));

        if (empty($_GET)) {
            $this->clear_cookies();
        } elseif ($application->mode == "insert") {
            $this->populate_from_cookies();
        } else {
            if (empty($error_handler->error_string)) {
                if ($description == false) {
                    $ret = $this->populate_from_db();
                } else {
                    //$ret = $this->get_specific_description($this->validity_start_date);
                    $a = 1;
                }
                if (!$ret) {
                    h1("An error has occurred - no such quota order number");
                    die();
                }
            } else {
                $this->populate_from_cookies();
            }
        }
        $this->show_hide_duties_link();
    }

    function show_hide_duties_link()
    {
        if ($this->show_duties_form == false) {
            //h1("disabling");
?>
            <script>
                $(document).ready(function() {
                    $("li#duties a").prop("disabled", true);
                    $("li#duties").addClass("disabled");
                });
            </script>
<?php
        }
    }

    function set_dates()
    {
        if (($this->validity_start_date_day == "") || ($this->validity_start_date_month == "") || ($this->validity_start_date_year == "")) {
            $this->validity_start_date = Null;
        } else {
            $this->validity_start_date    = to_date_string($this->validity_start_date_day,    $this->validity_start_date_month, $this->validity_start_date_year);
        }

        if (($this->validity_end_date_day == "") || ($this->validity_end_date_month == "") || ($this->validity_end_date_year == "")) {
            $this->validity_end_date = Null;
        } else {
            $this->validity_end_date    = to_date_string($this->validity_end_date_day, $this->validity_end_date_month, $this->validity_end_date_year);
        }
    }

    public function populate_activity_from_db()
    {
        global $conn;
        $sql = "select activity_name
        from measure_activities where measure_activity_sid = $1";
        $query_name = "get_measure_activity_name" . $this->measure_activity_sid;
        pg_prepare($conn, $query_name, $sql);
        $result = pg_execute($conn, $query_name, array($this->measure_activity_sid));
        $row_count = pg_num_rows($result);
        if (($result) && ($row_count > 0)) {
            $row = pg_fetch_row($result);
            $this->activity_name = $row[0];
        }
    }

    public function populate_core_from_db()
    {
        global $conn;
        $sql = "select measure_type_id, geographical_area_id,
        validity_start_date, validity_end_date, measure_generating_regulation_id, activity_name
        from measure_activities where measure_activity_sid = $1";
        $query_name = "get_measure_" . $this->measure_activity_sid;
        pg_prepare($conn, $query_name, $sql);
        $result = pg_execute($conn, $query_name, array($this->measure_activity_sid));
        $row_count = pg_num_rows($result);
        if (($result) && ($row_count > 0)) {
            $row = pg_fetch_row($result);
            $this->measure_type_id = $row[0];
            $this->geographical_area_id = $row[1];
            $this->validity_start_date = $row[2];
            $this->validity_end_date = $row[3];
            $this->measure_generating_regulation_id = $row[4];
            $this->activity_name = $row[5];

            if ($this->validity_start_date != Null) {
                $this->validity_start_date_day = date('d', strtotime($this->validity_start_date));
                $this->validity_start_date_month = date('m', strtotime($this->validity_start_date));
                $this->validity_start_date_year = date('Y', strtotime($this->validity_start_date));
            } else {
                $this->validity_start_date_day = "";
                $this->validity_start_date_month = "";
                $this->validity_start_date_year = "";
            }

            if ($this->validity_end_date != Null) {
                $this->validity_end_date_day = date('d', strtotime($this->validity_end_date));
                $this->validity_end_date_month = date('m', strtotime($this->validity_end_date));
                $this->validity_end_date_year = date('Y', strtotime($this->validity_end_date));
            } else {
                $this->validity_end_date_day = "";
                $this->validity_end_date_month = "";
                $this->validity_end_date_year = "";
            }
        }
    }

    public function clear_cookies()
    {
        setcookie("footnote_id", "", time() + (86400 * 30), "/");
        setcookie("footnote_type_id", "", time() + (86400 * 30), "/");
        setcookie("footnote_validity_start_date_day", "", time() + (86400 * 30), "/");
        setcookie("footnote_validity_start_date_month", "", time() + (86400 * 30), "/");
        setcookie("footnote_validity_start_date_year", "", time() + (86400 * 30), "/");
        setcookie("footnote_description", "", time() + (86400 * 30), "/");
        setcookie("footnote_validity_end_date_day", "", time() + (86400 * 30), "/");
        setcookie("footnote_validity_end_date_month", "", time() + (86400 * 30), "/");
        setcookie("footnote_validity_end_date_year", "", time() + (86400 * 30), "/");
    }

    function validate_form_core()
    {
        global $application;

        $_SESSION["validity_start_date_day"] = get_formvar("validity_start_date_day");
        $_SESSION["validity_start_date_month"] = get_formvar("validity_start_date_month");
        $_SESSION["validity_start_date_year"] = get_formvar("validity_start_date_year");
        $_SESSION["validity_end_date_day"] = get_formvar("validity_end_date_day");
        $_SESSION["validity_end_date_month"] = get_formvar("validity_end_date_month");
        $_SESSION["validity_end_date_year"] = get_formvar("validity_end_date_year");
        $_SESSION["measure_generating_regulation_id"] = get_formvar("measure_generating_regulation_id");
        $_SESSION["measure_type_id"] = get_before_hyphen(get_formvar("measure_type_id"));
        $_SESSION["geographical_area_id_countries"] = get_formvar("geographical_area_id_countries");

        // Lookup the measure type ID, so that it can be reviewed as to wheter duties are required.
        $this->lookup_measure_type_id();
        $_SESSION["show_duties_form"] = $this->show_duties_form;
        $errors = array();
        $this->measure_activity_sid = $_SESSION["measure_activity_sid"];

        $this->measure_generating_regulation_id = get_formvar("measure_generating_regulation_id", "", True);
        $this->geographical_area_id = get_formvar("geographical_area_id_countries", "", True);

        $this->validity_start_date_day = get_formvar("validity_start_date_day", "", True);
        $this->validity_start_date_month = get_formvar("validity_start_date_month", "", True);
        $this->validity_start_date_year = get_formvar("validity_start_date_year", "", True);
        $this->validity_start_date_string = $this->validity_start_date_day . "|" . $this->validity_start_date_month . "|" . $this->validity_start_date_year;
        setcookie("validity_start_date_string", $this->validity_start_date_string, time() + (86400 * 30), "/");

        $this->validity_end_date_day = get_formvar("validity_end_date_day", "", True);
        $this->validity_end_date_month = get_formvar("validity_end_date_month", "", True);
        $this->validity_end_date_year = get_formvar("validity_end_date_year", "", True);
        $this->validity_end_date_string = $this->validity_end_date_day . "|" . $this->validity_end_date_month . "|" . $this->validity_end_date_year;
        setcookie("validity_end_date_string", $this->validity_end_date_string, time() + (86400 * 30), "/");

        $this->set_dates();

        # Check on the validity start date
        $valid_start_date = checkdate($this->validity_start_date_month, $this->validity_start_date_day, $this->validity_start_date_year);
        if ($valid_start_date != 1) {
            array_push($errors, "validity_start_date");
        }

        # Check on the validity end date: must either be a valid date or blank
        /*
        if ($application->mode == "update") {
            if (($this->validity_end_date_day == "") && ($this->validity_end_date_month == "") && ($this->validity_end_date_year == "")) {
                $valid_end_date = 1;
            } else {
                $valid_end_date = checkdate($this->validity_end_date_month, $this->validity_end_date_day, $this->validity_end_date_year);
            }
            if ($valid_end_date != 1) {
                array_push($errors, "validity_end_date");
            }
        }
        */

        $this->measure_generating_regulation_id = get_formvar("measure_generating_regulation_id", "", True);
        $this->measure_generating_regulation_id = get_before_hyphen($this->measure_generating_regulation_id);
        if ($this->measure_generating_regulation_id == "") {
            array_push($errors, "measure_generating_regulation_id");
        }

        $this->measure_type_id = get_formvar("measure_type_id", "", True);
        $this->measure_type_id = get_before_hyphen($this->measure_type_id);

        if ($this->measure_type_id == "") {
            array_push($errors, "measure_type_id");
        }

        $this->geographical_area_id = get_formvar("geographical_area_id_countries", "", True);
        if ($this->geographical_area_id == "") {
            array_push($errors, "geographical_area_id");
        }

        if (count($errors) > 0) {
            $error_string = serialize($errors);
            setcookie("errors", $error_string, time() + (86400 * 30), "/");
            $url = "create_edit_core.html?err=1&mode=" . $application->mode . "&certificate_type_code=" . $this->certificate_type_code;
        } else {
            $this->persist_core();
            $url = "./create_edit_permutations.html?mode=" . $application->mode;
        }
        header("Location: " . $url);
    }


    function validate_form_activity_name()
    {
        global $application;
        $errors = array();

        # Check on the activity_name
        $this->activity_name = get_formvar("activity_name");
        $_SESSION["activity_name"] = $this->activity_name;
        //$this->measure_activity_sid = $_SESSION["measure_activity_sid"];
        if ($this->activity_name == "") {
            array_push($errors, "activity_name");
        }

        if (count($errors) > 0) {
            $error_string = serialize($errors);
            setcookie("errors", $error_string, time() + (86400 * 30), "/");
            $url = "create_edit.html?err=1&mode=" . $application->mode;
        } else {
            $this->persist_activity_name("Create measures");
            $url = "./create_edit_core.html?mode=" . $application->mode;
        }
        header("Location: " . $url);
    }

    function validate_form_conditions()
    {
        global $application;
        $url = "./create_edit_duties.html?mode=" . $application->mode;
        header("Location: " . $url);
    }


    function validate_form_footnotes()
    {
        global $application;
        $url = "./create_edit_summary.html?mode=" . $application->mode;
        header("Location: " . $url);
    }

    function persist_core()
    {
        global $conn;
        $sql = "update measure_activities set
        measure_generating_regulation_id = $1,
        measure_type_id = $2,
        validity_start_date = $3,
        validity_end_date = $4,
        geographical_area_id = $5,
        show_duties_form = $6
        where measure_activity_sid = $7
        ";
        pg_prepare($conn, "persist_core", $sql);
        pg_execute($conn, "persist_core", array(
            $this->measure_generating_regulation_id,
            $this->measure_type_id,
            $this->validity_start_date,
            $this->validity_end_date,
            $this->geographical_area_id,
            $this->show_duties_form,
            $this->measure_activity_sid
        ));
    }

    function persist_activity_name($sub_record_type)
    {
        // Create the measure activity record
        global $conn, $application;
        $date = $application->get_operation_date();
        if ($this->measure_activity_sid == "") {

            $sql = "insert into measure_activities (workbasket_id, date_created, activity_name, activity_name_complete)
            VALUES ($1, $2, $3, $4)
            RETURNING measure_activity_sid;";

            pg_prepare($conn, "persist_activity_name", $sql);
            $result = pg_execute($conn, "persist_activity_name", array(
                $application->session->workbasket->workbasket_id, $date, $this->activity_name, true
            ));

            // Set the session variable
            $row_count = pg_num_rows($result);
            if (($result) && ($row_count > 0)) {
                $row = pg_fetch_row($result);
                $_SESSION["measure_activity_sid"] = $row[0];
            } else {
                die();
            }
        } else {

            $sql = "update measure_activities set activity_name = $1 where measure_activity_sid = $2;";

            pg_prepare($conn, "persist_activity_name", $sql);
            $result = pg_execute($conn, "persist_activity_name", array(
                $this->activity_name, $this->measure_activity_sid
            ));
        }

        // Create the workbasket item
        $sql = "insert into workbasket_items (
            workbasket_id, record_id, record_type, sub_record_type,
            status, created_at, operation)
        VALUES ($1, $2, $3, $4, $5, $6, $7)";

        pg_prepare($conn, "create_workbasket_item", $sql);
        $result = pg_execute($conn, "create_workbasket_item", array(
            $application->session->workbasket->workbasket_id, $_SESSION["measure_activity_sid"], "measure_activity", $sub_record_type,
            "In progress", $date, "C"
        ));
    }

    function persist_quota_core() {
        //Persisting quota core
        global $conn;

        $this->measure_activity_sid = $_SESSION["measure_activity_sid"];

        $sql = "update measure_activities set
        measure_type_id = $1,
        measure_generating_regulation_id = $2
        where measure_activity_sid = $3";
        $stmt = "persist_quota_core" . uniqid();
        pg_prepare($conn, $stmt, $sql);
        $result = pg_execute($conn, $stmt, array(
            $this->measure_type_id,
            $this->measure_generating_regulation_id,
            $this->measure_activity_sid
        ));
        //die();
    }

    function persist_commodities()
    {
        global $conn;

        // Delete any existing commodities
        $sql = "delete from measure_activity_commodities where measure_activity_sid = $1";
        pg_prepare($conn, "delete_commodities", $sql);
        pg_execute($conn, "delete_commodities", array(
            $this->measure_activity_sid
        ));

        // Save new commodities
        foreach ($this->commodity_code_list as $commodity) {
            $sql = "insert into measure_activity_commodities (measure_activity_sid, goods_nomenclature_item_id) values ($1, $2)";
            $gnii = $commodity->goods_nomenclature_item_id;
            $stmt = "persist_commodities" . $gnii;
            pg_prepare($conn, $stmt, $sql);
            pg_execute($conn, $stmt, array(
                $this->measure_activity_sid,
                $gnii
            ));
        }

        // Delete any existing additional codes
        $sql = "delete from measure_activity_additional_codes where measure_activity_sid = $1";
        pg_prepare($conn, "delete_additional_codes", $sql);
        pg_execute($conn, "delete_additional_codes", array(
            $this->measure_activity_sid
        ));

        // Save new additional codes
        foreach ($this->additional_code_list as $additional_code) {
            $code = $additional_code->code;
            $additional_code_type_id = substr($code, 0, 1) . "";
            $additional_code = substr($code, 1, 3) . "";
            if (($additional_code_type_id != "") && ($additional_code != "")) {
                //h1("Inserting");
                $sql = "insert into measure_activity_additional_codes (measure_activity_sid, additional_code_type_id, additional_code) values ($1, $2, $3)";
                $stmt = "persist_additional_codes" . $code;
                pg_prepare($conn, $stmt, $sql);
                pg_execute($conn, $stmt, array(
                    $this->measure_activity_sid,
                    $additional_code_type_id,
                    $additional_code
                ));
            }
        }
    }

    public function get_sid()
    {
        $temp = get_querystring("measure_activity_sid");
        if ($temp == "") {
            if (isset($_SESSION["measure_activity_sid"])) {
                $this->measure_activity_sid = $_SESSION["measure_activity_sid"];
            } else {
                $this->measure_activity_sid = null;
            }
        } else {
            $this->measure_activity_sid = $temp;
        }
    }

    public function populate_permutations_form()
    {
        global $conn;

        // Get core measure data
        $sql = "select ma.measure_type_id, activity_name,
        mt.order_number_capture_code, mt.measure_component_applicable_code
        from measure_activities ma, measure_types mt
        where measure_activity_sid = $1
        and ma.measure_type_id = mt.measure_type_id;";
        pg_prepare($conn, "p1", $sql);
        $result = pg_execute($conn, "p1", array(
            $this->measure_activity_sid
        ));

        $row_count = pg_num_rows($result);
        if (($result) && ($row_count > 0)) {
            $row = pg_fetch_array($result);
            $this->measure_type_id = $row['measure_type_id'];
            $this->activity_name = $row['activity_name'];
            $this->activity_name = $row['activity_name'];
            $this->order_number_capture_code = $row['order_number_capture_code'];
            $this->measure_component_applicable_code = $row['measure_component_applicable_code'];
        }


        // Get commodity codes
        $sql = "select * from measure_activity_commodities where measure_activity_sid = $1 order by goods_nomenclature_item_id";
        pg_prepare($conn, "populate_permutations_form", $sql);
        $result = pg_execute($conn, "populate_permutations_form", array($this->measure_activity_sid));
        $row_count = pg_num_rows($result);
        $this->commodity_code_list = array();
        $this->commodity_codes = "";
        if (($result) && ($row_count > 0)) {
            while ($row = pg_fetch_array($result)) {
                $gn = new goods_nomenclature;
                $gn->goods_nomenclature_item_id = $row['goods_nomenclature_item_id'];
                array_push($this->commodity_code_list, $gn);
                $this->commodity_codes .= $gn->goods_nomenclature_item_id . "\n";
            }
        }
        $this->commodity_codes = rtrim($this->commodity_codes);

        // Work out if the additional code form should be shown
        $additional_code_type_count = 0;
        $sql = "select count(*) as additional_code_type_count from additional_code_type_measure_types where measure_type_id = $1;";
        pg_prepare($conn, "get_acmt", $sql);
        $result = pg_execute($conn, "get_acmt", array($this->measure_type_id));
        $row_count = pg_num_rows($result);
        if (($result) && ($row_count > 0)) {
            $row = pg_fetch_row($result);
            $additional_code_type_count = $row[0];
        }
        if ($additional_code_type_count == 0) {
            $this->additional_codes = "";
            $this->suppress_additional_codes_field = true;
        } else {
            $this->suppress_additional_codes_field = false;

            // Get additional codes
            $sql = "select additional_code_type_id || additional_code as additional_code from measure_activity_additional_codes
            where measure_activity_sid = $1 order by additional_code_type_id, additional_code";
            pg_prepare($conn, "populate_additional_form", $sql);
            $result = pg_execute($conn, "populate_additional_form", array(
                $this->measure_activity_sid
            ));

            $row_count = pg_num_rows($result);
            $this->additional_code_list = array();
            $this->additional_codes = "";
            if (($result) && ($row_count > 0)) {
                while ($row = pg_fetch_array($result)) {
                    $ac = new additional_code;
                    $ac->code = $row['additional_code'];
                    array_push($this->additional_code_list, $ac);
                    $this->additional_codes .= $ac->code . "\n";
                }
            }
            $this->additional_codes = rtrim($this->additional_codes);
        }
    }

    public function populate_duties_form()
    {
        global $conn;

        $sql = "select ma.measure_type_id, activity_name,
        mt.order_number_capture_code, mt.measure_component_applicable_code
        from measure_activities ma, measure_types mt
        where measure_activity_sid = $1
        and ma.measure_type_id = mt.measure_type_id;";
        //pre ($sql);
        pg_prepare($conn, "p1", $sql);
        $result = pg_execute($conn, "p1", array(
            $this->measure_activity_sid
        ));

        $row_count = pg_num_rows($result);
        if (($result) && ($row_count > 0)) {
            $row = pg_fetch_array($result);
            $this->activity_name = $row['activity_name'];
            $this->measure_type_id = $row['measure_type_id'];
            $this->order_number_capture_code = $row['order_number_capture_code'];
            $this->measure_component_applicable_code = $row['measure_component_applicable_code'];
        }

        // Get commodity codes
        $sql = "select * from measure_activity_commodities where measure_activity_sid = $1 order by goods_nomenclature_item_id";
        pg_prepare($conn, "populate_duties_form", $sql);
        $result = pg_execute($conn, "populate_duties_form", array(
            $this->measure_activity_sid
        ));

        $row_count = pg_num_rows($result);
        $this->commodity_code_list = array();
        $this->commodity_codes = "";
        if (($result) && ($row_count > 0)) {
            while ($row = pg_fetch_array($result)) {
                $gn = new goods_nomenclature;
                $gn->goods_nomenclature_item_id = $row['goods_nomenclature_item_id'];
                array_push($this->commodity_code_list, $gn);
                $this->commodity_codes .= $gn->goods_nomenclature_item_id . "\n";
            }
        }
        $this->commodity_codes = rtrim($this->commodity_codes);

        // Get additional codes
        $sql = "select additional_code_type_id || additional_code as additional_code from measure_activity_additional_codes
        where measure_activity_sid = $1 order by additional_code_type_id, additional_code";
        pg_prepare($conn, "populate_additional_form", $sql);
        $result = pg_execute($conn, "populate_additional_form", array(
            $this->measure_activity_sid
        ));

        $row_count = pg_num_rows($result);
        $this->additional_code_list = array();
        $this->additional_codes = "";
        if (($result) && ($row_count > 0)) {
            while ($row = pg_fetch_array($result)) {
                $ac = new additional_code;
                $ac->code = trim($row['additional_code']);
                if ($ac->code != "") {
                    $ac->spilt_code();
                    $ac->get_details_from_id();
                    array_push($this->additional_code_list, $ac);
                    $this->additional_codes .= $ac->code . "\n";
                }
            }
        }
        $this->additional_codes = rtrim($this->additional_codes);
    }

    public function populate_footnotes_form()
    {
        // Get footnotes
        global $conn;

        $sql = "select maf.footnote_type_id, maf.footnote_id, f.description 
        from measure_activity_footnotes maf, ml.ml_footnotes f
        where maf.footnote_id = f.footnote_id 
        and maf.footnote_type_id = f.footnote_type_id
        and maf.measure_activity_sid = $1
        order by maf.footnote_type_id, maf.footnote_id ";
        pg_prepare($conn, "populate_footnote_form", $sql);
        $result = pg_execute($conn, "populate_footnote_form", array(
            $this->measure_activity_sid
        ));

        $row_count = pg_num_rows($result);
        $this->footnote_list = array();
        if (($result) && ($row_count > 0)) {
            while ($row = pg_fetch_array($result)) {
                $f = new footnote;
                $f->footnote_type_id = $row['footnote_type_id'];
                $f->footnote_id = $row['footnote_id'];
                $f->description = $row['description'];
                $f->delete_action = "<a class='govuk-link' href='/measures/measure_activity_actions.php?action=delete_footnote&footnote_type_id=" . $f->footnote_type_id . "&footnote_id=" . $f->footnote_id . "'><img alt='Delete footnote' src='/assets/images/delete.png' /></a>";

                array_push($this->footnote_list, $f);
            }
        }
    }

    public function populate_conditions_form()
    {
        // Get conditions
        global $conn;
        $this->measure_activity_sid = $_SESSION["measure_activity_sid"];

        $sql = "select mac.measure_activity_condition_sid, mac.condition_code, mcd.description as condition_code_description, component_sequence_number,
        reference_price, mac.action_code, mad.description as action_code_description,
        certificate_type_code || certificate_code as code, certificate_type_code, certificate_code, mac.applicable_duty, mac.applicable_duty_permutation,
        COUNT (*) OVER (PARTITION BY mac.condition_code) as condition_code_count
        from measure_activity_conditions mac, measure_action_descriptions mad, measure_condition_code_descriptions mcd
        where mac.action_code = mad.action_code
        and mac.condition_code = mcd.condition_code
        and measure_activity_sid = $1
        order by condition_code, component_sequence_number;";

        pg_prepare($conn, "populate_conditions_form", $sql);
        $result = pg_execute($conn, "populate_conditions_form", array(
            $this->measure_activity_sid
        ));

        $row_count = pg_num_rows($result);
        $this->condition_list = array();
        if (($result) && ($row_count > 0)) {
            while ($row = pg_fetch_array($result)) {
                $mc = new measure_condition;
                $mc->measure_activity_condition_sid = $row['measure_activity_condition_sid'];
                $mc->applicable_duty_permutation = $row['applicable_duty_permutation'];
                if ($mc->applicable_duty_permutation == 1) {
                    $mc->applicable_duty = "Variable";
                } else {
                    $mc->applicable_duty = $row['applicable_duty'];
                }
                $mc->condition_code = $row['condition_code'] . ". " . $row['condition_code_description'];
                $mc->component_sequence_number = $row['component_sequence_number'];
                $mc->condition_code_count = $row['condition_code_count'];
                /*
                $mc->condition_monetary_unit_code = $row['condition_monetary_unit_code'];
                $mc->condition_measurement_unit_code = $row['condition_measurement_unit_code'];
                $mc->condition_measurement_unit_qualifier_code = $row['condition_measurement_unit_qualifier_code'];
                */
                $mc->action_code = $row['action_code'] . ' ' . $row['action_code_description'];
                $mc->certificate_code = $row['certificate_type_code'] . $row['certificate_code'];
                //$mc->reference_price = $row['condition_duty_amount'] . $row['condition_monetary_unit_code'] . $row['condition_measurement_unit_code'] . $row['condition_measurement_unit_qualifier_code'];
                $mc->reference_price = $row['reference_price'];

                $mc->delete_string = "<a title='Delete this condition' class='govuk-link' href='/measures/measure_activity_actions.html?action=delete_condition&measure_activity_condition_sid=" . $mc->measure_activity_condition_sid . "'><img alt='Delete condition' src='/assets/images/delete.png' /></a>";
                $mc->action_string = "";
                if ($mc->component_sequence_number > 1) {
                    $mc->action_string .= "<a title='Promote this condition' href='/measures/measure_activity_actions.html?action=promote_condition&measure_activity_condition_sid=" . $mc->measure_activity_condition_sid . "'><img alt='Promote condition' src='/assets/images/promote.png' /></a>";
                } else {
                    $mc->action_string .= "<img alt='' src='/assets/images/blank.png' />";
                }
                if ($mc->component_sequence_number < $mc->condition_code_count) {
                    $mc->action_string .= "<a title='Demote this condition' href='/measures/measure_activity_actions.html?action=demote_condition&measure_activity_condition_sid=" . $mc->measure_activity_condition_sid . "'><img alt='Demote condition' src='/assets/images/demote.png' /></a>";
                } else {
                    $mc->action_string .= "<img alt='' src='/assets/images/blank.png' />";
                }
                $mc->action_string .= $mc->delete_string;

                array_push($this->condition_list, $mc);
            }
        }
    }

    public function get_commodity_code_array()
    {
        $this->commodity_code_list = array();
        $ccs = $this->commodity_codes;

        $ccs = str_replace("\n", " ", $ccs);
        $ccs = str_replace("\r", " ", $ccs);


        $parts = preg_split('/\s+/', $ccs);

        foreach ($parts as $cc) {
            $gn = new goods_nomenclature;
            $gn->goods_nomenclature_item_id = $cc;
            array_push($this->commodity_code_list, $gn);
        }
    }

    public function get_additional_code_array()
    {
        $this->additional_code_list = array();
        $ccs = $this->additional_codes;

        $ccs = str_replace("\n", " ", $ccs);
        $ccs = str_replace("\r", " ", $ccs);
        $ccs = trim($ccs);

        $parts = preg_split('/\s+/', $ccs);

        foreach ($parts as $cc) {
            $ac = new additional_code;
            $ac->code = $cc;
            array_push($this->additional_code_list, $ac);
        }
    }

    public function get_certificate_array()
    {
        $this->additional_code_list = array();
        $ccs = $this->additional_codes;

        $ccs = str_replace("\n", " ", $ccs);
        $ccs = str_replace("\r", " ", $ccs);
        $ccs = trim($ccs);

        $parts = preg_split('/\s+/', $ccs);

        foreach ($parts as $cc) {
            $ac = new additional_code;
            $ac->code = $cc;
            array_push($this->additional_code_list, $ac);
        }
    }

    public function validate_form_permutations()
    {
        global $application;
        $this->measure_activity_sid = $_SESSION["measure_activity_sid"];
        $errors = array();

        $this->commodity_codes = $_REQUEST["commodity_codes"];
        $this->additional_codes = $_REQUEST["additional_codes"];
        $this->certificate_list = $_REQUEST["certificate_list"];

        $this->get_commodity_code_array();
        $this->get_additional_code_array();
        $this->get_certificate_array();

        if (!$this->check_commodities()) {
            array_push($errors, "commodity_codes");
        }
        if (!$this->check_additional_codes()) {
            array_push($errors, "additional_codes");
        }

        $this->persist_commodities();
        if (count($errors) > 0) {
            $error_string = serialize($errors);
            setcookie("errors", $error_string, time() + (86400 * 30), "/");
            $url = "create_edit_permutations.html?err=1&mode=" . $application->mode;
        } else {
            $url = "./create_edit_conditions.html?mode=" . $application->mode;
        }
        header("Location: " . $url);
    }


    public function validate_form_duties()
    {
        global $application;
        $this->measure_activity_sid = $_SESSION["measure_activity_sid"];

        // Go to the database and work out what conditions there are
        global $conn;
        $sql = "select measure_activity_condition_sid, condition_code, component_sequence_number,
        (condition_code || component_sequence_number) as condition_id, mac.action_code 
        from measure_activity_conditions mac, measure_actions ma 
        where measure_activity_sid = $1
        and mac.action_code = ma.action_code 
        and ma.requires_duty = true
        order by condition_code, component_sequence_number ;";
        $stmt = "get_measure_activity_" . uniqid();
        pg_prepare($conn, $stmt, $sql);
        $result = pg_execute($conn, $stmt, array($this->measure_activity_sid));
        $row_count = pg_num_rows($result);
        $measure_conditions = array();
        $condition_count = 0;
        if (($result) && ($row_count > 0)) {
            while ($row = pg_fetch_array($result)) {
                $measure_condition = new reusable();
                $measure_condition->key = "";
                $measure_condition->measure_activity_condition_sid = $row["measure_activity_condition_sid"];
                $measure_condition->condition_code = $row["condition_code"];
                $measure_condition->condition_id = $row["condition_id"];
                $measure_condition->component_sequence_number = $row["component_sequence_number"];
                $measure_condition->action_code = $row["action_code"];
                array_push($measure_conditions, $measure_condition);
                $condition_count += 1;
            }
        }

        $table_data_string = get_formvar("table_data");
        $table_data = json_decode($table_data_string, true);

        $has_commodity_codes = false;
        $has_additional_codes = false;

        if (count($table_data) > 0) {
            // Check the fitrst object to compare the names of the fields against the condition codes
            $row = $table_data[0];
            foreach ($row as $key => $value) {
                if (strpos($key, ":") !== false) {
                    $key2 = substr($key, 0, strpos($key, ":"));
                    foreach ($measure_conditions as $measure_condition) {
                        if ($measure_condition->condition_id == $key2) {
                            $measure_condition->key = $key;
                            break;
                        }
                    }
                    $is_conditional = true;
                } elseif ($key == "Standard (non-conditional) duty") {
                    $is_conditional = null;
                } elseif ($key == "commodity_code") {
                    $has_commodity_codes = true;
                } elseif ($key == "additional_code") {
                    $has_additional_codes = true;
                }
            }

            //h1 ($is_conditional);
            //die();

            // Delete the existing records for this measure activity sid
            $sql = "delete from measure_activity_duties where measure_activity_sid = $1";
            $stmt = "delete_duties_" . uniqid();
            pg_prepare($conn, $stmt, $sql);
            pg_execute($conn, $stmt, array($this->measure_activity_sid));

            // Now we know how to save the data, it's time to save it
            foreach ($table_data as $table_row) {
                $commodity_code = null;
                $additional_code = null;
                if ($has_commodity_codes == true) {
                    $commodity_code = $table_row["commodity_code"];
                }
                if ($has_additional_codes) {
                    $additional_code = $table_row["additional_code"];
                }
                $measure_activity_condition_sid = null;
                foreach ($table_row as $key => $value) {
                    if (($key != "commodity_code") && ($key != "additional_code") && ($key != "pq_cellselect")) {
                        // Get the condition_sid
                        if ($is_conditional) {
                            foreach ($measure_conditions as $measure_condition) {
                                if ($measure_condition->key == $key) {
                                    $measure_activity_condition_sid = $measure_condition->measure_activity_condition_sid;
                                    break;
                                }
                            }
                        } else {
                            $measure_activity_condition_sid = null;
                        }
                        //pre($key);
                        $sql = "insert into measure_activity_duties (
                        measure_activity_sid,
                        duty,
                        is_conditional,
                        measure_activity_condition_sid,
                        commodity_code,
                        additional_code)
                        values ($1, $2, $3, $4, $5, $6);";
                        $stmt = "insert_duty_" . uniqid();
                        pg_prepare($conn, $stmt, $sql);
                        pg_execute($conn, $stmt, array(
                            $this->measure_activity_sid,
                            $value,
                            $is_conditional,
                            $measure_activity_condition_sid,
                            $commodity_code,
                            $additional_code
                        ));
                    }
                }
            }
        }
        //pre($measure_conditions);
        //prend($table_data);
        $errors = array();
        if (count($errors) > 0) {
            $error_string = serialize($errors);
            setcookie("errors", $error_string, time() + (86400 * 30), "/");
            $url = "create_edit_duties.html?err=1&mode=" . $application->mode;
        } else {
            //$this->persist_commodities();

            $url = "./create_edit_conditions.html?mode=" . $application->mode;
        }
        //die();

        $url = "./create_edit_footnotes.html?mode=" . $application->mode;
        header("Location: " . $url);
    }


    public function check_commodities()
    {
        $valid = true;
        foreach ($this->commodity_code_list as $commodity) {
            if (strlen($commodity->goods_nomenclature_item_id) != 10) {
                $valid = false;
                break;
            }
        }
        return ($valid);
    }

    public function check_additional_codes()
    {
        $valid = true;
        foreach ($this->additional_code_list as $additional_code) {
            $code = trim($additional_code->code);
            if ($code != "") {
                if (strlen($additional_code->code) != 4) {
                    $valid = false;
                    break;
                }
            }
        }
        return ($valid);
    }

    public function set_properties(
        $measure_sid,
        $goods_nomenclature_item_id,
        $quota_order_number_id,
        $validity_start_date,
        $validity_end_date,
        $geographical_area_id,
        $measure_type_id,
        $additional_code_type_id,
        $additional_code_id,
        $regulation_id_full,
        $measure_type_description = ""
    ) {
        $this->measure_sid = $measure_sid;
        $this->goods_nomenclature_item_id = $goods_nomenclature_item_id;
        $this->quota_order_number_id = $quota_order_number_id;
        $this->validity_start_date = $validity_start_date;
        $this->validity_end_date = $validity_end_date;
        $this->geographical_area_id = $geographical_area_id;
        $this->measure_type_id = $measure_type_id;
        $this->additional_code_type_id = $additional_code_type_id;
        $this->additional_code_id = $additional_code_id;
        $this->regulation_id_full = $regulation_id_full;
        $this->measure_type_description = $measure_type_description;
    }

    /* BEGIN MEASURE FUNCTIONS */
    public function insert_measure()
    {
        global $conn;
        $application = new application;
        $operation = "C";
        $operation_date = $application->get_operation_date();
        $this->get_new_measure_sid();

        $sql = "INSERT INTO measures_oplog (
 measure_sid,
 measure_type_id,
 geographical_area_id,
 goods_nomenclature_item_id,
 validity_start_date,
 validity_end_date,
 measure_generating_regulation_role,
 measure_generating_regulation_id,
 justification_regulation_role,
 justification_regulation_id,
 stopped_flag,
 geographical_area_sid,
 goods_nomenclature_sid,
 ordernumber,
 additional_code_type_id,
 additional_code_id,
 additional_code_sid,
 reduction_indicator,
 export_refund_nomenclature_sid, operation, operation_date)
 VALUES (
 $1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14, $15, $16, $17, $18, $19, $20, $21)";

        pg_prepare($conn, "measure_insert", $sql);
        pg_execute($conn, "measure_insert", array(
            $this->measure_sid,
            $this->measure_type_id,
            $this->geographical_area_id,
            $this->goods_nomenclature_item_id,
            $this->validity_start_date,
            $this->validity_end_date,
            $this->measure_generating_regulation_role,
            $this->measure_generating_regulation_id,
            $this->justification_regulation_role,
            $this->justification_regulation_id,
            $this->stopped_flag,
            $this->geographical_area_sid,
            $this->goods_nomenclature_sid,
            $this->ordernumber,
            $this->additional_code_type_id,
            $this->additional_code_id,
            $this->additional_code_sid,
            $this->reduction_indicator,
            $this->export_refund_nomenclature_sid,
            $operation, $operation_date
        ));

        $url = "/measures/view.html?mode=view&measure_sid=" . $this->measure_sid;
        header("Location: " . $url);
    }

    public function update_measure()
    {
        global $conn;
        $application = new application;
        $operation = "U";
        $operation_date = $application->get_operation_date();

        $sql = "INSERT INTO measures_oplog (
 measure_sid,
 measure_type_id,
 geographical_area_id,
 goods_nomenclature_item_id,
 validity_start_date,
 validity_end_date,
 measure_generating_regulation_role,
 measure_generating_regulation_id,
 justification_regulation_role,
 justification_regulation_id,
 stopped_flag,
 geographical_area_sid,
 goods_nomenclature_sid,
 ordernumber,
 additional_code_type_id,
 additional_code_id,
 additional_code_sid,
 reduction_indicator,
 export_refund_nomenclature_sid, operation, operation_date)
 VALUES (
 $1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14, $15, $16, $17, $18, $19, $20, $21)";

        pg_prepare($conn, "measure_update", $sql);
        pg_execute($conn, "measure_update", array(
            $this->measure_sid,
            $this->measure_type_id,
            $this->geographical_area_id,
            $this->goods_nomenclature_item_id,
            $this->validity_start_date,
            $this->validity_end_date,
            $this->measure_generating_regulation_role,
            $this->measure_generating_regulation_id,
            $this->justification_regulation_role,
            $this->justification_regulation_id,
            $this->stopped_flag,
            $this->geographical_area_sid,
            $this->goods_nomenclature_sid,
            $this->ordernumber,
            $this->additional_code_type_id,
            $this->additional_code_id,
            $this->additional_code_sid,
            $this->reduction_indicator,
            $this->export_refund_nomenclature_sid,
            $operation, $operation_date
        ));

        $url = "/measures/view.html?mode=view&measure_sid=" . $this->measure_sid;
        header("Location: " . $url);
    }

    public function get_new_measure_sid()
    {
        global $conn;
        $sql = "select max(measure_sid) from measures";
        $result = pg_query($conn, $sql);
        if ($result) {
            $row = pg_fetch_row($result);
            $this->measure_sid = $row[0] + 1;
        }
    }

    /* END MEASURE FUNCTIONS */

    /* BEGIN MEASURE COMPONENT FUNCTIONS */
    public function update_component($measure_sid, $duty_expression_id, $duty_amount, $monetary_unit_code, $measurement_unit_code, $measurement_unit_qualifier_code)
    {
        global $conn;
        $application = new application;
        $operation = "U";
        $operation_date = $application->get_operation_date();

        $sql = "INSERT INTO measure_components_oplog
 (measure_sid, duty_expression_id, duty_amount, monetary_unit_code,
 measurement_unit_code, measurement_unit_qualifier_code, operation, operation_date)
 VALUES (
 $1, $2, $3, $4, $5, $6, $7, $8)";

        pg_prepare($conn, "component_insert", $sql);
        pg_execute($conn, "component_insert", array(
            $measure_sid, $duty_expression_id, $duty_amount, $monetary_unit_code,
            $measurement_unit_code, $measurement_unit_qualifier_code, $operation, $operation_date
        ));

        return (True);
    }

    public function insert_component($measure_sid, $duty_expression_id, $duty_amount, $monetary_unit_code, $measurement_unit_code, $measurement_unit_qualifier_code)
    {
        global $conn;
        $application = new application;
        $operation = "C";
        $operation_date = $application->get_operation_date();

        $sql = "INSERT INTO measure_components_oplog
 (measure_sid, duty_expression_id, duty_amount, monetary_unit_code,
 measurement_unit_code, measurement_unit_qualifier_code, operation, operation_date)
 VALUES (
 $1, $2, $3, $4, $5, $6, $7, $8)";

        pg_prepare($conn, "component_insert", $sql);
        pg_execute($conn, "component_insert", array(
            $measure_sid, $duty_expression_id, $duty_amount, $monetary_unit_code,
            $measurement_unit_code, $measurement_unit_qualifier_code, $operation, $operation_date
        ));

        return (True);
    }


    public function insert_footnote_association_measure($measure_sid, $footnote_type_id, $footnote_id)
    {
        global $conn;
        $application = new application;
        $operation = "C";
        $operation_date = $application->get_operation_date();

        $sql = "INSERT INTO footnote_association_measures_oplog
 (measure_sid, footnote_type_id, footnote_id, operation, operation_date)
 VALUES (
 $1, $2, $3, $4, $5)";

        pg_prepare($conn, "insert_footnote_association_measure", $sql);
        pg_execute($conn, "insert_footnote_association_measure", array($measure_sid, $footnote_type_id, $footnote_id, $operation, $operation_date));
        return (True);
    }


    public function get_measure_oplog_components()
    {
        global $conn;
        $this->measure_components = array();
        $sql = "select duty_expression_id, duty_amount, monetary_unit_code, measurement_unit_code, measurement_unit_qualifier_code
 from measure_components_oplog where measure_sid = $1 and operation = $2 order by duty_expression_id";
        $query_name = "get_measure_oplog_components_" . $this->measure_sid;
        pg_prepare($conn, $query_name, $sql);
        $result = pg_execute($conn, $query_name, array($this->measure_sid, $this->operation));
        $row_count = pg_num_rows($result);
        if (($result) && ($row_count > 0)) {
            while ($row = pg_fetch_array($result)) {
                $measure_component = new measure_component();
                $measure_component->measure_sid = $this->measure_sid;
                $measure_component->duty_expression_id = $row[0];
                $measure_component->duty_amount = $row[1];
                $measure_component->monetary_unit_code = $row[2];
                $measure_component->measurement_unit_code = $row[3];
                $measure_component->measurement_unit_qualifier_code = $row[4];
                $measure_component->operation = $this->operation;
                array_push($this->measure_components, $measure_component);
            }
        }
        $this->measure_components_xml = "";
        foreach ($this->measure_components as $measure_component) {
            $this->measure_components_xml .= $measure_component->xml();
        }
    }

    public function get_measure_activity_conditions()
    {
    }

    public function get_measure_oplog_conditions()
    {
        global $conn;
        $this->measure_conditions = array();
        $sql = "select measure_condition_sid, measure_sid, condition_code, component_sequence_number, condition_duty_amount, condition_monetary_unit_code,
 condition_measurement_unit_code, condition_measurement_unit_qualifier_code,
 action_code, certificate_type_code, certificate_code
 from measure_conditions
 where measure_sid = $1 and operation = $2
 order by component_sequence_number";
        $query_name = "get_measure_oplog_conditions_" . $this->measure_sid;
        pg_prepare($conn, $query_name, $sql);
        $result = pg_execute($conn, $query_name, array($this->measure_sid, $this->operation));
        $row_count = pg_num_rows($result);
        if (($result) && ($row_count > 0)) {
            while ($row = pg_fetch_array($result)) {
                $measure_condition = new measure_condition();
                $measure_condition->measure_sid = $this->measure_sid;
                $measure_condition->measure_condition_sid = $row[0];
                $measure_condition->condition_code = $row[2];
                $measure_condition->component_sequence_number = $row[3];
                $measure_condition->condition_duty_amount = $row[4];
                $measure_condition->condition_monetary_unit_code = $row[5];
                $measure_condition->condition_measurement_unit_code = $row[6];
                $measure_condition->condition_measurement_unit_qualifier_code = $row[7];
                $measure_condition->action_code = $row[8];
                $measure_condition->certificate_type_code = $row[9];
                $measure_condition->certificate_code = $row[10];
                $measure_condition->operation = $this->operation;
                array_push($this->measure_conditions, $measure_condition);
            }
        }
        $this->measure_conditions_xml = "";
        foreach ($this->measure_conditions as $measure_condition) {
            $this->measure_conditions_xml .= $measure_condition->xml();
        }
    }


    public function get_measure_oplog_excluded_geographical_areas()
    {
        global $conn;
        $this->measure_excluded_geographical_areas = array();
        $sql = "select excluded_geographical_area, geographical_area_sid from measure_excluded_geographical_areas_oplog where measure_sid = $1 and operation = $2
 order by excluded_geographical_area";
        $query_name = "get_measure_oplog_excluded_geographical_areas_" . $this->measure_sid;
        pg_prepare($conn, $query_name, $sql);
        $result = pg_execute($conn, $query_name, array($this->measure_sid, $this->operation));
        $row_count = pg_num_rows($result);
        if (($result) && ($row_count > 0)) {
            while ($row = pg_fetch_array($result)) {
                $measure_excluded_geographical_area = new measure_excluded_geographical_area();
                $measure_excluded_geographical_area->measure_sid = $this->measure_sid;
                $measure_excluded_geographical_area->excluded_geographical_area = $row[0];
                $measure_excluded_geographical_area->geographical_area_sid = $row[1];
                $measure_excluded_geographical_area->operation = $this->operation;
                array_push($this->measure_excluded_geographical_areas, $measure_excluded_geographical_area);
            }
        }
        $this->measure_excluded_geographical_areas_xml = "";
        foreach ($this->measure_excluded_geographical_areas as $measure_excluded_geographical_area) {
            $this->measure_excluded_geographical_areas_xml .= $measure_excluded_geographical_area->xml();
        }
    }


    public function get_measure_components()
    {
        global $conn;
        $this->measure_components = array();
        $sql = "select duty_expression_id, duty_amount, monetary_unit_code, measurement_unit_code, measurement_unit_qualifier_code
 from measure_components where measure_sid = $1 order by duty_expression_id";
        pg_prepare($conn, "get_measure_components", $sql);
        $result = pg_execute($conn, "get_measure_components", array($this->measure_sid));
        $row_count = pg_num_rows($result);
        if (($result) && ($row_count > 0)) {
            while ($row = pg_fetch_array($result)) {
                $measure_component = new measure_component();
                $measure_component->measure_sid = $this->measure_sid;
                $measure_component->duty_expression_id = $row[0];
                $measure_component->duty_amount = $row[1];
                $measure_component->monetary_unit_code = $row[2];
                $measure_component->measurement_unit_code = $row[3];
                $measure_component->measurement_unit_qualifier_code = $row[4];
                array_push($this->measure_components, $measure_component);
            }
        }
    }

    public function delete_measure_components()
    {
        global $conn;
        $application = new application;
        $operation = "D";
        $operation_date = $application->get_operation_date();
        $i = 0;
        foreach ($this->measure_components as $measure_component) {
            $sql = "INSERT INTO measure_components_oplog
 (measure_sid, duty_expression_id, duty_amount, monetary_unit_code,
 measurement_unit_code, measurement_unit_qualifier_code, operation, operation_date)
 VALUES (
 $1, $2, $3, $4, $5, $6, $7, $8)";
            $query_name = "component_delete" . $i;

            pg_prepare($conn, $query_name, $sql);
            pg_execute($conn, $query_name, array(
                $measure_component->measure_sid, $measure_component->duty_expression_id,
                $measure_component->duty_amount, $measure_component->monetary_unit_code,
                $measure_component->measurement_unit_code, $measure_component->measurement_unit_qualifier_code, $operation, $operation_date
            ));
            $i += 1;
        }
    }

    public function delete_measure()
    {
        global $conn;
        $application = new application;
        $operation = "D";
        $operation_date = $application->get_operation_date();
        $i = 0;

        $sql = "insert into measures_oplog (
 measure_sid, measure_type_id, geographical_area_id, goods_nomenclature_item_id,
 validity_start_date, validity_end_date, measure_generating_regulation_role, measure_generating_regulation_id,
 justification_regulation_role, justification_regulation_id, stopped_flag, geographical_area_sid,
 goods_nomenclature_sid, ordernumber, additional_code_type_id, additional_code_id,
 additional_code_sid,
 reduction_indicator,
 export_refund_nomenclature_sid,
 operation,
 operation_date
 )
 select
 measure_sid,
 measure_type_id,
 geographical_area_id,
 goods_nomenclature_item_id,
 validity_start_date,
 validity_end_date,
 measure_generating_regulation_role,
 measure_generating_regulation_id,
 justification_regulation_role,
 justification_regulation_id,
 stopped_flag,
 geographical_area_sid,
 goods_nomenclature_sid,
 ordernumber,
 additional_code_type_id,
 additional_code_id,
 additional_code_sid,
 reduction_indicator,
 export_refund_nomenclature_sid,
 'D',
 '" . $operation_date . "'
 from measures_oplog where measure_sid=$1 order by oid desc limit 1;";

        $query_name = "measure_delete";

        pg_prepare($conn, $query_name, $sql);
        pg_execute($conn, $query_name, array($this->measure_sid));
    }

    public function get_goods_nomenclature_item_id()
    {
        global $conn;
        $sql = "select goods_nomenclature_item_id, geographical_area_id from measures where measure_sid = $1";
        pg_prepare($conn, "get_goods_nomenclature_item_id", $sql);
        $result = pg_execute($conn, "get_goods_nomenclature_item_id", array($this->measure_sid));
        $row_count = pg_num_rows($result);
        if (($result) && ($row_count > 0)) {
            $row = pg_fetch_row($result);
            $this->goods_nomenclature_item_id = $row[0];
            $this->geographical_area_id = $row[1];
        }
    }

    public function delete_component($measure_sid, $duty_expression_id)
    {
        // Here we need to get the latest details of the component from the database before adding in a delete record
        global $conn;
        $application = new application;
        $operation = "D";
        $operation_date = $application->get_operation_date();

        $sql = "select duty_amount, monetary_unit_code, measurement_unit_code, measurement_unit_qualifier_code
 from measure_components
 where measure_sid = $1 and duty_expression_id = $2";
        pg_prepare($conn, "get_component", $sql);
        $result = pg_execute($conn, "get_component", array($measure_sid, $duty_expression_id));
        $row_count = pg_num_rows($result);
        if (($result) && ($row_count > 0)) {
            $row = pg_fetch_row($result);
            $duty_amount = $row[0];
            $monetary_unit_code = $row[1];
            $measurement_unit_code = $row[2];
            $measurement_unit_qualifier_code = $row[3];
        }

        $sql = "INSERT INTO measure_components_oplog
 (measure_sid, duty_expression_id, duty_amount, monetary_unit_code,
 measurement_unit_code, measurement_unit_qualifier_code, operation, operation_date)
 VALUES (
 $1, $2, $3, $4, $5, $6, $7, $8)";

        pg_prepare($conn, "component_delete", $sql);
        pg_execute($conn, "component_delete", array(
            $measure_sid, $duty_expression_id, $duty_amount, $monetary_unit_code,
            $measurement_unit_code, $measurement_unit_qualifier_code, $operation, $operation_date
        ));

        return (True);
    }
    /* END MEASURE COMPONENT FUNCTIONS */

    /* BEGIN CONDITION COMPONENT FUNCTIONS */
    public function get_measure_condition_components()
    {
        global $conn;
        $this->measure_condition_components = array();
        $sql = "select mcc.measure_condition_sid, mcc.duty_expression_id, mcc.duty_amount, mcc.monetary_unit_code,
 mcc.measurement_unit_code, mcc.measurement_unit_qualifier_code
 from measure_condition_components mcc, measure_conditions mc
 where mc.measure_condition_sid = mcc.measure_condition_sid and measure_sid = $1
 order by mc.component_sequence_number, mcc.duty_expression_id;";
        pg_prepare($conn, "get_measure_condition_components", $sql);
        $result = pg_execute($conn, "get_measure_condition_components", array($this->measure_sid));
        $row_count = pg_num_rows($result);
        if (($result) && ($row_count > 0)) {
            while ($row = pg_fetch_array($result)) {
                $measure_condition_component = new measure_condition_component();
                $measure_condition_component->measure_sid = $this->measure_sid;
                $measure_condition_component->measure_condition_sid = $row[0];
                $measure_condition_component->duty_expression_id = $row[1];
                $measure_condition_component->duty_amount = $row[2];
                $measure_condition_component->monetary_unit_code = $row[3];
                $measure_condition_component->measurement_unit_code = $row[4];
                $measure_condition_component->measurement_unit_qualifier_code = $row[5];
                array_push($this->measure_condition_components, $measure_condition_component);
            }
        }
    }

    public function delete_measure_condition_components()
    {
        global $conn;
        $application = new application;
        $operation = "D";
        $operation_date = $application->get_operation_date();
        $i = 0;
        foreach ($this->measure_condition_components as $mcc) {
            $sql = "INSERT INTO measure_condition_components_oplog
 (measure_condition_sid, duty_expression_id, duty_amount, monetary_unit_code,
 measurement_unit_code, measurement_unit_qualifier_code,
 operation, operation_date)
 VALUES (
 $1, $2, $3, $4, $5, $6, $7, $8)";
            $query_name = "mcc_delete" . $i;

            pg_prepare($conn, $query_name, $sql);
            pg_execute($conn, $query_name, array(
                $mcc->measure_condition_sid, $mcc->duty_expression_id, $mcc->duty_amount, $mcc->monetary_unit_code,
                $mcc->measurement_unit_code, $mcc->measurement_unit_qualifier_code,
                $operation, $operation_date
            ));
            $i += 1;
        }
    }

    /* BEGIN MEASURE CONDITION FUNCTIONS */
    public function get_measure_conditions()
    {
        global $conn;
        $this->measure_conditions = array();
        $sql = "select measure_condition_sid, condition_code, component_sequence_number, condition_duty_amount, 
 condition_monetary_unit_code, condition_measurement_unit_code, condition_measurement_unit_qualifier_code
 from measure_conditions where measure_sid = $1 order by component_sequence_number;";
        pg_prepare($conn, "get_measure_conditions", $sql);
        $result = pg_execute($conn, "get_measure_conditions", array($this->measure_sid));
        $row_count = pg_num_rows($result);
        if (($result) && ($row_count > 0)) {
            while ($row = pg_fetch_array($result)) {
                $measure_condition = new measure_condition();
                $measure_condition->measure_sid = $this->measure_sid;
                $measure_condition->measure_condition_sid = $row[0];
                $measure_condition->condition_code = $row[1];
                $measure_condition->component_sequence_number = $row[2];
                $measure_condition->condition_duty_amount = $row[3];
                $measure_condition->condition_monetary_unit_code = $row[4];
                $measure_condition->condition_measurement_unit_code = $row[5];
                $measure_condition->condition_measurement_unit_qualifier_code = $row[6];
                array_push($this->measure_conditions, $measure_condition);
            }
        }
    }

    public function delete_measure_conditions()
    {
        global $conn;
        $application = new application;
        $operation = "D";
        $operation_date = $application->get_operation_date();
        $i = 0;
        foreach ($this->measure_conditions as $mc) {
            $sql = "INSERT INTO measure_conditions_oplog
 (measure_condition_sid, measure_sid, condition_code, component_sequence_number,
 condition_duty_amount, condition_monetary_unit_code, condition_measurement_unit_code, 
 condition_measurement_unit_qualifier_code, action_code, certificate_type_code, certificate_code,
 operation, operation_date)
 VALUES (
 $1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13)";
            $query_name = "mc_delete" . $i;

            pg_prepare($conn, $query_name, $sql);
            pg_execute($conn, $query_name, array(
                $mc->measure_condition_sid,
                $mc->measure_sid,
                $mc->condition_code,
                $mc->component_sequence_number,
                $mc->condition_duty_amount,
                $mc->condition_monetary_unit_code,
                $mc->condition_measurement_unit_code,
                $mc->condition_measurement_unit_qualifier_code,
                $mc->action_code,
                $mc->certificate_type_code,
                $mc->certificate_code,
                $operation, $operation_date
            ));
            $i += 1;
        }
    }
    /* END MEASURE CONDITION FUNCTIONS */

    /* BEGIN PARTIAL TEMPORARY STOP FUNCTIONS */
    public function get_measure_partial_temporary_stops()
    {
        global $conn;
        $this->measure_partial_temporary_stops = array();
        $sql = "select validity_start_date, validity_end_date, partial_temporary_stop_regulation_id,
 partial_temporary_stop_regulation_officialjournal_number, partial_temporary_stop_regulation_officialjournal_page,
 abrogation_regulation_id, abrogation_regulation_officialjournal_number, abrogation_regulation_officialjournal_page
 from measure_partial_temporary_stops where measure_sid = $1;";
        pg_prepare($conn, "get_measure_partial_temporary_stops", $sql);
        $result = pg_execute($conn, "get_measure_partial_temporary_stops", array($this->measure_sid));
        $row_count = pg_num_rows($result);
        if (($result) && ($row_count > 0)) {
            while ($row = pg_fetch_array($result)) {
                $measure_partial_temporary_stop = new measure_partial_temporary_stop();
                $measure_partial_temporary_stop->measure_sid = $this->measure_sid;
                $measure_partial_temporary_stop->validity_start_date = $row[0];
                $measure_partial_temporary_stop->validity_end_date = $row[1];
                $measure_partial_temporary_stop->partial_temporary_stop_regulation_id = $row[2];
                $measure_partial_temporary_stop->partial_temporary_stop_regulation_officialjournal_number = $row[3];
                $measure_partial_temporary_stop->partial_temporary_stop_regulation_officialjournal_page = $row[4];
                $measure_partial_temporary_stop->abrogation_regulation_id = $row[5];
                $measure_partial_temporary_stop->abrogation_regulation_officialjournal_number = $row[6];
                $measure_partial_temporary_stop->abrogation_regulation_officialjournal_page = $row[7];
                array_push($this->measure_partial_temporary_stops, $measure_partial_temporary_stop);
            }
        }
    }

    public function delete_measure_partial_temporary_stops()
    {
        global $conn;
        $application = new application;
        $operation = "D";
        $operation_date = $application->get_operation_date();
        $i = 0;
        foreach ($this->measure_partial_temporary_stops as $mpts) {
            $sql = "INSERT INTO measure_partial_temporary_stops_oplog
 (measure_sid, validity_start_date, validity_end_date, partial_temporary_stop_regulation_id,
 partial_temporary_stop_regulation_officialjournal_number, partial_temporary_stop_regulation_officialjournal_page,
 abrogation_regulation_id, abrogation_regulation_officialjournal_number, abrogation_regulation_officialjournal_page,
 operation, operation_date)
 VALUES (
 $1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11)";
            $query_name = "mpts_delete" . $i;

            pg_prepare($conn, $query_name, $sql);
            pg_execute($conn, $query_name, array(
                $mpts->measure_sid, $mpts->validity_start_date, $mpts->validity_end_date, $mpts->partial_temporary_stop_regulation_id,
                $mpts->partial_temporary_stop_regulation_officialjournal_number, $mpts->partial_temporary_stop_regulation_officialjournal_page,
                $mpts->abrogation_regulation_id, $mpts->abrogation_regulation_officialjournal_number, $mpts->abrogation_regulation_officialjournal_page,
                $operation, $operation_date
            ));
            $i += 1;
        }
    }
    /* END PARTIAL TEMPORARY STOP FUNCTIONS */


    /* BEGIN FOOTNOTE ASSOCIATION FUNCTIONS */
    public function get_footnote_association_measures()
    {
        global $conn;
        $this->footnote_association_measures = array();
        $sql = "select footnote_type_id, footnote_id from footnote_association_measures where measure_sid = $1 order by footnote_type_id, footnote_id";
        pg_prepare($conn, "get_footnote_association_measures", $sql);
        $result = pg_execute($conn, "get_footnote_association_measures", array($this->measure_sid));
        $row_count = pg_num_rows($result);
        if (($result) && ($row_count > 0)) {
            while ($row = pg_fetch_array($result)) {
                $footnote_association = new footnote_association_measure();
                $footnote_association->measure_sid = $this->measure_sid;
                $footnote_association->footnote_type_id = $row[0];
                $footnote_association->footnote_id = $row[1];
                array_push($this->footnote_association_measures, $footnote_association);
            }
        }
    }

    public function delete_footnote_association_measures()
    {
        global $conn;
        $application = new application;
        $operation = "D";
        $operation_date = $application->get_operation_date();
        $i = 0;
        foreach ($this->footnote_association_measures as $footnote_association_measure) {
            $sql = "INSERT INTO footnote_association_measures_oplog
 (measure_sid, footnote_type_id, footnote_id, operation, operation_date)
 VALUES (
 $1, $2, $3, $4, $5)";
            $query_name = "measure_footnote_association_delete" . $i;
            pg_prepare($conn, $query_name, $sql);
            pg_execute($conn, $query_name, array(
                $footnote_association_measure->measure_sid, $footnote_association_measure->footnote_type_id,
                $footnote_association_measure->footnote_id, $operation, $operation_date
            ));
            $i += 1;
        }
    }
    /* END FOOTNOTE ASSOCIATION FUNCTIONS */

    /* BEGIN EXCLUDED GEO AREAS FUNCTIONS */

    public function measure_excluded_geographical_area_insert($excluded_geographical_area, $geographical_area_sid)
    {
        global $conn;
        $application = new application;
        $operation = "C";
        $operation_date = $application->get_operation_date();
        $sql = "INSERT INTO measure_excluded_geographical_areas_oplog
 (measure_sid, excluded_geographical_area, geographical_area_sid, operation, operation_date)
 VALUES (
 $1, $2, $3, $4, $5)";
        pg_prepare($conn, "measure_excluded_geographical_area_insert", $sql);
        pg_execute($conn, "measure_excluded_geographical_area_insert", array(
            $this->measure_sid, $excluded_geographical_area,
            $geographical_area_sid, $operation, $operation_date
        ));
    }

    public function measure_excluded_geographical_area_delete($excluded_geographical_area)
    {
        global $conn;
        $application = new application;
        $geo = new geographical_area();
        $geo->geographical_area_id = $excluded_geographical_area;
        $geo->get_geographical_area_sid();
        $operation = "D";
        $operation_date = $application->get_operation_date();
        $sql = "INSERT INTO measure_excluded_geographical_areas_oplog
 (measure_sid, excluded_geographical_area, geographical_area_sid, operation, operation_date)
 VALUES ($1, $2, $3, $4, $5)";
        pg_prepare($conn, "measure_excluded_geographical_area_delete", $sql);
        pg_execute($conn, "measure_excluded_geographical_area_delete", array(
            $this->measure_sid, $excluded_geographical_area,
            $geo->geographical_area_sid, $operation, $operation_date
        ));
    }


    public function get_measure_excluded_geographical_areas()
    {
        global $conn;
        $this->measure_excluded_geographical_areas = array();
        $sql = "select excluded_geographical_area, geographical_area_sid from measure_excluded_geographical_areas where measure_sid = $1 order by excluded_geographical_area";
        pg_prepare($conn, "get_measure_excluded_geographical_areas", $sql);
        $result = pg_execute($conn, "get_measure_excluded_geographical_areas", array($this->measure_sid));
        $row_count = pg_num_rows($result);
        if (($result) && ($row_count > 0)) {
            while ($row = pg_fetch_array($result)) {
                $measure_excluded_geographical_area = new measure_excluded_geographical_area();
                $measure_excluded_geographical_area->measure_sid = $this->measure_sid;
                $measure_excluded_geographical_area->excluded_geographical_area = $row[0];
                $measure_excluded_geographical_area->geographical_area_sid = $row[1];
                array_push($this->measure_excluded_geographical_areas, $measure_excluded_geographical_area);
            }
        }
    }

    public function delete_measure_excluded_geographical_areas()
    {
        global $conn;
        $application = new application;
        $operation = "D";
        $operation_date = $application->get_operation_date();
        $i = 0;
        foreach ($this->measure_excluded_geographical_areas as $measure_excluded_geographical_area) {
            $sql = "INSERT INTO measure_excluded_geographical_areas_oplog
            (measure_sid, excluded_geographical_area, geographical_area_sid, operation, operation_date)
            VALUES (
            $1, $2, $3, $4, $5)";
            $query_name = "measure_excluded_geographical_area_delete" . $i;
            pg_prepare($conn, $query_name, $sql);
            pg_execute($conn, $query_name, array(
                $measure_excluded_geographical_area->measure_sid, $measure_excluded_geographical_area->excluded_geographical_area,
                $measure_excluded_geographical_area->geographical_area_sid, $operation, $operation_date
            ));
            $i += 1;
        }
    }
    /* END EXCLUDED GEO AREAS FUNCTIONS */


    public function get_footnote_string()
    {
        $s = "";
        $footnote_count = count($this->footnote_list);
        for ($j = 0; $j < $footnote_count; $j++) {
            $f = $this->footnote_list[$j];
            $s .= $f->footnote_type_id . $f->footnote_id . ", ";
        }
        $s = trim($s);
        $s = trim($s, ",");
        $this->footnote_string = $s;
    }

    public function get_mega_string()
    {
        $s = "";
        $mega_count = count($this->mega_list);
        for ($j = 0; $j < $mega_count; $j++) {
            $mega = $this->mega_list[$j];
            $s .= $mega->excluded_geographical_area . ", ";
        }
        $s = trim($s);
        $s = trim($s, ",");
        $this->mega_string = $s;
    }

    function populate_from_db()
    {
        global $conn;
        $sql = "select activity_name, activity_name_complete,  core_data_complete, 
        commodity_data_complete, duty_data_complete, condition_data_complete, footnote_data_complete
        from measure_activities where measure_activity_sid = $1";
        $query_name = "get_measure_activity_" . $this->measure_sid;
        pg_prepare($conn, $query_name, $sql);
        $result = pg_execute($conn, $query_name, array($this->measure_activity_sid));
        $row_count = pg_num_rows($result);
        if (($result) && ($row_count > 0)) {
            $row = pg_fetch_row($result);
            $this->activity_name = $row[0];
            $this->activity_name_complete = $row[1];
            $this->core_data_complete = $row[2];
            $this->commodity_data_complete = $row[3];
            $this->duty_data_complete = $row[4];
            $this->condition_data_complete = $row[5];
            $this->footnote_data_complete = $row[6];
        }
    }

    function populate_from_cookies()
    {
        $this->heading = "Create new measure";
        $this->measure_sid = get_cookie("measure_sid");
        $this->validity_start_date_day = get_cookie("measure_type_validity_start_date_day");
        $this->validity_start_date_month = get_cookie("measure_type_validity_start_date_month");
        $this->validity_start_date_year = get_cookie("measure_type_validity_start_date_year");
        $this->validity_end_date_day = get_cookie("measure_type_validity_end_date_day");
        $this->validity_end_date_month = get_cookie("measure_type_validity_end_date_month");
        $this->validity_end_date_year = get_cookie("measure_type_validity_end_date_year");

        $this->measure_generating_regulation_id = get_cookie("measure_generating_regulation_id");
        $this->measure_type_id = get_cookie("measure_type_id");
        $this->goods_nomenclature_item_id = get_cookie("goods_nomenclature_item_id");
        $this->additional_code_type_id = get_cookie("additional_code_type_id");
        $this->additional_code_id = get_cookie("additional_code_id");
        $this->geographical_area_id = get_cookie("geographical_area_id");
        $this->ordernumber = get_cookie("ordernumber");
    }

    public function get_siv_specific()
    {
        $s = 0;
        if (count($this->siv_component_list) > 0) {
            $s = floatval($this->siv_component_list[0]->duty_amount);
        }
        $this->combined_duty = "<span class='entry_price'>Entry Price</span> " . number_format($s, 2) . "%";
    }

    public function combine_duties()
    {
        $this->combined_duty = "";
        $this->measure_list = array();
        $this->measure_type_list = array();
        $this->additional_code_list = array();

        foreach ($this->duty_list as $d) {
            $d->geographical_area_id = $this->geographical_area_id;
            array_push($this->measure_type_list, $d->measure_type_id);
            array_push($this->measure_list, $d->measure_sid);
            array_push($this->additional_code_list, $d->additional_code_id);
            //p($d->perceived_value . " : " . $d->goods_nomenclature_item_id);
            // $d->perceived_value = floatval($d->perceived_value);
            $d->perceived_value = str_replace(",", "", $d->perceived_value);
            //p($d->perceived_value . " : " . $d->goods_nomenclature_item_id);
            if (($d->perceived_value != "") && ($d->perceived_value != Null)) {
                $this->perceived_value += $d->perceived_value;
            }
        }

        $measure_type_list_unique = set($this->measure_type_list);
        $measure_list_unique = set($this->measure_list);
        $additional_code_list_unique = set($this->additional_code_list);

        $this->measure_count = count($measure_list_unique);
        $this->measure_type_count = count($measure_type_list_unique);
        $this->additional_code_count = count($additional_code_list_unique);

        if (($this->measure_count == 1) && ($this->measure_type_count == 1) && ($this->additional_code_count == 1)) {
            foreach ($this->duty_list as $d) {
                $this->combined_duty .= $d->duty_string . " ";
            }
        } else {
            if ($this->measure_type_count > 1) {
                if (in_array("105", $measure_type_list_unique)) {
                    foreach ($this->duty_list as $d) {
                        if ($d->measure_type_id == "105") {
                            $this->combined_duty .= $d->duty_string . " ";
                        }
                    }
                }
            } elseif ($this->additional_code_count > 1) {
                if (in_array("500", $additional_code_list_unique)) {
                    foreach ($this->duty_list as $d) {
                        if ($d->additional_code_id == "500") {
                            $this->combined_duty .= $d->duty_string . " ";
                        }
                    }
                }
                if (in_array("500", $additional_code_list_unique)) {
                    foreach ($this->duty_list as $d) {
                        if ($d->additional_code_id == "500") {
                            $this->combined_duty .= $d->duty_string . " ";
                        }
                    }
                }
            }
        }

        $this->combined_duty = str_replace(" ", " ", $this->combined_duty);
        $this->combined_duty = trim($this->combined_duty);

        # Now add in the Meursing components
        $ad = strpos($this->combined_duty, "AC");
        $sd = strpos($this->combined_duty, "SD");
        $fd = strpos($this->combined_duty, "FD");
    }

    public function add_condition()
    {
        global $conn;

        $this->get_sid();
        $condition_code = get_formvar("condition_code");
        $action_code = get_formvar("action_code");
        $reference_price = get_formvar("reference_price");
        $applicable_duty = get_formvar("applicable_duty");
        $applicable_duty_permutation = get_formvar("applicable_duty_permutation");
        $condition_monetary_unit_code = get_formvar("condition_monetary_unit_code");
        $condition_measurement_unit_code = get_formvar("condition_measurement_unit_code");
        $condition_measurement_unit_qualifier_code = get_formvar("condition_measurement_unit_qualifier_code");
        $certificate = new certificate();
        $certificate->parse(get_formvar("certificate"));
        $action_code = get_formvar("action_code");

        // Before doing the insert, get the last component sequence number of its kind
        $sql = "select component_sequence_number from measure_activity_conditions where measure_activity_sid = $1 and condition_code = $2 order by 1 desc limit 1";
        pg_prepare($conn, "get_component_sequence_number", $sql);
        $result = pg_execute($conn, "get_component_sequence_number", array($this->measure_activity_sid, $condition_code));
        $row_count = pg_num_rows($result);
        if (($result) && ($row_count > 0)) {
            $row = pg_fetch_row($result);
            $component_sequence_number = $row[0] + 1;
        } else {
            $component_sequence_number = 1;
        }

        $sql = "insert into measure_activity_conditions
        (condition_code, reference_price, action_code,
        certificate_type_code, certificate_code, component_sequence_number,
        applicable_duty, applicable_duty_permutation, measure_activity_sid)
        VALUES
        ($1, $2, $3, $4, $5, $6, $7, $8, $9)";
        $stmt = "add_condition";
        pg_prepare($conn, $stmt, $sql);
        pg_execute($conn, $stmt, array(
            $condition_code,
            $reference_price,
            $action_code,
            $certificate->certificate_type_code,
            $certificate->certificate_code,
            $component_sequence_number,
            $applicable_duty,
            $applicable_duty_permutation,
            $this->measure_activity_sid
        ));
        //die();
        $url = "/measures/create_edit_conditions.html?mode=insert";
        header("Location: " . $url);
    }


    public function delete_condition()
    {
        global $conn;
        $measure_activity_condition_sid = get_querystring("measure_activity_condition_sid");
        $sql = "delete from measure_activity_conditions where measure_activity_condition_sid = $1;";
        $stmt = "delete_condition";
        pg_prepare($conn, $stmt, $sql);
        pg_execute($conn, $stmt, array($measure_activity_condition_sid));
        $url = "/measures/create_edit_conditions.html?mode=insert";
        header("Location: " . $url);
    }

    public function promote_condition()
    {
        global $conn;

        $this->get_sid();
        $measure_activity_condition_sid = get_querystring("measure_activity_condition_sid");
        $sql = "select component_sequence_number, condition_code from measure_activity_conditions where measure_activity_condition_sid = $1";
        pg_prepare($conn, "promote_01", $sql);
        $result = pg_execute($conn, "promote_01", array($measure_activity_condition_sid));
        $row_count = pg_num_rows($result);
        if (($result) && ($row_count > 0)) {
            $row = pg_fetch_row($result);
            $component_sequence_number = $row[0];
            $condition_code = $row[1];

            // Promote the current one
            $sql = "update measure_activity_conditions set component_sequence_number = $1 where measure_activity_condition_sid = $2";
            $stmt = "promote_02";
            pg_prepare($conn, $stmt, $sql);
            pg_execute($conn, $stmt, array($component_sequence_number - 1, $measure_activity_condition_sid));


            // Then demote the one above
            $sql = "update measure_activity_conditions
            set component_sequence_number = $1
            where condition_code = $2
            and component_sequence_number = $3
            and measure_activity_sid = $4
            and measure_activity_condition_sid != $5";
            $stmt = "promote_03";
            pg_prepare($conn, $stmt, $sql);
            pg_execute($conn, $stmt, array($component_sequence_number, $condition_code, $component_sequence_number - 1, $this->measure_activity_sid, $measure_activity_condition_sid));
        }
        $url = "/measures/create_edit_conditions.html?mode=insert";
        header("Location: " . $url);
    }


    public function demote_condition()
    {
        global $conn;

        $this->get_sid();
        $measure_activity_condition_sid = get_querystring("measure_activity_condition_sid");
        $sql = "select component_sequence_number, condition_code from measure_activity_conditions where measure_activity_condition_sid = $1";
        pg_prepare($conn, "demote_01", $sql);
        $result = pg_execute($conn, "demote_01", array($measure_activity_condition_sid));
        $row_count = pg_num_rows($result);
        if (($result) && ($row_count > 0)) {
            $row = pg_fetch_row($result);
            $component_sequence_number = $row[0];
            $condition_code = $row[1];

            // demote the current one
            $sql = "update measure_activity_conditions set component_sequence_number = $1 where measure_activity_condition_sid = $2";
            $stmt = "demote_02";
            pg_prepare($conn, $stmt, $sql);
            pg_execute($conn, $stmt, array($component_sequence_number + 1, $measure_activity_condition_sid));


            // Then demote the one above
            $sql = "update measure_activity_conditions
            set component_sequence_number = $1
            where condition_code = $2
            and component_sequence_number = $3
            and measure_activity_sid = $4
            and measure_activity_condition_sid != $5";
            $stmt = "demote_03";
            pg_prepare($conn, $stmt, $sql);
            pg_execute($conn, $stmt, array($component_sequence_number, $condition_code, $component_sequence_number + 1, $this->measure_activity_sid, $measure_activity_condition_sid));
        }
        $url = "/measures/create_edit_conditions.html?mode=insert";
        header("Location: " . $url);
    }

    public function add_footnote()
    {
        global $conn;
        $this->get_sid();
        $footnote_id = get_formvar("measure_footnote_id");
        $footnote_id = get_before_hyphen($footnote_id);

        if (strlen($footnote_id) == 5) {
            $footnote_type_id = substr($footnote_id, 0, 2);
            $footnote_id = substr($footnote_id, 2, 3);
            $sql = "insert into measure_activity_footnotes (measure_activity_sid, footnote_type_id, footnote_id) values ($1, $2, $3)";
            $stmt = "add_footnote";
            pg_prepare($conn, $stmt, $sql);
            pg_execute($conn, $stmt, array(
                $this->measure_activity_sid,
                $footnote_type_id,
                $footnote_id
            ));
        }

        $url = "/measures/create_edit_footnotes.html";
        header("Location: " . $url);
    }

    public function delete_footnote()
    {
        global $conn;
        $this->get_sid();
        $footnote_id = get_querystring("footnote_id");
        $footnote_type_id = get_querystring("footnote_type_id");

        if ((strlen($footnote_id) == 3) && (strlen($footnote_type_id) == 2)) {
            $sql = "delete from measure_activity_footnotes
            where measure_activity_sid = $1
            and footnote_type_id = $2
            and footnote_id = $3";
            $stmt = "delete_footnote";
            pg_prepare($conn, $stmt, $sql);
            pg_execute($conn, $stmt, array(
                $this->measure_activity_sid,
                $footnote_type_id,
                $footnote_id
            ));
        }
        $url = "/measures/create_edit_footnotes.html";
        header("Location: " . $url);
    }

    public function get_activity_options()
    {
        global $conn;
        $sql = "select measure_activity_option_id, description from measure_activity_options
        where active is true order by sort_index";
        pg_prepare($conn, "get_activity_options", $sql);
        $result = pg_execute($conn, "get_activity_options", array());
        $row_count = pg_num_rows($result);
        if (($result) && ($row_count > 0)) {
            while ($row = pg_fetch_array($result)) {
                $obj = new simple_object($row["measure_activity_option_id"], $row["description"]);
                array_push($this->activity_options, $obj);
            }
        }
    }

    public function execute_activity_option()
    {
        $this->activity_name = get_formvar("activity_name");;
        $this->edit_activity_option = get_formvar("edit_activity_option");
        $this->persist_activity_name($this->edit_activity_option);

        switch ($this->edit_activity_option) {
            case "Measures - Change generating regulation":
                // Change generating regulation
                $url = "./measure_change_regulation.html";
                break;
            case "Measures - Change measure type":
                // Change measure type
                $url = "./measure_change_measure_type.html";
                break;
            case "Measures - Change validity period":
                // Change validity period
                $url = "./measure_change_validity_period.html";
                break;
            case "Measures - Change geography":
                // Change geography
                $url = "./measure_change_geography.html";
                break;
            case "vChange duties":
                // Change duties
                $url = "./measure_change_duties.html";
                break;
            case "Measures - Change conditions":
                // Change conditions
                $url = "./measure_change_conditions.html";
                break;
            case "Measures - Change footnotes":
                // Change footnote
                $url = "./measure_change_footnotes.html";
                break;
            case "Measures - Delete or end-date":
                // Delete or end-date
                $url = "./measure_terminate.html";
                break;
            case "Measures - Change commodity codes":
                // Change commodity codes
                break;
            case "Measures - Change additional codes":
                // Change additional code
                break;
            case "Measures - Change multiple fields":
                // Change multiple fields
                $url = "./measure_change_multiple.html";
                break;
        }
        header("Location: " . $url);
    }

    public function get_measure_sids()
    {
        $this->measure_sid_list = get_formvar("measure_sid");
        if (!is_array($this->measure_sid_list)) {
            $this->measure_sid_list = array();
            $temp = get_querystring("measure_sid");
            if ($temp != "") {
                array_push($this->measure_sid_list, $temp);
                $this->activity_name = $this->get_default_activity_name();
                $this->measure_count = 1;
            } else {
                prend("No measures selected");
            }
        } else {
            $this->measure_count = count($this->measure_sid_list);
        }
    }

    public function get_default_activity_name()
    {
        global $conn;
        if (count($this->measure_sid_list) == 1) {
            $sql = "select geographical_area_id, goods_nomenclature_item_id, measure_type_id
            from measures where measure_sid = $1";
            pg_prepare($conn, "get_default_activity_name", $sql);
            $result = pg_execute($conn, "get_default_activity_name", array(
                $this->measure_sid_list[0]
            ));
            $row_count = pg_num_rows($result);
            if (($result) && ($row_count > 0)) {
                $row = pg_fetch_row($result);
                $this->geographical_area_id = $row[0];
                $this->goods_nomenclature_item_id = $row[1];
                $this->measure_type_id = $row[2];
            }
            return ("Edit measure " . $this->measure_sid_list[0] . " on commodity code " . $this->goods_nomenclature_item_id . " of type " . $this->measure_type_id . " on geography " . $this->geographical_area_id);
        }
    }

    public function lookup_measure_type_id()
    {
        //h1 ($this->measure_type_id);
        $this->measure_component_applicable_code = null;
        global $conn;
        $sql = "select measure_component_applicable_code from measure_types
        where measure_type_id = $1";
        $query_name = "get_measure_type" . $this->measure_type_id;
        pg_prepare($conn, $query_name, $sql);
        $result = pg_execute($conn, $query_name, array($this->measure_type_id));
        $row_count = pg_num_rows($result);
        if (($result) && ($row_count > 0)) {
            $row = pg_fetch_row($result);
            $this->measure_component_applicable_code = $row[0];
        }

        if ($this->measure_component_applicable_code < 2) {
            $this->show_duties_form = true;
        } else {
            $this->show_duties_form = false;
        }

        //die();
    }

    function get_full_summary()
    {
        global $conn;
        $sql = "select activity_name, ma.validity_start_date, ma.validity_end_date, measure_generating_regulation_id, 
        ma.measure_type_id, geographical_area_id, geographical_area_sid, information_text,
        mtd.description as measure_type_description, ma.quota_order_number_id
        from measure_activities ma, base_regulations br, measure_type_descriptions mtd
        where ma.measure_generating_regulation_id = br.base_regulation_id
        and ma.measure_type_id = mtd.measure_type_id
        and measure_activity_sid = $1";
        $query_name = "stmt1_" . $this->measure_activity_sid;
        pg_prepare($conn, $query_name, $sql);
        $result = pg_execute($conn, $query_name, array($this->measure_activity_sid));
        $row_count = pg_num_rows($result);
        if (($result) && ($row_count > 0)) {
            $row = pg_fetch_row($result);

            $this->activity_name = $row[0];
            $this->validity_start_date = $row[1];
            $this->validity_end_date = $row[2];
            $this->measure_generating_regulation_id = $row[3];
            $this->measure_type_id = $row[4];
            $this->geographical_area_id = $row[5];
            $this->geographical_area_sid = $row[6];
            $this->regulation_information_text = $row[7];
            $this->measure_type_description = $row[8];
            $this->quota_order_number_id = na($row[9]);
        }

        // Get commodities
        $sql = "select * from measure_activity_commodities where measure_activity_sid = $1 order by goods_nomenclature_item_id";
        $query_name = "stmt2_" . $this->measure_activity_sid;
        pg_prepare($conn, $query_name, $sql);
        $result = pg_execute($conn, $query_name, array($this->measure_activity_sid));
        $row_count = pg_num_rows($result);
        $this->commodity_code_list = array();
        $this->commodity_codes = "";
        if (($result) && ($row_count > 0)) {
            while ($row = pg_fetch_array($result)) {
                $gn = new goods_nomenclature;
                $gn->goods_nomenclature_item_id = $row['goods_nomenclature_item_id'];
                array_push($this->commodity_code_list, $gn);
                $this->commodity_codes .= $gn->goods_nomenclature_item_id . "\n";
            }
        }
        $this->commodity_codes = rtrim($this->commodity_codes);

        // Get additional codes
        $sql = "select additional_code_type_id || additional_code as additional_code from measure_activity_additional_codes
        where measure_activity_sid = $1 order by additional_code_type_id, additional_code";
        $query_name = "stmt3_" . $this->measure_activity_sid;
        pg_prepare($conn, $query_name, $sql);
        $result = pg_execute($conn, $query_name, array(
            $this->measure_activity_sid
        ));

        $row_count = pg_num_rows($result);
        $this->additional_code_list = array();
        $this->additional_codes = "";
        if (($result) && ($row_count > 0)) {
            while ($row = pg_fetch_array($result)) {
                $ac = new additional_code;
                $ac->code = $row['additional_code'];
                array_push($this->additional_code_list, $ac);
                $this->additional_codes .= $ac->code . "\n";
            }
        }
        $this->additional_codes = rtrim($this->additional_codes);

        // Get conditions
        $sql = "select mac.condition_code, component_sequence_number, mac.action_code, certificate_type_code, certificate_code,
        applicable_duty, applicable_duty_permutation, reference_price, mcc.condition_code_type,
        mad.abbreviation as action_abbreviation, ma.requires_duty
        from measure_activity_conditions mac, measure_condition_codes mcc, measure_action_descriptions mad, measure_actions ma
        where mac.condition_code = mcc.condition_code
        and mac.action_code = mad.action_code
        and mac.action_code = ma.action_code
        and measure_activity_sid = $1
        order by mac.condition_code, component_sequence_number;";
        //pre ($sql);
        $query_name = "stmt4_" . $this->measure_activity_sid;
        pg_prepare($conn, $query_name, $sql);
        $result = pg_execute($conn, $query_name, array(
            $this->measure_activity_sid
        ));

        $row_count = pg_num_rows($result);
        $this->measure_conditions = array();
        if (($result) && ($row_count > 0)) {
            while ($row = pg_fetch_array($result)) {
                $mc = new measure_condition;
                $mc->condition_code = $row['condition_code'];
                $mc->component_sequence_number = $row['component_sequence_number'];
                $mc->action_code = $row['action_code'];
                $mc->certificate_type_code = $row['certificate_type_code'];
                $mc->certificate_code = $row['certificate_code'];
                $mc->applicable_duty = $row['applicable_duty'];
                $mc->applicable_duty_permutation = $row['applicable_duty_permutation'];
                $mc->reference_price = $row['reference_price'];
                $mc->condition_code_type = $row['condition_code_type'];
                $mc->action_abbreviation = $row['action_abbreviation'];
                $requires_duty = $row['requires_duty'];
                if ($requires_duty == "t") {
                    $mc->requires_duty = true;
                } else {
                    $mc->requires_duty = false;
                }

                array_push($this->measure_conditions, $mc);
            }
        }

        // Get duties
        //h1 ($this->measure_activity_sid);
    }

    function cancel () {
        $url = "./delete_confirmation.html";
        header("Location: " . $url);
    }

    function cancel_confirm() {
        global $conn;

        $this->get_sid();

        // Delete the measure activity duties
        $sql = "delete from measure_activity_duties where measure_activity_sid = $1";
        $stmt = "delete_measure_activity_" . uniqid();
        pg_prepare($conn, $stmt, $sql);
        pg_execute($conn, $stmt, array($this->measure_activity_sid));

        // Delete the measure activity conditions
        $sql = "delete from measure_activity_conditions where measure_activity_sid = $1";
        $stmt = "delete_measure_activity_conditions_" . uniqid();
        pg_prepare($conn, $stmt, $sql);
        pg_execute($conn, $stmt, array($this->measure_activity_sid));

        // Delete the measure activity footnotes
        $sql = "delete from measure_activity_footnotes where measure_activity_sid = $1";
        $stmt = "delete_measure_activity_footnotees_" . uniqid();
        pg_prepare($conn, $stmt, $sql);
        pg_execute($conn, $stmt, array($this->measure_activity_sid));

        // Delete the measure activity commodities
        $sql = "delete from measure_activity_commodities where measure_activity_sid = $1";
        $stmt = "delete_measure_activity_commodities_" . uniqid();
        pg_prepare($conn, $stmt, $sql);
        pg_execute($conn, $stmt, array($this->measure_activity_sid));

        // Delete the measure activity additional codes
        $sql = "delete from measure_activity_additional_codes where measure_activity_sid = $1";
        $stmt = "delete_measure_activity_additional_codes_" . uniqid();
        pg_prepare($conn, $stmt, $sql);
        pg_execute($conn, $stmt, array($this->measure_activity_sid));

        // Delete the measure activity
        $sql = "delete from measure_activities where measure_activity_sid = $1";
        $stmt = "delete_measure_activity_" . uniqid();
        pg_prepare($conn, $stmt, $sql);
        pg_execute($conn, $stmt, array($this->measure_activity_sid));

        $this->measure_activity_sid = null;
        $_SESSION["measure_activity_sid"] = null;
        unset ($_SESSION["measure_activity_sid"]);
        $url = "./delete_confirmation2.html";
        
        header("Location: " . $url);
    }
}
