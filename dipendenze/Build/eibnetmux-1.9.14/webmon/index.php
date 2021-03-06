<?php
/**************************************************************/
/*                        EIBnetmux webmon                    */
/*             Copyright (C) 2006-2009 by Urs Zurbuchen       */
/*                                                            */
/**************************************************************/


/**************************************************************/
/* deteremine our location in the file system                 */
/**************************************************************/
$webmonBasePath = str_replace( "\\", "/", dirname(__FILE__) ) . "/";

/**************************************************************/
/* load required files                                        */
/**************************************************************/
require_once( $webmonBasePath . "/config.php" );
require_once( "eibnetmux.php" );

/**************************************************************/
/* global variables                                           */
/**************************************************************/

/**************************************************************/
/* execute on commands                                        */
/**************************************************************/

function hex_dump( $buf, $len )
{
	$code = unpack( "C" . $len . "c", $buf );
	foreach( $code as $dec ) {
		printf( "%02X ", $dec );
	}
	echo( "<br>" );
}

function uptime( $seconds )
{
	$hours = intval( $seconds / 3600 );
	$minutes = intval( $seconds % 3600 ) / 60;
	$seconds = $seconds % 60;
	$days    = intval( $hours / 24 );
	$hours   = $hours % 24;
	if( $days > 0 ) {
		$uptime = sprintf( "%d days %02d:%02d:%02d", $days, $hours, $minutes, $seconds );
	} else {
		$uptime = sprintf( "%02d:%02d:%02d", $days, $hours, $minutes, $seconds );
	}
	return( $uptime );
}

if( isset( $_GET['s'] )) {
	$server = $_GET['s'];
} else {
	$server = 0;
}

$conn = new eibnetmux( "webmon", $configEibNetMuxConnection[$server]['host'], $configEibNetMuxConnection[$server]['port'] );
$status = $conn->mgmt_getstatus();
$conn->close();

// printf( "<pre>" );
// print_r( $status );
// printf( "</pre>" );

$systemUptime = $status['value']['common']['uptime'];
$clientUptime = $status['value']['client']['uptime'];

$socketState = $status['value']['socket']['active_tcp'] ? "TCP" : "";
if( $socketState =! "" ) $socketState .= " &amp; ";
$socketState .= $status['value']['socket']['active_pipe'] ? "Named pipe" : "";

/**************************************************************/
/* display eibnetmux status                                   */
/**************************************************************/

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!--
/**************************************************************/
/*                        EIBnetmux webmon                    */
/*             Copyright (C) 2006-2009 by Urs Zurbuchen       */
/*                                                            */
/**************************************************************/
-->
<html xmlns="http://www.w3.org/1999/xhtml"><head>
  <title>EIBnetmux webmon</title>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
  <link rel="icon" href="./favicon.ico" type="image/x-icon" />
  <link rel="shortcut icon" href="./favicon.ico" type="image/x-icon" />
  <link rel="stylesheet" href="./eibnetmux.css" type="text/css" />
  <meta name="author" content="Urs Zurbuchen" />
  <meta http-equiv="Pragma" content="no-cache" />
  <script type="text/javascript" src="./browser.js"></script>
</head>
<body id="eibnetmuxBody">
<script type="text/javascript">
    function get_element(id) {
        if (typeof id != 'string') return id;
        if (document.getElementById)
            return document.getElementById(id);
        if (document.all)
            return document.all[id];
        return null;
    }

    var e;
    if (document.getElementById)
        e = document.getElementById("browser_warning");
    if( e ) {
        e.style.visibility = 'hidden';
        if( browser.is_ie && browser.v_ie < 7 ) {
            e.style.visibility = 'visible';
        }
    }
</script>
<div id="header">
  <span id="logo"><img alt="eibnetmux logo" src="./eibnetmux_logo.png" height="55" /></span>
    <span id="browser_warning"><a href="http://getfirefox.com/" title="Get Firefox - Take Back the Web">
    <img class="firefox" src="http://www.mozilla.org/products/firefox/buttons/getfirefox_88x31.png" width="88" height="31" border="0" alt="Get Firefox" />
    </a>
    You really should upgrade to Firefox. Even this simple page is not rendered correctly by Internet Explorer.
    </span>
</div>
<?php
  if( count( $configEibNetMuxConnection, 0 ) > 1 ) {
    print( "<div id=\"selection\">\n" );
    print( "  <div id=\"tabs\">\n" );
    print( "    <ul>\n" );
    foreach( $configEibNetMuxConnection as $nr => $entry ) {
      if( $entry['port'] == "4390" ) {
        printf( "      <li><a %s href=\"?s=%d\"><span>%s</span></a></li>\n", ($nr == $server) ? "class=\"selected\"" : "", $nr, $entry['host'] );
      } else {
        printf( "      <li><a %s href=\"?s=%d\"><span>%s:%s</span></a></li>\n", ($nr == $server) ? "class=\"selected\"" : "", $nr, $entry['host'], $entry['port'] );
      }
    }
    print( "    </ul>\n" );
    print( "  </div>\n" );
    print( "</div>\n" );
  } else {
    print( "<div id=\"headersep\">\n" );
    print( "</div>\n" );
  }
