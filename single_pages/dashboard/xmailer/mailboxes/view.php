<h1><?=t('Users')?></h1>
<table border="0" cellspacing="0" cellpadding="0" class="ccm-search-results-table">
    <thead>
        <tr>
            <th class="false"><?=t('User')?></th>
            <th class="false"><?=t('Email')?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($users as $user) { ?>
        <tr>
            <td><a href="<?=URL::to('/dashboard/xmailer/mailboxes', 'user', $user["bID"])?>"><?=$user["uName"]?></a></td>
            <td><a href="<?=URL::to('/dashboard/xmailer/mailboxes', 'user', $user["bID"])?>"><?=$user["email"]?></a></td>
        <tr>
        <?php } ?>
    </tbody>
</table>

<h1><?=t('Groups')?></h1>
<table border="0" cellspacing="0" cellpadding="0" class="ccm-search-results-table">
    <thead>
        <tr>
            <th class="false"><?=t('Group')?></th>
            <th class="false"><?=t('Email')?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($groups as $group) { ?>
        <tr>
            <td><a href="<?=URL::to('/dashboard/xmailer/mailboxes', 'group', $group["bID"])?>"><?=$group["gName"]?></a></td>
            <td><a href="<?=URL::to('/dashboard/xmailer/mailboxes', 'group', $group["bID"])?>"><?=$group["email"]?></a></td>
        <tr>
        <?php } ?>
    </tbody>
</table>