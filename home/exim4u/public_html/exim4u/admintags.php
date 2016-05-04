<?php
  include_once dirname(__FILE__) . '/config/variables.php';
  include_once dirname(__FILE__) . '/config/authpostmaster.php';
  include_once dirname(__FILE__) . '/config/functions.php';
  include_once dirname(__FILE__) . '/config/httpheaders.php';

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
      <a href="admintagsadd.php?tag_new=yes"><?php echo _('Add New Tag'); ?></a>
      <br>
      <a href="admin.php"><?php echo _('Main Menu'); ?></a><br>
      <br><a href="logout.php"><?php echo _('Logout'); ?></a><br>
    </div>
    <div id="Content">

      <table>
        <tr>
          <th>&nbsp;</th>
          <th><?php echo _('Tags'); ?></th>
        </tr>
        <?php

        $query = "SELECT t.tag_id, t.tag_name FROM tags t
          WHERE t.domain_id=:domain_id"; 
        $sth = $dbh->prepare($query);
        $sth->execute(array(':domain_id'=>$_SESSION['domain_id']));
        print '<form action="admintagssubmit.php" method="post" id="deletetag"></form>';
        while ($row = $sth->fetch()) {
          print '<tr>';
          print '<td class="trash">';
          print '<input type="submit" class="tagsformsubmit" name="submitdelete" value="'.$row['tag_id'].'" form="deletetag" />';
          print '</td>';
          print '<td class="tags"><a href="admintagsadd.php?'
            .'tag_id='.$row['tag_id']
            .'&tag_name='.$row['tag_name'].'">'
            .$row['tag_name'].'</a></td>';
          print "</tr>\n";
        }

        ?>
      </table>
    </div>
  </body>
</html>
<!-- Layout and CSS tricks obtained from http://www.bluerobot.com/web/layouts/ -->
