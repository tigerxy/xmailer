<script>
var settings = <?= json_encode($settings) ?>;
var sslOptions = <?= json_encode($sslOptions) ?>;
var userAttributes = <?= json_encode($userAttributes) ?>;
var groups = <?= json_encode($groups) ?>;
console.log(groups);
</script>
<form class="form-horizontal" method="post" action="<?= $view->action('submit') ?>">
    <div id="settings-form"></div>
    <div class="ccm-dashboard-form-actions-wrapper">
        <div class="ccm-dashboard-form-actions">
            <button class="pull-right btn btn-success" type="submit"><?= t('Save') ?></button>
        </div>
    </div>
</form>