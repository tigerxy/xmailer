<form class="form-horizontal" method="post" action="<?=$view->action('submit')?>">
<fieldset>

<!-- Form Name -->
<legend>IMAP</legend>

<!-- Text input-->
<div class="form-group">
  <label class="col-md-4 control-label" for="SMTP Host">IMAP Host</label>  
  <div class="col-md-4">
  <input id="host" name="host" type="text" placeholder="Host" class="form-control input-md" required="required" value="<?=$imap['host']?>" />
  </div>
</div>

<!-- Text input-->
<div class="form-group">
  <label class="col-md-4 control-label" for="textinput">IMAP User</label>  
  <div class="col-md-4">
  <input id="user" name="user" type="text" placeholder="User" class="form-control input-md" required="required" value="<?=$imap['user']?>" />
    
  </div>
</div>

<!-- Password input-->
<div class="form-group">
  <label class="col-md-4 control-label" for="passwordinput">IMAP Password</label>
  <div class="col-md-4">
    <input id="password" name="password" type="password" placeholder="Password" class="form-control input-md" value="" />
  <span class="help-block"><?=t('Leave blank to keep current password.')?></span>  
    
  </div>
</div>

<!-- Select Basic -->
<div class="form-group">
  <label class="col-md-4 control-label" for="selectbasic">SSL</label>
  <div class="col-md-4">
    <select id="ssl" name="ssl" class="form-control" value="<?=$imap['ssl']?>" />
      <option></option>
      <option>SSL</option>
      <option>TLS</option>
    </select>
  </div>
</div>

<!-- Text input-->
<div class="form-group">
  <label class="col-md-4 control-label" for="textinput">Port</label>  
  <div class="col-md-4">
  <input id="port" name="port" type="number" placeholder="143" class="form-control input-md" value="<?=$imap['port']?>" />
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
      <input type="checkbox" name="spam" id="spam" value="1" <?php if($config['spam']) echo 'checked = "checked"'; ?> />
      <?=t('Forward only Emails form registred Users')?>
    </label>
	</div>
  <div class="checkbox">
    <label for="checkboxes-1">
      <input type="checkbox" name="replyto" id="replyto" value="1" <?php if($config['replyto']) echo 'checked = "checked"'; ?> />
      <?=t('Add from email adress as reply-to')?>
    </label>
	</div>
  </div>
</div>

<!-- Textarea -->
<div class="form-group">
  <label class="col-md-4 control-label" for="textarea"><?=t('Allow this Adresses')?></label>
  <div class="col-md-4">                     
    <textarea class="form-control" id="allow" name="allow"><?php if ($config['allow'] != null) echo implode(", ", $config['allow']); ?></textarea>
    <span class="help-block"><?=t('(Seperate multiple emails with a comma)')?></span>  
  </div>
</div>

</fieldset>
<div class="ccm-dashboard-form-actions-wrapper">
    <div class="ccm-dashboard-form-actions">
        <button class="pull-right btn btn-success" type="submit" ><?=t('Save')?></button>
    </div>
    </div>
</form>