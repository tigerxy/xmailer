<form class="form-horizontal" method="post" action="<?=$view->action('submit')?>">
    <fieldset>
        <legend><?=t('User')?></legend>
        <div class="form-group">
            <label class="col-sm-2 control-label"><?=t('User')?></label>
            <div class="col-sm-10">
                <select name="uID" size="10" class="form-control">
                    <?php foreach($allUsers as $auser) { ?>
                    <option<?php if ($auser["uID"] == $user["uID"]) echo " selected";?> value="<?=$auser["uID"]?>">
                        <?=$auser["uName"]?>
                    </option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label"><?=t('Email')?></label>
            <div class="col-sm-10">
                <input type="email" name="email" class="form-control" value="<?=$user["email"]?>" placeholder="<?=t('Email')?>"/>
            </div>
        </div>
    </fieldset>
    <input type="hidden" name="bID"value="<?=$user["bID"]?>"/>
<div class="ccm-dashboard-form-actions-wrapper">
    <div class="ccm-dashboard-form-actions">
        <button class="pull-right btn btn-success" type="submit" ><?=t('Save')?></button>
    </div>
    </div>
</form>