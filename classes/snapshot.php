<?php
class snapshot
{
	// Class properties and methods go here
    public $scope = "";
    public $range = "";
    public $format = "html";
    public $form_submitted = false;
    public $day_start = "";
    public $month_start = "";
    public $year_start = "";
    public $day_end = "";
    public $month_end = "";
    public $year_end = "";
    public $geographical_area_description = "";

    public function __construct() {
        $this->error_handler = new error_handler;
	}

	public function get_parameters() {
        if (!empty($_REQUEST)) {
            $this->form_submitted = true;
            $this->get_scope();
            $this->get_format();
            $this->get_range();
            $this->get_start_date();
            $this->get_end_date();
            if (($this->form_submitted == true) && (count($this->error_handler->error_list) == 0)) {
                $this->get_data();
                if ($this->format == "csv") {
                    $this->simple_write_csv();
                }
                elseif ($this->format == "json") {
                    $this->simple_write_json();
                }
            }
        }
    }

    private function simple_write_csv() {
        ob_start();
        echo "Commodity code,Suffix,Indent,End-line?,Description,Assigned,Measure type,Origin,Origin exclusions,Duties";
        echo ($this->delimiter);
        foreach ($this->commodities as $commodity) {
            if (count($commodity->measure_list) == 0) {
                echo ("'" . $commodity->goods_nomenclature_item_id . "',");
                echo ("'" . $commodity->productline_suffix . "',");
                echo ($commodity->number_indents . ",");
                echo ("'" . yn($commodity->leaf) . "',");
                echo ("'" . $commodity->format_description2() . "',");
                echo ("'" . yn($commodity->assigned) . "',");
                echo ("'',");
                echo ("'',");
                echo ("'',");
                echo ("''");
                echo ($this->delimiter);
            } else {
                foreach ($commodity->measure_list as $measure) {
                    echo ("'" . $commodity->goods_nomenclature_item_id . "',");
                    echo ("'" . $commodity->productline_suffix . "',");
                    echo ($commodity->number_indents . ",");
                    echo ("'" . yn($commodity->leaf) . "',");
                    echo ("'" . $commodity->format_description2() . "',");
                    echo ("'" . yn($commodity->assigned) . "',");
                    echo ("'" . $measure->measure_type_id . " " . $measure->measure_type_description . "',");
                    echo ("'" . $measure->geographical_area_id . "',");
                    echo ("'" . "" . "',");
                    //echo ("'" . "$measure->mega_string" . "',");
                    echo ("'" . $measure->combined_duty . "'");
                    echo ($this->delimiter);
                }
            }
            ob_flush();
            flush();
        }
        die();
    }

    private function simple_write_json() {
        ob_start();
        
        echo ("[" . $this->delimiter);
        $commodity_count = count($this->commodities);
        $commodity_index = 0;
        foreach ($this->commodities as $commodity) {
            $commodity_index += 1;
            if ($commodity->productline_suffix == "80") {
                if (count($commodity->measure_list) == 0) {
                    if ($commodity->combined_duty == "") {
                        $commodity->combined_duty = "n/a";
                    }
                    echo ('  {' . $this->delimiter);
                    echo ('    "commodityCode": "' . $commodity->node . '",' . $this->delimiter);
                    echo ('    "label": "' . $this->filter_for_json($commodity->description) . '",' . $this->delimiter);
                    echo ('    "Tariff": "' . $commodity->combined_duty . '"' . $this->delimiter);
                    if ($commodity_index == $commodity_count) {
                        echo ('  }' . $this->delimiter);
                    } else {
                        echo ('  },' . $this->delimiter);
                    }
                } else {
                    $measure_count = count($commodity->measure_list);
                    $measure_index = 0;
                    foreach ($commodity->measure_list as $measure) {
                        $measure_index += 1;
                        echo ('  {' . $this->delimiter);
                        echo ('    "commodityCode": "' . $commodity->node . '",' . $this->delimiter);
                        echo ('    "label": "' . $this->filter_for_json($commodity->description) . '",' . $this->delimiter);
                        echo ('    "Tariff": "' . $measure->combined_duty . '"' . $this->delimiter);
                        if (($commodity_index == $commodity_count) && ($measure_index == $measure_count)) {
                            echo ('  }' . $this->delimiter);
                        } else {
                            echo ('  },' . $this->delimiter);
                        }
                    }
                }
            }
            ob_flush();
            flush();
        }
        echo ("]");
        echo($this->end_string);
        die();
    }

