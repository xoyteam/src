<?php

    /**
     * validate user password
     *
     * validate if password and confirmation password match.
     * They can not be empty.
     *
     * @param   string   $clear   cleartext password
     * @param   string   $vclear  cleartext password (for validation)
     * @return  boolean  true if they match and contain no illegal characters
     */
    function validate_password($clear,$vclear) 
    {
        return ($clear === $vclear) && ($clear !== "");
    }


    /**
     * Check if a user already exists.
     *
     * Queries database $dbh, and redirects to the $page is the user already
     * exists.
     *
     * @param  mixed   $dbh         database to query
     * @param  string  $localpart  
     * @param  string  $domain_id
     * @param  string  $page       page to return to
     */
    function check_user_exists($dbh,$localpart,$domain_id,$page) 
    {
        $query = "SELECT COUNT(*) AS c 
                  FROM   users
                  WHERE localpart=:localpart
                  AND domain_id=:domain_id";
        $sth = $dbh->prepare($query);
        $sth->execute(array(':localpart'=>$localpart, ':domain_id'=>$domain_id));
        $row = $sth->fetch();
        if ($row['c'] != 0) 
        {
            header ("Location: $page?userexists=$localpart");
            die;
        }
    }


    /**
     * Render the alphabet. Directly onto the page.
     *
     * @param  unknown  $flag  unknown
     */
    function alpha_menu($flag) 
    {
        global $letter;      // needs to be available to the parent
        if ($letter == 'all') 
        {
            $letter = '';
        }
        if ($flag) 
        {
            print "\n<p class='alpha'><a href='" . $_SERVER['PHP_SELF'] . 
                  "?LETTER=ALL' class='alpha'>ALL</a>&nbsp;&nbsp; ";
            // loops through the alphabet. 
            // For international alphabets, replace the string in the proper order
            foreach (preg_split('//', _("ABCDEFGHIJKLMNOPQRSTUVWXYZ"), -1, 
                                PREG_SPLIT_NO_EMPTY) as $i) 
            {
                    print "<a href='" . $_SERVER['PHP_SELF'] . 
                      "?LETTER=$i' class='alpha'>$i</a>&nbsp; ";
            }
            print "</p>\n";
        }
    }

    /**
     * crypt the plaintext password.
     *
     * @golbal  string  $cryptscheme
     * @param   string  $clear  the cleartext password
     * @param   string  $salt   optional salt
     * @return  string          the properly crypted password
     */
    function crypt_password($clear, $salt = '')
    {
        global $cryptscheme;

        if($cryptscheme === 'sha') {
            $hash = sha1($clear);
            $cryptedpass = '{SHA}' . base64_encode(pack('H*', $hash));
        } elseif ($cryptscheme === 'CLEAR') {
            $cryptedpass=$clear;
        } else {
            if(empty($salt)) {
                switch($cryptscheme){
                    case 'des':
                        $salt = '';
                    break;
                    case 'md5':
                        $salt='$1$';
                    break;
                    case 'sha512':
                        $salt='$6$';
                    break;
                    case 'bcrypt':
                        $salt='$2a$10$';
                    break;
                    default:
                        if(preg_match('/\$[:digit:][:alnum:]?\$/', $cryptscheme)) {
                            $salt=$cryptscheme;
                        } else {
                            die(_('The value of $cryptscheme is invalid!'));
                        }
                }
                $salt.=get_random_bytes(CRYPT_SALT_LENGTH).'$';
            }
            $cryptedpass = crypt($clear, $salt);
        }
        return $cryptedpass;
    }

    /**
     * Generate pseudo random bytes
     *
     * @param int $count number of bytes to generate
     * @return string A string with the hexadecimal number
     */
    function get_random_bytes($count)
    {
        $output = base64_encode(openssl_random_pseudo_bytes($count));
        $output = strtr(substr($output, 0, $count), '+', '.'); //base64 is longer, so must truncate the result
        return $output;
    }

     /**
    * Properly encode a mail header text for using with mail().
    *
    * @param string $text the text to encode
    */
    function vexim_encode_header($text)
    {
     if (function_exists('mb_encode_mimeheader')) {
         mb_internal_encoding('UTF-8');
         $text = mb_encode_mimeheader($text, 'UTF-8', 'Q');
     } elseif (function_exists('imap_8bit')) {
         $text = str_replace(" ", "_", imap_8bit(trim($text)));
         $text = str_replace("?", "=3F", $text);
         $text = str_replace("=\r\n", "?=\r\n =?UTF-8?Q?", $text);
         $text = "=?UTF-8?Q?" . $text . "?=" ;
     }
     // if both mb and imap are not available, simply return what was given.
     // this isn't standards-compliant, and the header will be displayed
     // incorrectly if it contains accented letters. Let's just hope it won't
     // be the case too often. :)
    return $text;
    }
?>
