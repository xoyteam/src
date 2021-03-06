<?php
  include_once dirname(__FILE__) . '/config/variables.php';
  include_once dirname(__FILE__) . '/config/authpostmaster.php';
  include_once dirname(__FILE__) . '/config/functions.php';
  include_once dirname(__FILE__) . '/config/httpheaders.php';

  $query = "DELETE FROM users
     WHERE user_id=:user_id
     AND domain_id=:domain_id AND type='fail'";
  $sth = $dbh->prepare($query);
  $success = $sth->execute(array(':user_id'=>$_GET['user_id'], ':domain_id'=>$_SESSION['domain_id']));
  if ($success) {
    header ("Location: adminfail.php?deleted={$_GET['localpart']}");
  } else {
    header ("Location: adminfail.php?faildeleted={$_GET['localpart']}");
	die;
  }
?>
<!-- Layout and CSS tricks obtained from http://www.bluerobot.com/web/layouts/ -->
