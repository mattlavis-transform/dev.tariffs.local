<?php
require(dirname(__FILE__) . "../../includes/db.php");
//prend ($_REQUEST);
$application = new application;
$application->init("measures_summary");
$error_handler = new error_handler();
$measure_activity = new measure_activity();
$measure_activity->get_sid();
$measure_activity->get_full_summary();
?>

<!DOCTYPE html>
<html lang="en" class="govuk-template">
<?php
require("../includes/metadata.php");
?>

<body class="govuk-template__body">
    <?php
    require("../includes/header.php");
    ?>
    <div class="govuk-width-container">
        <?php
        require("../includes/phase_banner.php");
        $control_content = array();
        new data_entry_form($control_content, $measure_activity, $left_nav = "", "measure_activity_actions.php");

        ?>

    </div>
    <?php
    require("../includes/footer.php");
    ?>

</body>

</html>