    private function filter_for_json($s) {
        $s = str_replace('"', "'", $s);
        $s = str_replace('-', "-", $s);
        $s = str_replace('<br>', " ", $s);
        $s = str_replace('<p>', " ", $s);
        $s = str_replace('<p/>', " ", $s);
        $s = str_replace('•', " ", $s);
        $s = str_replace(chr(9), " ", $s);
        $s = str_replace(chr(10), " ", $s);
        $s = str_replace(chr(11), " ", $s);
        $s = str_replace(chr(13), " ", $s);
        $s = str_replace("\n", " ", $s);
        $s = str_replace("\r", " ", $s);
        $s = str_replace(array("\n", "\r"), '', $s);
        
        $s = str_replace("  ", " ", $s);
        $s = str_replace(PHP_EOL, '', $s);
        $s = trim(preg_replace('/\s\s+/', ' ', $s));
        //$s = preg_replace('/\s+/', '', $s); 
        $s = preg_replace('!\s+!', ' ', $s);
        $s = strtr($s,"\n\r","  ");
        $s = stripcslashes($s);
        $s = trim($s);
        return ($s);
    }

    private function get_start_date() {
        // Get snapshot day start
        $this->day_start = get_querystring("day_start") . "";
        $this->month_start = get_querystring("month_start") . "";
        $this->year_start = get_querystring("year_start") . "";
        $this->snapshot_date_start = to_date_string($this->day_start, $this->month_start, $this->year_start);
        $valid_date_start = checkdate($this->month_start, $this->day_start, $this->year_start);
        if ($valid_date_start != 1) {
            array_push($this->error_handler->error_list, "snapshot_date_start");
        }
	}

	private function get_end_date() {
        // Get snapshot day end
        $this->day_end = get_querystring("day_end") . "";
        $this->month_end = get_querystring("month_end") . "";
        $this->year_end = get_querystring("year_end") . "";
        if (($this->day_end != "") && ($this->month_end != "") && ($this->year_end != "")) {
            $this->snapshot_date_end = to_date_string($this->day_end, $this->month_end, $this->year_end);
            $valid_date_end = checkdate($this->month_end, $this->day_end, $this->year_end);
            if ($valid_date_end != 1) {
                array_push($this->error_handler->error_list, "snapshot_date_end");
            }
        } else {
            $this->snapshot_date_end = "";
        }
    }

	private function get_range() {
        // Get commodity range
        $this->range = get_querystring("range") . "";
        if ($this->range == "" ) {
            if ($this->format == "html") {
                array_push($this->error_handler->error_list, "commodity_range");
            } else {
                $this->range = "";
            }
        } else {
            $this->get_geographical_area_description();
        }
	}

	private function get_geographical_area_description() {
        global $conn;
        $sql = "select description from ml.ml_geographical_areas mga where geographical_area_id = $1;";
        pg_prepare($conn, "get_description", $sql);
        $result = pg_execute($conn, "get_description", array($this->scope));
        if ($result) {
            $row = pg_fetch_row($result);
            $this->geographical_area_description = $row[0];
        }
	}

	private function get_scope() {
	    // Get scope
        $this->scope = strtoupper(get_querystring("scope") . "");
        if ($this->scope == "" ) {
            if ($this->format == "html") {
                array_push($this->error_handler->error_list, "scope");
            }
        }
    }

	private function get_format() {
        // Write to CSV or not
        $fmt = get_querystring("fmt");
        if ($fmt == "csv") {
            $this->format = "csv";
        }
        elseif ($fmt == "json") {
            $this->format = "json";
        }
        else {
            $this->format = "html";
        }
        // If writing to CSV, do we want to actually write the CSV to screen instead
        if ($this->format != "html") {
            $this->write_to_screen = get_querystring("wts");
            if ($this->write_to_screen == false) {
                header("Content-type: text/" . $this->format);
                header("Content-Disposition: attachment; filename=" . $this->scope . "." . $this->format);
                header("Pragma: no-cache");
                header("Expires: 0");
                $this->delimiter = "\n";
                $this->start_string = "";
                $this->end_string = "";
            } else {
                $this->delimiter = "<br />";
                $this->start_string = "<pre>";
                $this->end_string = "</pre>";
            }
            echo ($this->start_string);
        }
    }

