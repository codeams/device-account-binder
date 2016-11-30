<?php

/*
// credentials
$ldaprdn  = '10001098';     // ldap rdn or dn
$ldappass = 'password';  // associated password


// connecting to the server
$ldapconn = ldap_connect( "148.209.67.42" )
  or die( "Could not connect to LDAP server." );


if ( $ldapconn ) {

  // authenticating
  $ldapbind = ldap_bind( $ldapconn, $ldaprdn, $ldappass );

  // verifying bind
  if ( $ldapbind ) {

      echo "LDAP bind successful...";

  } else {

      echo "LDAP bind failed...";

  }

}
*/

require 'credentials.php';

$con = @ldap_connect('ldap://148.209.67.42');
ldap_set_option($con, LDAP_OPT_PROTOCOL_VERSION, 3);
ldap_set_option($con, LDAP_OPT_REFERRALS, 0);
var_dump(@ldap_bind($con, 'a10001098@alumnos.uady.mx', $password));

?>
