<?php
// use Xmailer\Config as XConf;
// $config =  new XConf();
// $smtp = $config->smtp;
// $imap = $config->imap;
?>

<script>
var ssl_options = <?= $config->allSslOptionsToJson() ?>;

function updatePort(e) {
    var section = e.id.split("_")[0];
    var o = ssl_options[section].filter(o => o.id == e.value)[0];
    document.getElementById(section + "_port").placeholder = o.port;
}
</script>
<form class="form-horizontal" method="post" action="<?=$view->action('submit')?>">
    <?php foreach (array('imap'=>$config->imap,'smtp'=>$config->smtp) as $name => $conf) { ?>
    <fieldset>
        <!-- Form Name -->
        <legend><?=strtoupper($name)?></legend>

        <!-- Text input-->
        <div class="form-group">
            <label class="col-md-4 control-label" for="SMTP Host"><?=strtoupper($name)?> Host</label>
            <div class="col-md-4">
                <input id="<?=$name?>_host" name="<?=$name?>_host" type="text" placeholder="Host" class="form-control input-md"
                    required="required" value="<?=$conf->getHost()?>" />
            </div>
        </div>

        <!-- Text input-->
        <div class="form-group">
            <label class="col-md-4 control-label" for="textinput"><?=strtoupper($name)?> User</label>
            <div class="col-md-4">
                <input id="<?=$name?>_user" name="<?=$name?>_user" type="text" placeholder="User" class="form-control input-md"
                    required="required" value="<?=$conf->getUser()?>" />

            </div>
        </div>

        <!-- Password input-->
        <div class="form-group">
            <label class="col-md-4 control-label" for="passwordinput"><?=strtoupper($name)?> Password</label>
            <div class="col-md-4">
                <input id="<?=$name?>_password" name="<?=$name?>_password" type="password" placeholder="Password"
                    class="form-control input-md" value="" />
                <span class="help-block"><?=t('Leave blank to keep current password.')?></span>

            </div>
        </div>

        <!-- Select Basic -->
        <div class="form-group">
            <label class="col-md-4 control-label" for="selectbasic"><?=strtoupper($name)?> SSL</label>
            <div class="col-md-4">
                <select id="<?=$name?>_ssl" name="<?=$name?>_ssl" class="form-control"
                    onchange="updatePort(this)" autocomplete="off" />
                <?php foreach ($conf->ssl_options as $opt) { ?>
                <option value="<?=$opt->getId()?>"<?=$opt->isSelected()?' selected':''?>>
                    <?=$opt->getDescription()?>
                </option>
                <?php } ?>
                </select>
            </div>
        </div>

        <!-- Text input-->
        <div class="form-group">
            <label class="col-md-4 control-label" for="textinput"><?=strtoupper($name)?> Port</label>
            <div class="col-md-4">
                <input id="<?=$name?>_port" name="<?=$name?>_port" type="number"
                    placeholder="<?= $conf->ssl_options->selected()->getPort() ?>" class="form-control input-md"
                    value="<?=$conf->getPort() > 0 ? $conf->getPort() : ''?>" />
                <span class="help-block"><?=t('Port (Leave blank for default)')?></span>
            </div>
        </div>

    </fieldset>
    <?php } ?>
    <fieldset>

        <!-- Form Name -->
        <legend>Spam</legend>
        <!-- Multiple Checkboxes -->
        <div class="form-group">
            <label class="col-md-4 control-label" for="checkboxes"><?=t('Spam defence')?></label>
            <div class="col-md-4">
                <div class="checkbox">
                    <label for="checkboxes-0">
                        <input type="checkbox" name="spam" id="spam" value="1"
                            <?php if($config->getSpam()) echo 'checked = "checked"'; ?> />
                        <?=t('Forward only Emails form registred Users')?>
                    </label>
                </div>
                <div class="checkbox">
                    <label for="checkboxes-1">
                        <input type="checkbox" name="replyto" id="replyto" value="1"
                            <?php if($config->getReplyTo()) echo 'checked = "checked"'; ?> />
                        <?=t('Add from email adress as reply-to')?>
                    </label>
                </div>
            </div>
        </div>

        <!-- Textarea -->
        <div class="form-group">
            <label class="col-md-4 control-label" for="textarea"><?=t('Allow this Adresses')?></label>
            <div class="col-md-4">
                <textarea class="form-control" id="allow"
                    name="allow"><?php if ($config->getAllow() != null) echo implode(", ", $config->getAllow()); ?></textarea>
                <span class="help-block"><?=t('(Seperate multiple emails with a comma)')?></span>
            </div>
        </div>

    </fieldset>
    <fieldset>

        <!-- Form Name -->
        <legend>More Settings</legend>
        <!-- Multiple Checkboxes -->
        <div class="form-group">
            <label class="col-md-4 control-label" for="checkboxes">Verhalten</label>
            <div class="col-md-4">
                <div class="checkbox">
                    <label for="checkboxes-0">
                        <input type="checkbox" name="addpagename" id="addpagename" value="1"
                            <?=$config->getAddPageName() ? ' checked' : ''?> />
                        Seitennamen zu Betreff hinzufügen
                    </label>
                </div>
            </div>
        </div>

    </fieldset>
    <div class="ccm-dashboard-form-actions-wrapper">
        <div class="ccm-dashboard-form-actions">
            <button class="pull-right btn btn-success" type="submit"><?=t('Save')?></button>
        </div>
    </div>
</form>