?>
<div id="menu">
  <p class="title">WebMon</p>
  <p class="item"><a href="">Status</a></p>
  <p class="item"><a href="mgmt_connect.php?l=1&s=<?php echo $server; ?>">Increase log level</a></p>
  <p class="item"><a href="mgmt_connect.php?l=0&s=<?php echo $server; ?>">Decrease log level</a></p>
  <?php if( $status['value']['client']['active'] == 1 ) { ?>
  <p class="item"><a href="mgmt_connect.php?c=0&s=<?php echo $server; ?>">Disconnect from bus</a></p>
  <?php } else { ?>
  <p class="item"><a href="mgmt_connect.php?c=1&s=<?php echo $server; ?>">Connect to bus</a></p>
  <?php } ?>
</div>

<div id="contents">
	<table class="status_table">
    <tr>
      <td class="title" colspan="8">EIBnetmux @ <?php echo( $configEibNetMuxConnection[$server]['host'] ); ?></td>
    </tr>
	<tr>
	  <td class="status_header" colspan="8">System Information</td>
	</tr>
	<tr>
	  <td class="status_label">Version:</td>
	  <td class="status_value"><?php printf( "%s", $status['value']['common']['version'] ); ?></td>
	  <td class="status_empty">&nbsp;</td>
	  <td class="status_label">Uptime:</td>
	  <td class="status_value"><?php printf( "%s", $systemUptime ); ?></td>
	  <td class="status_empty">&nbsp;</td>
	  <td class="status_label">Daemon mode:</td>
	  <td class="status_value"><?php printf( "%s", $status['value']['common']['daemon'] == 1 ? "yes" : "no" ); ?></td>
	</tr>
	<tr>
	  <td class="status_label">Log level:</td>
	  <td class="status_value"><?php printf( "%d", $status['value']['common']['level'] ); ?></td>
	  <td class="status_empty">&nbsp;</td>
	  <td class="status_label">User id:</td>
	  <td class="status_value"><?php printf( "%d", $status['value']['common']['user'] ); ?></td>
	  <td class="status_empty">&nbsp;</td>
	  <td class="status_label">Group id:</td>
	  <td class="status_value"><?php printf( "%d", $status['value']['common']['group'] ); ?></td>
	</tr>
	<tr>
	  <td class="status_empty" colspan="8"><hr /></td>
	</tr>
	<tr>
	  <td class="status_header" colspan="8">EIBnet/IP Client</td>
	</tr>
	<tr>
	  <td class="status_label">Connected:</td>
	  <td class="status_value"><?php printf( "%s", $status['value']['client']['active'] == 1 ? $status['value']['client']['targetname'] . ":" . $status['value']['client']['targetport'] : "no" ); ?></td>
	  <td>&nbsp;</td>
	  <td class="status_label">Uptime:</td>
	  <td class="status_value"><?php printf( "%s", $clientUptime ); ?></td>
	  <td>&nbsp;</td>
	  <td class="status_label">Heartbeats missed:</td>
	  <td class="status_value"><?php printf( "%d", $status['value']['client']['missed'] ); ?></td>
	</tr>
	<tr>
	  <td class="status_label">Received / Sent:</td>
	  <td class="status_value"><?php printf( "%d / %d", $status['value']['client']['received_total'], $status['value']['client']['sent_total'] ); ?></td>
	  <td>&nbsp;</td>
	  <td class="status_label">This session:</td>
	  <td class="status_value"><?php printf( "%d / %d", $status['value']['client']['received'], $status['value']['client']['sent'] ); ?></td>
	  <td>&nbsp;</td>
	  <td class="status_label">In queue:</td>
	  <td class="status_value"><?php printf( "%d", $status['value']['client']['queue'] ); ?></td>
	</tr>
	<tr>
	  <td class="status_empty" colspan="8"><hr /></td>
	</tr>
	<tr>
	  <td class="status_header" colspan="8">EIBnet/IP Server</td>
	</tr>
	<tr>
	  <td class="status_label">Running:</td>
	  <td class="status_value"><?php printf( "%s", $status['value']['server']['active'] == 1 ? "yes" : "no" ); ?></td>
	  <td>&nbsp;</td>
	  <td class="status_label">Listening on:</td>
	  <td class="status_value"><?php printf( "%d", $status['value']['server']['port'] ); ?></td>
	  <td>&nbsp;</td>
	  <td class="status_label">Clients:</td>
	  <td class="status_value"><?php printf( "%d / %d", $status['value']['server']['nr_clients'], $status['value']['server']['max_clients'] ); ?></td>
	</tr>
	<tr>
	  <td class="status_label">Received:</td>
	  <td class="status_value"><?php printf( "%d", $status['value']['server']['received_total'] ); ?></td>
	  <td>&nbsp;</td>
	  <td class="status_label">Sent:</td>
	  <td class="status_value"><?php printf( "%d", $status['value']['server']['sent_total'] ); ?></td>
	  <td>&nbsp;</td>
	  <td class="status_label">In queue:</td>
	  <td class="status_value"><?php printf( "%d", $status['value']['server']['queue'] ); ?></td>
	</tr>
	<?php
	if( $status['value']['server']['nr_clients'] > 0 ) {
	?>
	<tr>
	  <td>&nbsp;</td>
	  <td colspan="7">
	    <table>
	    <tr>
	      <td class="status_label">Client</td>
	      <td>&nbsp;</td>
	      <td class="status_colhead_left">Address</td>
	      <td>&nbsp;</td>
	      <td class="status_colhead_right">Received</td>
	      <td>&nbsp;</td>
	      <td class="status_colhead_right">Sent</td>
	    </tr>
	<?php
		for( $loop = 0; $loop < $status['value']['server']['nr_clients']; $loop++ ) {
	?>
	    <tr>
	      <td class="status_listlabel"><?php echo $loop +1; ?></td>
	      <td>&nbsp;</td>
	      <td class="status_listvalue"><?php printf( "%s", $status['value']['server']['clients'][$loop]['ip'] ); ?></td>
	      <td>&nbsp;</td>
	      <td class="status_listvalue"><?php printf( "%d", $status['value']['server']['clients'][$loop]['received'] ); ?></td>
	      <td>&nbsp;</td>
	      <td class="status_listvalue"><?php printf( "%d", $status['value']['server']['clients'][$loop]['sent'] ); ?></td>
	    </tr>
	<?php
		}
	?>
	    </table>
	  </td>
	</tr>
	<?php
	}
	?>
	<tr>
	  <td class="status_empty" colspan="8"><hr /></td>
	</tr>
	<tr>
	  <td class="status_header" colspan="8">Socket Server</td>
	</tr>
	<?php
	if( $status['value']['socket']['active_tcp'] != 0 ) {
	?>
	<tr>
	  <td class="status_label">TCP:</td>
	  <td class="status_value"><?php printf( "%s", $status['value']['socket']['active_tcp'] ? "yes" : "" ); ?></td>
	  <td>&nbsp;</td>
	  <td class="status_label">Listening on:</td>
	  <td class="status_value"><?php printf( "%d", $status['value']['socket']['port'] ); ?></td>
	  <td>&nbsp;</td>
	  <td class="status_label">Clients:</td>
	  <td class="status_value"><?php printf( "%d / %d", $status['value']['socket']['nr_clients'], $status['value']['socket']['max_clients'] ); ?></td>
	</tr>
	<?php
	}
	if( $status['value']['socket']['active_pipe'] != 0 ) {
	?>
	<tr>
	  <td class="status_label">Named pipe:</td>
	  <td class="status_value"><?php printf( "%s", $status['value']['socket']['active_pipe'] ? "yes" : "" ); ?></td>
	  <td>&nbsp;</td>
	  <td class="status_label">Listening on:</td>
	  <td class="status_value"><?php printf( "%d", $status['value']['socket']['pipe'] ); ?></td>
	  <td>&nbsp;</td>
	<?php
	if( $status['value']['socket']['active_tcp'] != 0 ) {
	?>
	  <td class="status_label">&nbsp;</td>
	  <td class="status_value">&nbsp;</td>
	<?php
	} else {
	?>
	  <td class="status_label">Clients:</td>
	  <td class="status_value"><?php printf( "%d / %d", $status['value']['socket']['nr_clients'], $status['value']['socket']['max_clients'] ); ?></td>
	<?php
	}
	?>
	</tr>
	<?php
	}
	?>
	<tr>
	  <td class="status_label">Received:</td>
	  <td class="status_value"><?php printf( "%d", $status['value']['socket']['received_total'] ); ?></td>
	  <td>&nbsp;</td>
	  <td class="status_label">Sent:</td>
	  <td class="status_value"><?php printf( "%d", $status['value']['socket']['sent_total'] ); ?></td>
	  <td>&nbsp;</td>
	  <td class="status_label">In queue:</td>
	  <td class="status_value"><?php printf( "%d", $status['value']['socket']['queue'] ); ?></td>
	</tr>
	<?php
	if( $status['value']['socket']['nr_clients'] > 0 ) {
	?>
	<tr>
	  <td>&nbsp;</td>
	  <td colspan="7">
	    <table>
	    <tr>
	      <td class="status_label">Client</td>
	      <td>&nbsp;</td>
	      <td class="status_colhead_left">Address</td>
	      <td>&nbsp;</td>
	      <td class="status_colhead_right">Received</td>
	      <td>&nbsp;</td>
	      <td class="status_colhead_right">Sent</td>
	    </tr>
	<?php
		for( $loop = 0; $loop < $status['value']['socket']['nr_clients']; $loop++ ) {
	?>
	    <tr>
	      <td class="status_listlabel"><?php echo $loop +1 . ": " . $status['value']['socket']['clients'][$loop]['identifier'] ?></td>
	      <td>&nbsp;</td>
	      <td class="status_listvalue"><?php printf( "%s", $status['value']['socket']['clients'][$loop]['ip'] ); ?></td>
	      <td>&nbsp;</td>
	      <td class="status_listvalue"><?php printf( "%d", $status['value']['socket']['clients'][$loop]['received'] ); ?></td>
	      <td>&nbsp;</td>
	      <td class="status_listvalue"><?php printf( "%d", $status['value']['socket']['clients'][$loop]['sent'] ); ?></td>
	    </tr>
	<?php
		}
	?>
	    </table>
	  </td>
	</tr>
	<?php
	}
	?>
	<tr>
	  <td class="status_empty" colspan="8"><hr /></td>
	</tr>
	<tr>
	  <td class="status_header" colspan="8">EIBD Server</td>
	</tr>
	<tr>
	  <td class="status_label">Running:</td>
	  <td class="status_value"><?php printf( "%s", $status['value']['eibd']['active'] == 1 ? "yes" : "no" ); ?></td>
	  <td>&nbsp;</td>
	  <td class="status_label">Listening on:</td>
	  <td class="status_value"><?php printf( "%d", $status['value']['eibd']['port'] ); ?></td>
	  <td>&nbsp;</td>
	  <td class="status_label">Clients:</td>
	  <td class="status_value"><?php printf( "%d / %d", $status['value']['eibd']['nr_clients'], $status['value']['eibd']['max_clients'] ); ?></td>
	</tr>
	<tr>
	  <td class="status_label">Received:</td>
	  <td class="status_value"><?php printf( "%d", $status['value']['eibd']['received_total'] ); ?></td>
	  <td>&nbsp;</td>
	  <td class="status_label">Sent:</td>
	  <td class="status_value"><?php printf( "%d", $status['value']['eibd']['sent_total'] ); ?></td>
	  <td>&nbsp;</td>
	  <td class="status_label">In queue:</td>
	  <td class="status_value"><?php printf( "%d", $status['value']['eibd']['queue'] ); ?></td>
	</tr>
	<?php
	if( $status['value']['eibd']['nr_clients'] > 0 ) {
	?>
	<tr>
	  <td>&nbsp;</td>
	  <td colspan="7">
	    <table>
	    <tr>
	      <td class="status_label">Client</td>
	      <td>&nbsp;</td>
	      <td class="status_colhead_left">Address</td>
	      <td>&nbsp;</td>
	      <td class="status_colhead_right">Received</td>
	      <td>&nbsp;</td>
	      <td class="status_colhead_right">Sent</td>
	    </tr>
	<?php
		for( $loop = 0; $loop < $status['value']['eibd']['nr_clients']; $loop++ ) {
	?>
	    <tr>
	      <td class="status_listlabel"><?php echo $loop +1; ?></td>
	      <td>&nbsp;</td>
	      <td class="status_listvalue"><?php printf( "%s:%04d", $status['value']['eibd']['clients'][$loop]['ip'], $status['value']['eibd']['clients'][$loop]['port']); ?></td>
	      <td>&nbsp;</td>
	      <td class="status_listvalue"><?php printf( "%d", $status['value']['eibd']['clients'][$loop]['received'] ); ?></td>
	      <td>&nbsp;</td>
	      <td class="status_listvalue"><?php printf( "%d", $status['value']['eibd']['clients'][$loop]['sent'] ); ?></td>
	    </tr>
	<?php
		}
	?>
	    </table>
	  </td>
	</tr>
	<?php
	}
	?>
	<tr>
	  <td class="status_empty" colspan="8">&nbsp;</td>
	</tr>
	<tr>
	  <td class="status_empty" colspan="8">&nbsp;</td>
	</tr>
	<tr>
	  <td class="status_copyright" colspan="8">
	    Powered by EIBnetmux webmon 1.9.14 (Urs Zurbuchen): >> <a href="http://eibnetmux.sourceforge.net/">Homepage</a><<
	  </td>
	</tr>  
	</table>
  </div>
</div>

</body>
</html>
