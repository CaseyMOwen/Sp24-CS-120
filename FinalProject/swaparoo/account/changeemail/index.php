<?php session_start(); include '../../functions.php'; include '/cs120/FinalProject/swaparoo/account/changeemail.php';shared_header('Change Email')?>
<?php $pdo = connect_mysql(); date_default_timezone_set('America/New_York'); ?>
<?php require_login('/cs120/FinalProject/swaparoo/account/changeemail/'); ?>

<div class = "changeemail">
    <h1>Change Email</h1>
    <form action="changeemail.php" method="post" class="changeform">
        <input type="text" name="newemail" placeholder="New Email" id="newemail" required>
        <input type="text" name="retypeemail" placeholder="Retype New Email" id="retype" required>
        <input type="submit" value="Submit" class="formsubmit">
    </form>
</div>

<?php shared_footer() ?>