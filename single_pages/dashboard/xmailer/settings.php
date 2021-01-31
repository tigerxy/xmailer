<script>
var ssl_options = <?= json_encode($ssl_options) ?>;

function updatePort(e) {
    var section = e.id.split("_")[0];
    var port = ssl_options[section][e.value]["port"];
    document.getElementById(section + "_port").placeholder = port;
    console.log(section, port);
}
</script>
<form class="form-horizontal" method="post" action="<?=$view->action('submit')?>">
    <fieldset>

        <!-- Form Name -->
        <legend>IMAP</legend>

        <!-- Text input-->
        <div class="form-group">
            <label class="col-md-4 control-label" for="SMTP Host">IMAP Host</label>
            <div class="col-md-4">
                <input id="imap_host" name="imap_host" type="text" placeholder="Host" class="form-control input-md"
                    required="required" value="<?=$imap['host']?>" />
            </div>
        </div>

        <!-- Text input-->
        <div class="form-group">
            <label class="col-md-4 control-label" for="textinput">IMAP User</label>
            <div class="col-md-4">
                <input id="imap_user" name="imap_user" type="text" placeholder="User" class="form-control input-md"
                    required="required" value="<?=$imap['user']?>" />

            </div>
        </div>

        <!-- Password input-->
        <div class="form-group">
            <label class="col-md-4 control-label" for="passwordinput">IMAP Password</label>
            <div class="col-md-4">
                <input id="imap_password" name="imap_password" type="password" placeholder="Password"
                    class="form-control input-md" value="" />
                <span class="help-block"><?=t('Leave blank to keep current password.')?></span>

            </div>
        </div>

        <!-- Select Basic -->
        <div class="form-group">
            <label class="col-md-4 control-label" for="selectbasic">SSL</label>
            <div class="col-md-4">
                <select id="imap_ssl" name="imap_ssl" class="form-control" value="<?=$imap['ssl']?>"
                    onchange="updatePort(this)" />
                <?php foreach ($ssl_options['imap'] as $key => $value) { ?>
                <option value="<?=$key?>" <?=$key==$imap['ssl']?' selected':''?>>
                    <?=$value['desc']?>
                </option>
                <?php } ?>
                </select>
            </div>
        </div>

        <!-- Text input-->
        <div class="form-group">
            <label class="col-md-4 control-label" for="textinput">Port</label>
            <div class="col-md-4">
                <input id="imap_port" name="imap_port" type="number"
                    placeholder="<?= $ssl_options['imap'][$imap['ssl']]['port'] ?>" class="form-control input-md"
                    value="<?=$imap['port']?>" />
                <span class="help-block"><?=t('Port (Leave blank for default)')?></span>
            </div>
        </div>

    </fieldset>
    <fieldset>

        <!-- Form Name -->
        <legend>SMTP</legend>

        <!-- Text input-->
        <div class="form-group">
            <label class="col-md-4 control-label" for="SMTP Host">SMTP Host</label>
            <div class="col-md-4">
                <input id="smtp_host" name="smtp_host" type="text" placeholder="Host" class="form-control input-md"
                    required="required" value="<?=$smtp['host']?>" />
            </div>
        </div>

        <!-- Text input-->
        <div class="form-group">
            <label class="col-md-4 control-label" for="textinput">SMTP User</label>
            <div class="col-md-4">
                <input id="smtp_user" name="smtp_user" type="text" placeholder="User" class="form-control input-md"
                    required="required" value="<?=$smtp['user']?>" />

            </div>
        </div>

        <!-- Password input-->
        <div class="form-group">
            <label class="col-md-4 control-label" for="passwordinput">SMTP Password</label>
            <div class="col-md-4">
                <input id="smtp_password" name="smtp_password" type="password" placeholder="Password"
                    class="form-control input-md" value="" />
                <span class="help-block"><?=t('Leave blank to keep current password.')?></span>

            </div>
        </div>

        <!-- Select Basic -->
        <div class="form-group">
            <label class="col-md-4 control-label" for="selectbasic">Transport Option</label>
            <div class="col-md-4">
                <select id="smtp_ssl" name="smtp_ssl" class="form-control" value="<?=$smtp['ssl']?>"
                    onchange="updatePort(this)" />
                <?php foreach ($ssl_options['smtp'] as $key => $value) { ?>
                <option value="<?=$key?>"<?=$key==$smtp['ssl']?' selected':''?>>
                    <?=$value['desc']?>
                </option>
                <?php } ?>
                </select>
                <span class="help-block"><?=t('Please select TLS or SSL')?></span>
            </div>
        </div>

        <!-- Text input-->
        <div class="form-group">
            <label class="col-md-4 control-label" for="textinput">Port</label>
            <div class="col-md-4">
                <input id="smtp_port" name="smtp_port" type="number" placeholder="<?= $ssl_options['smtp'][$smtp['ssl']]['port'] ?>"
                    class="form-control input-md" value="<?=$smtp['port']?>" />
                <span class="help-block"><?=t('Port (Leave blank for default)')?></span>
            </div>
        </div>

    </fieldset>
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
                            <?php if($config['spam']) echo 'checked = "checked"'; ?> />
                        <?=t('Forward only Emails form registred Users')?>
                    </label>
                </div>
                <div class="checkbox">
                    <label for="checkboxes-1">
                        <input type="checkbox" name="replyto" id="replyto" value="1"
                            <?php if($config['replyto']) echo 'checked = "checked"'; ?> />
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
                    name="allow"><?php if ($config['allow'] != null) echo implode(", ", $config['allow']); ?></textarea>
                <span class="help-block"><?=t('(Seperate multiple emails with a comma)')?></span>
            </div>
        </div>

    </fieldset>
    <div class="ccm-dashboard-form-actions-wrapper">
        <div class="ccm-dashboard-form-actions">
            <button class="pull-right btn btn-success" type="submit"><?=t('Save')?></button>
        </div>
    </div>
</form>