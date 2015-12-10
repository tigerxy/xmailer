<form class="form-horizontal" method="post" action="<?=$view->action('submit')?>">
    <fieldset>
        <legend><?=t('Group')?></legend>
        <div class="form-group">
            <label class="col-sm-2 control-label"><?=t('Group')?></label>
            <div class="col-sm-10">
                <select name="gID" size="10" class="form-control">
                    <?php foreach($allGroups as $agroup) { ?>
                    <option<?php if ($agroup["gID"] == $group["gID"]) echo " selected";?> value="<?=$agroup["gID"]?>">
                        <?=$agroup["gName"]?>
                    </option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label"><?=t('Email')?></label>
            <div class="col-sm-10">
                <input type="email" name="email" class="form-control" value="<?=$group["email"]?>" placeholder="<?=t('Email')?>"/>
            </div>
        </div>
    </fieldset>
    <input type="hidden" name="bID" value="<?=$group["bID"]?>"/>
<div class="ccm-dashboard-form-actions-wrapper">
    <div class="ccm-dashboard-form-actions">
        <button class="pull-right btn btn-success" type="submit" ><?=t('Save')?></button>
    </div>
    </div>
</form>