    public function get_data() {
        global $conn;

        $range_len = strlen($this->range);
        if ($range_len != 0) {
            $range_clause = " and left(m.goods_nomenclature_item_id, " . $range_len . ") = '" . $this->range . "' ";
        } else {
            $range_clause = "";
        }

        // Get the measure condition components (for Entry Price)
        if (($this->scope == "mfn") || ($this->scope == "1011")) {
            $sql = "select m.measure_sid, mc.measure_condition_sid, mcc.duty_expression_id, mcc.duty_amount,
            mcc.monetary_unit_code, mcc.measurement_unit_code, mcc.measurement_unit_qualifier_code
            from ml.measures_real_end_dates m, measure_conditions mc, measure_condition_components mcc
            where m.measure_sid = mc.measure_sid
            and mc.measure_condition_sid = mcc.measure_condition_sid
            and m.validity_start_date <= '" . $this->snapshot_date_end . "'
            and (m.validity_end_date is null or m.validity_end_date >= '" . $this->snapshot_date_start . "')
            and mc.component_sequence_number = 1
            and mcc.duty_expression_id = '01'
            and m.measure_type_id in ('103', '105')" . $range_clause .
            " order by m.measure_sid, mcc.duty_expression_id, mcc.duty_amount";
        } else {
            $sql = "select m.measure_sid, mc.measure_condition_sid, mcc.duty_expression_id, mcc.duty_amount,
            mcc.monetary_unit_code, mcc.measurement_unit_code, mcc.measurement_unit_qualifier_code
            from ml.measures_real_end_dates m, measure_conditions mc, measure_condition_components mcc
            where m.measure_sid = mc.measure_sid
            and mc.measure_condition_sid = mcc.measure_condition_sid
            and m.validity_start_date <= '" . $this->snapshot_date_end . "'
            and (m.validity_end_date is null or m.validity_end_date >= '" . $this->snapshot_date_start . "')
            and mc.component_sequence_number = 1
            and mcc.duty_expression_id = '01'
            and m.measure_type_id in ('142', '145')" . $range_clause .
            "and geographical_area_id = '" . $this->scope . "' " .
            " order by m.measure_sid, mcc.duty_expression_id, mcc.duty_amount";
        }
        $result = pg_query($conn, $sql);
        $measure_condition_components = array();
        if ($result) {
            while ($row = pg_fetch_array($result)) {
                $mcc = new measurement_unit_qualifier();
                $mcc->measure_sid = $row['measure_sid'];
                $mcc->duty_expression_id = $row['duty_expression_id'];
                $mcc->duty_amount = $row['duty_amount'];
                $mcc->monetary_unit_code = $row['monetary_unit_code'];
                $mcc->measurement_unit_code = $row['measurement_unit_code'];
                $mcc->measurement_unit_qualifier_code = $row['measurement_unit_qualifier_code'];
                array_push($measure_condition_components, $mcc);
            }
        }
        //prend ($sql);

        // Get the duties / measure components
        if (($this->scope == "mfn") || ($this->scope == "1011")) {
            $sql = "select m.measure_sid, m.measure_type_id, m.goods_nomenclature_item_id, mc.duty_expression_id,
            mc.duty_amount, mc.monetary_unit_code, mc.measurement_unit_code, mc.measurement_unit_qualifier_code,
            m.validity_start_date, m.validity_end_date, mtd.description as measure_type_description
            from measure_type_descriptions mtd, ml.measures_real_end_dates m
            left outer join measure_components mc
            on m.measure_sid = mc.measure_sid
            where m.measure_type_id = mtd.measure_type_id
            and m.validity_start_date <= '" . $this->snapshot_date_end . "'
            and (m.validity_end_date is null or m.validity_end_date >= '" . $this->snapshot_date_start . "')
            and m.measure_type_id in ('103', '105') " . $range_clause .
            " order by m.goods_nomenclature_item_id, m.validity_start_date, mc.duty_expression_id;";
        } else {
            $sql = "select m.measure_sid, m.measure_type_id, m.goods_nomenclature_item_id, mc.duty_expression_id,
            mc.duty_amount, mc.monetary_unit_code, mc.measurement_unit_code, mc.measurement_unit_qualifier_code,
            m.validity_start_date, m.validity_end_date, mtd.description as measure_type_description
            from measure_type_descriptions mtd, ml.measures_real_end_dates m
            left outer join measure_components mc
            on m.measure_sid = mc.measure_sid
            where m.measure_type_id = mtd.measure_type_id
            and m.validity_start_date <= '" . $this->snapshot_date_end . "' and (m.validity_end_date is null or m.validity_end_date >= '" . $this->snapshot_date_start . "')
            and m.measure_type_id in ('142', '145') " . $range_clause .
            "and geographical_area_id = '" . $this->scope . "'
            order by m.goods_nomenclature_item_id, m.validity_start_date, mc.duty_expression_id
            ";
        }

        //prend ($sql);

        $result = pg_query($conn, $sql);
        $duties = array();
        if ($result) {
            while ($row = pg_fetch_array($result)) {
                $duty = new duty();
                $duty->measure_condition_components     = array();
                $duty->measure_sid                      = $row['measure_sid'];
                $duty->goods_nomenclature_item_id       = $row['goods_nomenclature_item_id'];
                $duty->measure_type_id                  = $row['measure_type_id'];
                $duty->duty_expression_id               = $row['duty_expression_id'];
                $duty->duty_amount                      = $row['duty_amount'];
                $duty->monetary_unit_code               = $row['monetary_unit_code'];
                $duty->measurement_unit_code            = $row['measurement_unit_code'];
                $duty->measurement_unit_qualifier_code  = $row['measurement_unit_qualifier_code'];
                $duty->measure_type_description         = $row['measure_type_description'];
                $duty->validity_start_date              = $row['validity_start_date'];
                $duty->validity_end_date                = $row['validity_end_date'];

                if ($duty->duty_expression_id == Null) {
                    $duty->entry_price_applies              = true;
                    foreach ($measure_condition_components as $mcc) {
                        if ($mcc->measure_sid == $duty->measure_sid) {
                            array_push($duty->measure_condition_components, $mcc);
                        }
                    }
                    $duty->duty_string = "";
                    foreach ($duty->measure_condition_components as $mcc) {
                        $duty->duty_string .= number_format($mcc->duty_amount, 3) . "%";
                        $duty->entry_price_string = "Y";
                    }


                    //$duty->duty_string = "Entry price";
                } else {
                    $duty->get_duty_string();
                }
                array_push($duties, $duty);
            }
        }
        // Form the duties into measure objects
        $measures = array();
        foreach ($duties as $duty) {
            $measure_sid = $duty->measure_sid;
            $matched = false;
            foreach ($measures as $measure) {
                $measure_sid2 = $measure->measure_sid;
                if ($measure_sid == $measure_sid2) {
                    array_push($measure->duty_list, $duty);
                    $matched = true;
                    break;
                }
            }
            if ($matched == false) {
                $measure = new measure();
                $measure->measure_sid               = $duty->measure_sid;
                $measure->goods_nomenclature_item_id = $duty->goods_nomenclature_item_id;
                $measure->measure_type_id           = $duty->measure_type_id;
                $measure->measure_type_description  = $duty->measure_type_description;
                $measure->validity_start_date       = $duty->validity_start_date;
                $measure->validity_end_date         = $duty->validity_end_date;
                $measure->entry_price_string        = $duty->entry_price_string;
                array_push($measure->duty_list, $duty);
                array_push($measures, $measure);
            }
        }

        foreach ($measures as $measure) {
            $measure->combine_duties();
        }

        // Next - SQL to get the commodity codes
        $sql = "select goods_nomenclature_item_id, producline_suffix, number_indents,
        description, leaf, significant_digits, validity_start_date, validity_end_date, node
        from ml.goods_nomenclature_export_new ('" . $this->range . "%', '" . $this->snapshot_date_start . "')
        order by goods_nomenclature_item_id, producline_suffix;";

        //prend ($sql);

        $result = pg_query($conn, $sql);
        $this->commodities = array();
        if ($result) {
            while ($row = pg_fetch_array($result)) {
                $commodity	= new goods_nomenclature;
                $commodity->goods_nomenclature_item_id  = $row['goods_nomenclature_item_id'];
                $commodity->productline_suffix          = $row['producline_suffix'];
                $commodity->number_indents              = $row['number_indents'];
                $commodity->description                 = $row['description'];
                $commodity->leaf                        = yn($row['leaf']);
                $commodity->significant_digits          = $row['significant_digits'];
                $commodity->validity_start_date         = $row['validity_start_date'];
                $commodity->validity_end_date           = $row['validity_end_date'];
                $commodity->node                        = $row['node'];
                if ($commodity->significant_digits != 2) {
                    $commodity->number_indents += 1;
                }
                array_push($this->commodities, $commodity);
            }
        }

        // Finally, assign the measures to the commodities
        foreach ($measures as $measure) {
            foreach ($this->commodities as $commodity) {
                if ($measure->goods_nomenclature_item_id == $commodity->goods_nomenclature_item_id) {
                    if ($commodity->productline_suffix == "80") {
                        array_push($commodity->measure_list, $measure);
                        $commodity->measure_sid = $measure->measure_sid;
                        $commodity->assigned = true;
                        break;
                    }
                }
            }
        }

        // Now inherit down where appropriate
        // Now, inheritance needs to take account of dates
        $commodity_count = count($this->commodities);
        for ($i = 0; $i < $commodity_count; $i++) {
            $commodity = $this->commodities[$i];
            if ($commodity->assigned == true) {
                for ($j = ($i + 1); $j < $commodity_count; $j++) {
                    $commodity2 = $this->commodities[$j];
                    if ($commodity2->number_indents > $commodity->number_indents) {
                        if ($commodity2->assigned == false) {
                            if ($commodity2->productline_suffix == "80") {
                                $commodity2->measure_list = $commodity->measure_list;
                                foreach ($commodity2->measure_list as $measure) {
                                    $measure->goods_nomenclature_item_id = $commodity2->goods_nomenclature_item_id;
                                }
                            }
                        }
                    } else {
                        break;
                    }
                }
            }
        }
    }
}