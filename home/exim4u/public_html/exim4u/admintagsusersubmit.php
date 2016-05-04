<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once dirname(__FILE__) . '/config/variables.php';
include_once dirname(__FILE__) . '/config/authpostmaster.php';
include_once dirname(__FILE__) . '/config/functions.php';
include_once dirname(__FILE__) . '/config/httpheaders.php';

# confirm that the postmaster
# is updating a user they are permitted to change before going further
$query = "SELECT * FROM users WHERE user_id=:user_id
  AND domain_id=:domain_id AND (type='local' OR type='piped')";
$sth = $dbh->prepare($query);
$sth->execute(array(':user_id'=>$_POST['user_id'],
  ':domain_id'=>$_SESSION['domain_id']));
if (!$sth->rowCount()) {
  header ("Location: admintags.php?failupdated={$_POST['localpart']}");
  die();
}


if (isset($_POST['clear'])) {
  unset($_POST['clear']);
  header("Location: admintagsuser.php?user_id={$_POST['user_id']}&clear=yes");
  die;
}
else if (isset($_POST['submit'])) {
  unset($_POST['clear']);
  if (isset($_POST['user_id'])) $userid=$_POST['user_id'];
  if (isset($_POST['tc'])) $tc = $_POST['tc'];


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
    if($result[$i]['user_id'] != $userid)
      $result[$i]['user_id'] = 1;

  // указатель последней пустой ячейки в массиве для результирующего
  // массива (списка связей) из базы.
  // CURrent RELation
  $currel=0;
  // массив списка связей
  // LiSt RELation
  $lsrel= array();

  foreach($result as $row) {
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
          // checked tag for user
          $t['user_id'] = $userid;
          $lsrel[$currel] = $t;
          $currel++;
          // вспомогательные действия
          $t=$row;
          $RowCount=1;
        }
        else {
          // unchecked tag for user
          $t['user_id'] = 1;
          $lsrel[$currel] = $t;
          $currel++;

          // вспомогательные действия
          $t=$row;
          $RowCount=1;
        }
      }
    }
  }

  if ($t['user_id'] != $RowCount) {
    // checked tag for user
    $lsrel[$currel] = $t;
    $currel++;
    // вспомогательные действия
    $t=$row;
    $RowCount=1;
  }
  else {
    // unchecked tag for user
    $lsrel[$currel] = $t;
    $currel++;

    // вспомогательные действия
    $t=$row;
    $RowCount=1;
  }

  // из массива отмеченых в форме галочек ($tc)
  // формируем строку с необходимым количеством
  // вопросиков для размещеня перечисления в sql запросе.

  // если неопределен массив с формы (галочки тегов) для удаления =>
  // то сверять нечего - удалить все имеющиеся связи
  if (isset($tc)) {
    $in  = str_repeat('?,', count($tc) - 1) . '?';
    $query = "SELECT * FROM tags_user B
      LEFT JOIN tags A
      ON A.tag_id = B.tag_id
      WHERE A.domain_id = ?
      AND B.user_id = ?
      AND (B.tag_id NOT IN ($in))";
    $sth = $dbh->prepare($query);
    $tmptc = $tc;
    array_splice ($tmptc,0,0,array($_SESSION['domain_id'],$userid));
    $sth->execute($tmptc);
    unset ($tmptc);
    // получили список связей (тег-пользователь)
    // галочки на которых сняты. для удаления.
    $data = $sth->fetchAll();
    $re=count($data);

    $in  = str_repeat('?,', count($data) - 1) . '?';
    $query = "DELETE FROM tags_user
      WHERE user_id = ?
      AND id IN ($in)";
    $sth = $dbh->prepare($query);
    $delid[] = $userid;

    foreach($data as $v)
      $delid[] = $v['id'];
    $sth->execute($delid);

    // Получаем список тегов для которыых нужно
    // создать связи тег-пользователь.
    // get base rel row.
    // lsrel - list all relation
    $tmptc = $tc;
    foreach($lsrel as $k => $v) {
      // check user-id in base.
      // if current user check tag if exist
      if ($v['user_id'] == 4)
        foreach ($tmptc as $key => $tcTagId)
          if ($v['tag_id'] == $tcTagId)
            unset($tmptc[$key]);
    }
    if (count($tmptc)>0) {
      $in = str_repeat('(?, ?),', count($tmptc));
      $in = substr($in,0,-1);
      $query = "INSERT INTO tags_user (user_id, tag_id)
        VALUES $in";
      $sth = $dbh->prepare($query);
      foreach ($tmptc as &$v)
      {
        $qdata[] = $userid;
        $qdata[] = $v;
      }
      $sth->execute($qdata);
    }

    header ("Location: admintagsuser.php?user_id=".$userid);
    die();
  }
  else
  {
    $query = "DELETE FROM tags_user WHERE user_id = ?";
    $sth = $dbh->prepare($query);
    $delid[] = $userid;
    $sth->execute($delid);

    header ("Location: admintagsuser.php?user_id=".$userid."&nothing=yes");
    die();
  }
}




/*if ($success) {
header ("Location: adminuser.php?updated={$_POST['localpart']}");
} else {
header ("Location: adminuser.php?failupdated={$_POST['localpart']}");
}*/
?>
