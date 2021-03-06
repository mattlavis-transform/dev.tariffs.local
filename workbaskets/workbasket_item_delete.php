<?php
require(dirname(__FILE__) . "../../includes/db.php");
$application = new application;
$error_handler = new error_handler;
$application->init("workbaskets");
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
        $workbasket = new workbasket();
        $workbasket->workbasket_id = get_querystring("workbasket_id");
        $workbasket_item_sid = get_querystring("workbasket_item_sid");
        $workbasket->populate();
        //$workbasket = $application->session->get_workbasket_for_withdrawal($workbasket_id);
        //prend ($application->session);
        //h1 ($workbasket_item_sid);
        ?>
        <!-- Start breadcrumbs //-->
        <div class="govuk-breadcrumbs">
            <ol class="govuk-breadcrumbs__list">
            <li class="govuk-breadcrumbs__list-item">
                    <a class="govuk-breadcrumbs__link" href="/">Home</a>
                </li>
                <li class="govuk-breadcrumbs__list-item">
                    <a class="govuk-breadcrumbs__link" href="/#workbaskets">Workbaskets</a>
                </li>
                <li class="govuk-breadcrumbs__list-item" aria-current="page">Delete workbasket activity</li>
            </ol>
        </div>
        <!-- End breadcrumbs //-->
        <main class="govuk-main-wrapper" id="main-content" role="main">
            <div class="govuk-grid-row">
                <div class="govuk-grid-column-three-quarters">
                    <!-- Start main title //-->
                    <h1 class="govuk-heading-xl">Delete workbasket activity</h1>
                    <!-- End main title //-->


                    <form action="actions.php" method="get">

                        <?php
                        //h1 ($workbasket_item_sid);
                        new warning_control(
                            $text = "<span class='highlighted_text'>You have opted to delete the activity <strong>" . $workbasket->get_workbasket_item($workbasket_item_sid) . "</strong>. By selecting 'Yes' below, you will delete the content of this workbasket activity. This action cannot be undone.</span>",
                        );

                        new radio_control(
                            $label = "Are you sure you want to delete this workbasket activity?",
                            $label_style = "govuk-fieldset__legend--m",
                            $hint_text = "",
                            $control_name = "confirm_delete",
                            $dataset = $application->get_yes_no(),
                            $selected = null,
                            $radio_control_style = "stacked",
                            $required = true,
                            $disabled_on_edit = false
                        );

                        ?>
                        <?php
                        new hidden_control("workbasket_item_sid", $workbasket_item_sid);
                        new hidden_control("action", "delete_workbasket_item");
                        $btn = new button_control("Continue", "continue", "primary");
                        //$btn = new button_control("Cancel", "cancel", "text", "", "/");
                        ?>
                    </form>
                </div>
            </div>
        </main>

    </div>
    <?php
    require("../includes/footer.php");
    ?>
</body>

</html>