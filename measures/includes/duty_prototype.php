<div class="govuk-grid-row">
    <div class="govuk-grid-column-full">

        <!--PQ Grid files-->
        <link rel="stylesheet" href="/css/pqgrid.min.css" />
        <script src="/grid-2.4.1/pqgrid.min.js"></script>
        <!--PQ Grid Office theme-->
        <link rel="stylesheet" href="/css/themes/govuk/pqgrid.css" />
        <script>
            $(function() {
                var data = [{
                        commodity_code: "0123456789",
                        additional_code: "C233",
                        duty_001: "4 EUR",
                        duty_002: "4 EUR",
                        duty_003: "4 EUR"
                    },
                    {
                        commodity_code: "4829475839",
                        additional_code: "C234",
                        duty_001: "14 EUR",
                        duty_002: "4 EUR",
                        duty_003: "4 EUR"
                    },
                    {
                        commodity_code: "2834649594",
                        additional_code: "C237",
                        duty_001: "12%",
                        duty_002: "4 EUR",
                        duty_003: "4 EUR"
                    },
                    {
                        commodity_code: "8362837497",
                        additional_code: "B133",
                        duty_001: "4 EUR",
                        duty_002: "4 EUR",
                        duty_003: "4 EUR"
                    },
                    {
                        commodity_code: "2909878278",
                        additional_code: "C233",
                        duty_001: "4 EUR",
                        duty_002: "4 EUR",
                        duty_003: "4 EUR"
                    },
                    {
                        commodity_code: "7657658765",
                        additional_code: "C233",
                        duty_001: "4 EUR",
                        duty_002: "4 EUR",
                        duty_003: "4 EUR"
                    },
                    {
                        commodity_code: "0987654321",
                        additional_code: "C233",
                        duty_001: "10%",
                        duty_002: "4 EUR",
                        duty_003: "4 EUR"
                    }
                ]

                var obj = {
                    minWidth: 500,
                    height: 400,
                    resizable: true,
                    scrollModel: {
                        autoFit: true
                    },
                    showTop: false,
                    showBottom: false,
                    numberCell: {
                        show: true
                    },
                    title: "",
                    collapsible: {
                        on: true,
                        collapsed: false
                    },
                };
                obj.colModel = [{
                        title: "Commodity",
                        dataType: "string",
                        dataIndx: "commodity_code",
                        width: "15%",
                        editable: false
                    },
                    {
                        title: "Additional code",
                        dataType: "string",
                        dataIndx: "additional_code",
                        width: "16",
                        editable: false
                    },
                    {
                        title: "F1 0.35 EUR WAT",
                        dataType: "string",
                        dataIndx: "duty_001",
                        width: "23%"
                    },
                    {
                        title: "F2 0.248 EUR WAT",
                        dataType: "string",
                        dataIndx: "duty_002",
                        width: "23%"
                    },
                    {
                        title: "F3 0 EUR WAT",
                        dataType: "string",
                        dataIndx: "duty_003",
                        width: "23%"
                    }
                ]
                
                obj.dataModel = {
                    data: data
                };

                var $grid = $("#grid_json").pqGrid(obj);
                //$( "#grid_json" ).pqGrid( "option", "selectionModel", {type: 'row', mode: 'block'} );
                //$( "#grid_json" ).pqGrid( "option", "selectionModel", {type: 'cell', mode: 'range'} );

                //bind width to select list.
                $("#sl_width").change(function(evt) {
                    var val = $(this).val();
                    $grid.pqGrid('option', 'width', val)
                        .pqGrid('refresh');
                });
                $("#sl_margin").change(function(evt) {
                    var val = $(this).val();
                    $grid.css('margin', val).pqGrid('refresh');
                });
            });
        </script>

        <?php
        global $measure_activity;

        foreach ($measure_activity->additional_code_list as $additional_code) {
            if ($additional_code->additional_code == "999") {
        ?>
                <script>
                    $(document).ready(function() {
                        $("#residual_additional_code").val("<?= $additional_code->code ?>");
                    });
                </script>
            <?php
            }
        }

        if ($measure_activity->measure_component_applicable_code == 2) {
            new inset_control(
                $text = "You have selected a measure type which cannot have duties associated with it. Click on the 'Continue ...' button below to enter measure conditions."
            );
        } else {
            new inset_control(
                $text = "Use this form to enter the duties that will apply to the measures for the previously selected commodity codes.<br /><br /><span class='inset_1'>Workbasket:</span><span class='highlighted_text inset_2'>{workbasket_name}</span><span class='inset_1'>Activity:</span><span class='highlighted_text inset_2'>{activity_name}</span>"
            );
            ?>
            <div id="grid_json" style="margin:20px auto;"></div>
            <?php
            if (1 > 2) {

                $code_count = 0;
                if (count($measure_activity->additional_code_list) == 0) {
                    $ac = new additional_code();
                    $ac->code = "Dummy";
                    array_push($measure_activity->additional_code_list, $ac);
                }
                foreach ($measure_activity->additional_code_list as $additional_code) {
                    $row_index = 0;
                    $code_count++;
            ?>
                    <table class="govuk-table govuk-table--m sticky" id="duty_table_<?= $additional_code->code ?>">
                        <!--<caption class="govuk-table__caption--m">Applicable duties</caption>//-->
                        <thead class="govuk-table__head">
                            <?php
                            if ($additional_code->code != "Dummy") {
                            ?>
                                <tr class="govuk-table__row">
                                    <th colspan="4" class="additional_code_head govuk-table__cell">Additional code <?= $code_count ?> : <?= $additional_code->code ?> - <?= $additional_code->description ?></th>
                                </tr>
                            <?php
                            }
                            ?>

                            <tr class="govuk-table__row">
                                <th scope="col" class="govuk-table__header" style="width:5%">#</th>
                                <th scope="col" class="govuk-table__header" style="width:15%" nowrap>Commodity code</th>
                                <th scope="col" class="govuk-table__header" style="width:60%">Duty</th>
                                <th scope="col" class="govuk-table__header" style="width:25%">&nbsp;</th>
                            </tr>
                        </thead>
                        <tbody class="govuk-table__body">
                            <?php
                            $row_count = count($measure_activity->commodity_code_list);
                            foreach ($measure_activity->commodity_code_list as $commodity_code) {
                                $row_index++;
                                if (($measure_activity->duties_same_for_all_commodities) && ($row_index > 1)) {
                            ?>
                                    <tr class="govuk-table__row">
                                        <td class="govuk-table__cell vertical_align_middle"><?= $row_index ?></td>
                                        <td class="govuk-table__cell vertical_align_middle">
                                            <?= format_goods_nomenclature_item_id($commodity_code->goods_nomenclature_item_id) ?>
                                        </td>
                                        <td class="govuk-table__cell vertical_align_middle">
                                            As above
                                        </td>
                                    </tr>
                                <?php
                                } else {
                                ?>
                                    <tr class="govuk-table__row">
                                        <td class="govuk-table__cell vertical_align_middle"><?= $row_index ?></td>
                                        <td class="govuk-table__cell vertical_align_middle">
                                            <?= format_goods_nomenclature_item_id($commodity_code->goods_nomenclature_item_id) ?>
                                        </td>
                                        <td class="govuk-table__cell vertical_align_middle">
                                            <input class="govuk-input govuk-input--width-40 duty" id="duty_<?= $commodity_code->goods_nomenclature_item_id ?>" name="duty_<?= $commodity_code->goods_nomenclature_item_id ?>" type="text">
                                        </td>
                                        <?php
                                        if ((!$measure_activity->duties_same_for_all_commodities) && ($row_index == 1) && ($row_count > 1)) {
                                        ?>
                                            <td class="govuk-table__cell vertical_align_middle">
                                                <a class="govuk-link copy_to_all_rows" href="#">Copy to all rows</a>
                                            </td>
                                        <?php
                                        } else {
                                        ?>
                                            <td class="govuk-table__cell vertical_align_middle">&nbsp;</td>
                                        <?php
                                        }
                                        ?>
                                    </tr>
                            <?php
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                    <!-- End table //-->
        <?php
                }
            }
        }
        ?>
    </div>
</div>