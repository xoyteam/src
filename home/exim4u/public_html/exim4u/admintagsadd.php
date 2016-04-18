<?php
include_once dirname(__FILE__) . '/config/variables.php';
include_once dirname(__FILE__) . '/config/authpostmaster.php';
include_once dirname(__FILE__) . '/config/functions.php';
include_once dirname(__FILE__) . '/config/httpheaders.php';


if (isset($_GET['tag_id'])&& isset($_GET['tag_name'])){
  unset($_GET['tagnotfound']);
  $query = "SELECT t.tag_id, t.tag_name FROM tags t
    WHERE t.domain_id=:domain_id AND t.tag_id=:tag_id";
  $sth = $dbh->prepare($query);
  $sth->execute(array(':domain_id'=>$_SESSION['domain_id'],':tag_id'=>$_GET['tag_id']));
  if ($sth->rowCount() == 1){
    $row=$sth->fetch();
    //print ('<p>'.$row['tag_name'].','.$_GET['tag_name'].','.$row['tag_id'].','.$_GET['tag_id'].'</p>');
    //print ('<p>'.strcmp($row['tag_name'],$_GET['tag_name']).','.strcmp($row['tag_id'],$_GET['tag_id']).'</p>');
    if ((0 != strcmp($row['tag_name'],$_GET['tag_name'])) || (0 != strcmp($row['tag_id'],$_GET['tag_id']))) {
      header ("Location: admintagsadd.php?tagnotfound=true");
      die();
    }
  }
  else {
    header ("Location: admintagsadd.php?tagnotfound=true");
    die();
  }
}
else {
  $row = array('tag_name'=>'','tag_id'=>'');
}



ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

?>
<html>
<head>
<title><?php echo _('Exim4U') . ': ' . _('Manage Tags'); ?></title>
<link rel="stylesheet" href="style.css" type="text/css">
</head>
<body>
<?php include dirname(__FILE__) . '/config/header.php'; ?>
<div id="Menu">
  <a href="admintags.php"><?php echo _('List Tags'); ?></a><br>
  <a href="admin.php"><?php echo _('Main Menu'); ?></a><br>
  <br><a href="logout.php"><?php echo _('Logout'); ?></a><br>
</div>
<div id="Content">

  <div id="forms">
    <form name="admintagsadd" method="post" action="admintagssubmit.php">
      <table align="center">
        <?php if(isset($_GET['tagnotfound'])) print '<p>Tag not found! Make new tag!</p>'; ?>
        <tr>
          <td><?php echo _('Tag Name'); ?>:</td>
          <td colspan="2">
            <input type="hidden" name="tag_id" value="<?php echo $row['tag_id']; ?>">
            <input type="textfield" size="25" name="tag_name" class="textfield" value="<?php echo $row['tag_name']; ?>">
          </td>
        </tr>
        <tr>
          <td colspan="3" class="button">
            <input name="submit" type="submit" value="<?php echo _('Submit'); ?>">
          </td>
        </tr>
      </table>
    </form>

  </div>
  </body>
  </html>
  <!-- Layout and CSS tricks obtained from http://www.bluerobot.com/web/layouts/ -->
