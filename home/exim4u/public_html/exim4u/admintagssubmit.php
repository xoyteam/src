<?php
include_once dirname(__FILE__) . '/config/httpheaders.php';
include_once dirname(__FILE__) . '/config/variables.php';
include_once dirname(__FILE__) . '/config/authpostmaster.php';
include_once dirname(__FILE__) . '/config/functions.php';


#delete tag
if (isset($_POST['submitdelete'])) {
  $query = "SELECT COUNT (tag_id) AS count FROM tags WHERE tag_id=:tag_id AND domain_id=:domain_id";
  $sth = $dbh->prepare($query);
  $sth->execute(array(':tag_id'=>$_POST['submitdelete'],':domain_id'=>$_SESSION['domain_id']));
  $row = $sth->fetch();
  if ($row['count'] == "0") {
    header('Location: admintags.php?notfounddeleteid=yes');
    die;
  }
  header("Location: admintagsdelete.php?tag_id={$_POST['submitdelete']}");
  die;
}

# check blank tagname
if (preg_match("/^\s*$/",$_POST['tag_name'])) {
  header('Location: admintags.php?tagblankname=yes');
  die;
}
else
{
  # Strip off leading and trailing spaces
  $tname = preg_replace("/^\s+/","",$_POST['tag_name']);
  $tname = preg_replace("/\s+$/","",$tname); 
  $tname = preg_replace("/\s+/","_",$tname); 

  //print '<p>'.$_POST['tag_name'].'</p>';
  //header('Location: admintagsadd.php?tagexist='.$_POST['tag_name']);
  //die;
  # check dublicate
  $query = "SELECT tag_id, tag_name FROM tags WHERE tag_name LIKE :tag_name";
  $sth = $dbh->prepare($query);
  $sth->execute(array(':tag_name'=>$tname));
  if ($sth->rowCount() > 0) {
    header('Location: admintagsadd.php?tagexist=yes');
    die;
  }
  else {
    // add new tag
    if (($tname != "") && ($_POST['tag_id'] == ""))
    {
      $query = "INSERT INTO tags (tag_name,domain_id) VALUES (:tag_name,:domain_id)";
      $sth = $dbh->prepare($query);
      $success = $sth->execute(array(':tag_name'=>$tname,':domain_id'=>$_SESSION['domain_id']));
      if ($success) {
        header ("Location: admintags.php?tagadded=1&tagname=".$tname);
        die;
      } else {
        header ("Location: admintagsadd.php?errortagadded=1");
        die;
      }
    }
    else{
      //modify tag
      $query = "UPDATE tags SET tag_name=:tag_name WHERE tag_id=:tag_id";
      $sth = $dbh->prepare($query);
      $success = $sth->execute(array(':tag_name'=>$tname,':tag_id'=>$_POST['tag_id']));
      if ($success) {
        header ("Location: admintags.php?tagupdated=1&tagname=".$tname);
        die;
      } else {
        header ("Location: admintagsadd.php?errorupdate=1");
        die;
      }
    }
  }
}
?>
<!-- Layout and CSS tricks obtained from http://www.bluerobot.com/web/layouts/ -->
