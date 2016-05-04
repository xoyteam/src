<?php
include_once dirname(__FILE__) . '/config/variables.php';
include_once dirname(__FILE__) . '/config/authpostmaster.php';
include_once dirname(__FILE__) . '/config/functions.php';
include_once dirname(__FILE__) . '/config/httpheaders.php';

$query = "SELECT * FROM users WHERE user_id=:user_id
  AND domain_id=:domain_id
  AND (type='local' OR type='piped')";
$sth = $dbh->prepare($query);
$sth->execute(array(':user_id'=>$_GET['user_id'],
  ':domain_id'=>$_SESSION['domain_id']));
if ($sth->rowCount()) { $row = $sth->fetch(); }

$username = $row['username'];
$realname = $row['realname'];
$domquery = "SELECT spamassassin,quotas,pipe FROM domains
  WHERE domain_id=:domain_id";
$domsth = $dbh->prepare($domquery);
$domsth->execute(array(':domain_id'=>$_SESSION['domain_id']));
if ($domsth->rowCount()) {
  $domrow = $domsth->fetch();
}

$query = "SELECT * FROM domains WHERE domain_id=:domain_id";
$sth = $dbh->prepare($query);
$sth->execute(array(':domain_id'=>$_SESSION['domain_id']));
$row = $sth->fetch();
?>
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <title><?php echo _('Exim4U') . ': ' . _('Manage Users'); ?></title>
    <link rel="stylesheet" href="style.css" type="text/css">
  </head>
  <body>
    <?php include dirname(__FILE__) . '/config/header.php'; ?>
    <div id="menu">
      <a href="adminuser.php"><?php echo _('Manage Accounts'); ?></a><br >
      <a href="admin.php"><?php echo _('Main Menu'); ?></a><br>
      <br><a href="logout.php"><?php echo _('Logout'); ?></a><br>
    </div>
    <div id="forms">
      <form id="tag" name="tagcheck" method="post"
        action="admintagsusersubmit.php"></form>
      <table align="center">
        <tr>
          <td><?php echo _('Name'); ?>:</td>
          <td colspan="2"><?php echo $realname; ?></td>
        </tr>
        <tr>
          <td><?php echo _('Email'); ?>:</td>
          <td colspan="2"><?php print $username; ?></td>
        </tr>
        <tr>
          <th colspan=2 class=check><?php echo _('Tags'); ?>:</th>
        </tr>
<?php

if (isset($_GET['clear'])) {
  // нужно получить имена, идентификаторы тегов и пользователей кому назначен
  $query = "SELECT A.tag_id, A.tag_name FROM tags A
    WHERE A.domain_id = :domain_id";
  $sth = $dbh->prepare($query);
  $sth->execute(array(':domain_id'=>$_SESSION['domain_id']));
  $result = $sth->fetchAll();

  foreach($result as $row){
?>
        <tr>
          <td>
            <input form="tag" type="checkbox" name="tc[]" value="<?php
            echo ($row['tag_id']); ?>">
          </td>
          <td colspan=2><?php echo ($row['tag_name']); ?></td>
        </tr>
<?php
  }
}
else if ($_GET['user_id'] != 0) {
  // нужно получить имена.
  // идентификаторы тегов и пользователей кому назначен
  $query = "SELECT A.tag_id, A.tag_name, B.user_id
    FROM tags A
    LEFT JOIN tags_user B
    ON A.tag_id = B.tag_id
    WHERE A.domain_id = :domain_id";
  $sth = $dbh->prepare($query);
  $sth->execute(array(':domain_id'=>$_SESSION['domain_id']));
  $result = $sth->fetchAll();
  $RowCount=0; $t=array('tag_id'=>0, 'tag_name'=>0, 'user_id'=>0);

  // чтобы вывести список всех тегов из масива без
  // повторов, заменяем все user_id на 1.
  for ($i=count($result)-1; $i>=0; $i--)
    if($result[$i]['user_id'] != $_GET['user_id'])
      $result[$i]['user_id'] = 1;
  // Считаем количество повторных строк
  // и сравниваем с ссумой user_id
  // Если совпадет то втакой группе небыло реально id пользователя
  foreach($result as $row){
    //первое заполнение банка $t
    if ($t['tag_id']==0) {
      $t=$row;
      $RowCount++;
    }
    else {
      // следующаяя строка повторяется?
      if ($t['tag_id'] == $row['tag_id']) {
        $t['user_id'] += $row['user_id'];
        $RowCount++;
      }
      else {
        if ($t['user_id'] != $RowCount) {
?>
        <tr>
          <td><input form="tag" type="checkbox" name="tc[]" value="<?php
            echo ($t['tag_id']); ?>" checked="checked"></td>
          <td colspan="2"><?php echo ($t['tag_name']); ?></td>
        </tr>
<?php
          $t=$row;
          $RowCount=1;
        }
        else {
?>
        <tr>
          <td><input form="tag" type="checkbox" name="tc[]" value="<?php
            echo ($t['tag_id']);?>"></td>
          <td colspan="2"><?php echo($t['tag_name']); ?></td>
        </tr>
<?php
          $t=$row;
          $RowCount=1;
        }
      }
    }
  }
  if ($t['user_id'] != $RowCount) {
?>
        <tr>
          <td><input form="tag" name="tc[]" type="checkbox" value="<?php
            echo($t['tag_id']);?>" checked="checked"></td>
          <td colspan=2><?php echo($t['tag_name']); ?></td>
        </tr>
<?php
  }
  else {
?>
        <tr>
          <td><input form="tag" type="checkbox" name="tc[]" value="<?php
            echo ($t['tag_id']); ?>."></td>
          <td colspan=2><?php echo($t['tag_name']); ?></td>
        </tr>
<?php
  }
}

?>
        <tr>
          <td colspan="3" class="button">
            <input form="tag" type="hidden" name="user_id" value="<?php
            echo ($_GET['user_id']); ?>">
            <input form="tag" type="submit" name="submit" value="<?php
              echo _('Submit'); ?>">
            <input form="tag" type="submit" name="clear" value="<?php
              echo _('Clear'); ?>">
          </td>
        </tr>
      </table>
    </div>
  </body>
</html>
<!-- Layout and CSS tricks obtained from http://www.bluerobot.com/web/layouts/ -->
