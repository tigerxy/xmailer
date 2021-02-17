<script>
var settings = <?= json_encode($config) ?>;
var sslOptions = <?= json_encode($config->allSslOptionsToJson()) ?>;
</script>
<form class="form-horizontal" method="post" action="<?= $view->action('submit') ?>">
    <div id="settings-form"></div>
    <div class="ccm-dashboard-form-actions-wrapper">
        <div class="ccm-dashboard-form-actions">
            <button class="pull-right btn btn-success" type="submit"><?= t('Save') ?></button>
        </div>
    </div>
</form>