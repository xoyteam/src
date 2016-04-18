<?php
include_once dirname(__FILE__) . '/config/httpheaders.php';
include_once dirname(__FILE__) . '/config/variables.php';
include_once dirname(__FILE__) . '/config/authpostmaster.php';
include_once dirname(__FILE__) . '/config/functions.php';




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
    // modify tag of add new tag
    if (($tname != "") && ($_POST['tag_id'] == "")){
      //header('Location: tagstest.php?add=yes&tag_id='.$_POST['tag_id'].'&tag_name='.$_POST['tag_name']);
      print 'new tag<br>';
      print 'POST=';
    print_r($_GET);  // for all GET variables
      print 'GET=';
print_r($_POST); // for all POST variables
print '<br>tname='.$tname.'<br>';
    }else{
      //header('Location: tagstest.php?modify=yes&tag_id='.$_POST['tag_id'].'&tag_name='.$_POST['tag_name']);
      print 'modify tag<br>';
      print 'POST=';
    print_r($_GET);  // for all GET variables
      print 'GET=';
print_r($_POST); // for all POST variables
print '<br>tname='.$tname.'<br>';
    }die;
  }
}

#$query = "INSERT INTO tags (tag_name) VALUES (:tag_name)";
#$sth = $dbh->prepare($query);
#$success = $sth->execute(':tag_name'=>$_POST['tag_name']);
#if ($success) {
if (1) {
  header ("Location: admintags.php?tagadded=1&tagname=".$_POST['tag_name']);
  die;
} else {
  header ("Location: admintags.php?tagadded=0");
  die;
}
?>
<!-- Layout and CSS tricks obtained from http://www.bluerobot.com/web/layouts/ -->
