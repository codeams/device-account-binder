<?php

class LdapHandler
{
    public static function validateUser($user, $pass)
    {

        $ldap_con = ldap_connect('148.209.67.42', 389)
        or die('Could not connect to LDAP server.');

        if ($ldap_con) {

            ldap_set_option($ldap_con, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($ldap_con, LDAP_OPT_REFERRALS, 0);

            $bind = ldap_bind($ldap_con, "$user@inet.uady.mx", $pass);


            if ($bind) {
                $filter = "(sAMAccountName=$user)";

                $sr = ldap_search($ldap_con, "ou=Facultad de Matematicas, ou=INET, dc=inet, dc=uady, dc=mx", $filter)
                or die("falla en el search");

                $info = ldap_get_entries($ldap_con, $sr)
                or die("No entries");

                if ($info[0]) {
                    $name = $info[0]['givenname'][0];
                    return $name;

                } else {
                    return "invalid_user";
                }
            } else {
                return "incorrect_user";
            }

        }
        return "ldap_error";

    }
}



