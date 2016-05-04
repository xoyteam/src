<?php
  include_once dirname(__FILE__) . '/config/variables.php';
  include_once dirname(__FILE__) . '/config/authpostmaster.php';
  include_once dirname(__FILE__) . '/config/functions.php';
  include_once dirname(__FILE__) . '/config/httpheaders.php';

# confirm that the postmaster is looking to delete a user they are permitted to change before going further  
$query = "SELECT * FROM users WHERE user_id=:user_id
	AND domain_id=:domain_id
	AND (type='local' OR type='piped')";
$sth = $dbh->prepare($query);
//print_r ($_SESSION);
//die;
$sth->execute(array(':user_id'=>$_SESSION['user_id'], ':domain_id'=>$_SESSION['domain_id']));
if (!$sth->rowCount()) {
  header ("Location: adminuser.php?faildeleted={$_SESSION['localpart']}");
  die();  
}
if(!isset($_POST['confirm'])) { $_POST['confirm'] = null; }
 
if ($_POST['confirm'] == '1') {
  $query = "DELETE FROM tags
    WHERE tag_id=:tag_id
    AND domain_id=:domain_id";
  $sth = $dbh->prepare($query);
  $success = $sth->execute(array(':tag_id'=>$_POST['tag_id'], ':domain_id'=>$_SESSION['domain_id']));
  if ($success) {
    header ("Location: admintags.php?deleted={$_POST['tag_id']}");
    die;
  } else {
    header ("Location: admintags.php?faildeleted={$_POST['tag_id']}");
    die;
  }
} else if ($_POST['confirm'] == "cancel") {                 
    header ("Location: admintags.php?faildeleted=canceled");
    die;                                                      
} else {
  $query = "SELECT * FROM tags WHERE tag_id=:tag_id";
  $sth = $dbh->prepare($query);
  $sth->execute(array(':tag_id'=>$_GET['tag_id']));
  if ($sth->rowCount()) { $row = $sth->fetch(); }
}
?>

<html>
  <head>
    <title><?php echo _('Exim4U') . ': ' . _('Confirm Delete'); ?></title>
    <link rel="stylesheet" href="style.css" type="text/css">
  </head>
  <body>
    <?php include dirname(__FILE__) . '/config/header.php'; ?>
    <div id="menu">
      <a href="admintags.php"><?php echo _('List Tags'); ?></a><br>
      <a href="admin.php"><?php echo _('Main Menu'); ?></a><br>
      <br><a href="logout.php"><?php echo _('Logout'); ?></a><br>
    </div>
    <div id="Content">
      <form name="tagdelete" method="post" action="admintagsdelete.php">
        <table align="center">
          <tr>
            <td colspan="2">
              <?php printf (_('Please confirm deleting tag "%s"'),
                $row['tag_name']);
              ?>:
            </td>
          </tr>
          <tr>
            <td>
              <input name="confirm" type="radio" value="cancel" checked>
              <b><?php printf (_('Do Not Delete tag "%s"'),
                $row['tag_name']);
              ?></b>
            </td>
          </tr>
          <tr>
            <td>
              <input name="confirm" type="radio" value="1">
              <b><?php printf (_('Delete tag "%s"'),
                $row['tag_name']);
              ?></b>
            </td>
          </tr>
          <tr>
            <td>
              <input name='domain' type='hidden'
                value='<?php echo $_SESSION['domain_id']; ?>'>
              <input name='tag_id' type='hidden'
                value='<?php echo $row['tag_id']; ?>'>
              <input name='submit' type='submit'
                value='<?php echo _('Continue'); ?>'>
            </td>
          </tr>
        </table>
      </form>
    </div>
  </body>
</html>

