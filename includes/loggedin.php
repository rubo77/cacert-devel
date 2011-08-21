<? /*
    LibreSSL - CAcert web application
    Copyright (C) 2004-2008  CAcert Inc.

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; version 2 of the License.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
*/


	if($_SERVER['HTTP_HOST'] == $_SESSION['_config']['securehostname'] && $_SESSION['profile']['id'] > 0 && $_SESSION['profile']['loggedin'] != 0)
	{
		$uid = $_SESSION['profile']['id'];
		$_SESSION['profile']['loggedin'] = 0;
		$_SESSION['profile'] = "";
		foreach($_SESSION as $key)
		{
			if($key == '_config')
				continue;
			if(is_int($key) || is_string($key))
		                unset($_SESSION[$key]);
	                unset($$key);
        	        session_unregister($key);
		}

		$_SESSION['profile'] = mysql_fetch_assoc(mysql_query("select * from `users` where `id`='$uid'"));
		if($_SESSION['profile']['locked'] == 0)
			$_SESSION['profile']['loggedin'] = 1;
		else
			unset($_SESSION['profile']);
	}
  
	if($_SERVER['HTTP_HOST'] == $_SESSION['_config']['securehostname'] && ($_SESSION['profile']['id'] == 0 || $_SESSION['profile']['loggedin'] == 0))
	{
		$query = "select * from `emailcerts` where `serial`='${_SERVER['SSL_CLIENT_M_SERIAL']}' and `revoked`=0 and disablelogin=0 and
				UNIX_TIMESTAMP(`expire`) - UNIX_TIMESTAMP() > 0";
		$res = mysql_query($query);

		if(mysql_num_rows($res) > 0)
		{
			$row = mysql_fetch_assoc($res);

			$_SESSION['profile']['loggedin'] = 0;
			$_SESSION['profile'] = "";
			foreach($_SESSION as $key)
			{
				if($key == '_config')
					continue;
				if(is_int($key) || is_string($key))
			                unset($_SESSION[$key]);
                		unset($$key);
       			        session_unregister($key);
			}

			$_SESSION['profile'] = mysql_fetch_assoc(mysql_query("select * from `users` where `id`='".$row['memid']."'"));
			if($_SESSION['profile']['locked'] == 0)
				$_SESSION['profile']['loggedin'] = 1;
			else
				unset($_SESSION['profile']);
		} else {
			$_SESSION['profile']['loggedin'] = 0;
			$_SESSION['profile'] = "";
			foreach($_SESSION as $key)
			{
				if($key == '_config')
					continue;
		                unset($_SESSION[$key]);
	                	unset($$key);
        		        session_unregister($key);
			}

			unset($_SESSION['_config']['oldlocation']);

			foreach($_GET as $key => $val)
			{
				if($_SESSION['_config']['oldlocation'])
					$_SESSION['_config']['oldlocation'] .= "&";

				$key = str_replace(array("\n", "\r"), '', $key);
				$val = str_replace(array("\n", "\r"), '', $val);
				$_SESSION['_config']['oldlocation'] .= "$key=$val";
 			}
			$_SESSION['_config']['oldlocation'] = substr($_SERVER['SCRIPT_NAME'], 1)."?".$_SESSION['_config']['oldlocation'];

			header("location: https://".$_SESSION['_config']['securehostname']."/index.php?id=4");
			exit;
		}
	}

	if($_SERVER['HTTP_HOST'] == $_SESSION['_config']['securehostname'] && ($_SESSION['profile']['id'] <= 0 || $_SESSION['profile']['loggedin'] == 0))
	{
		header("location: https://".$_SESSION['_config']['normalhostname']);
		exit;
	}

	if($_SERVER['HTTP_HOST'] == $_SESSION['_config']['securehostname'] && $_SESSION['profile']['id'] > 0 && $_SESSION['profile']['loggedin'] > 0)
	{
		$query = "select sum(`points`) as `total` from `notary` where `to`='".$_SESSION['profile']['id']."' group by `to`";
		$res = mysql_query($query);
		$row = mysql_fetch_assoc($res);
		$_SESSION['profile']['points'] = $row['total'];

		if($_SESSION['profile']['language'] == "")
		{
			$query = "update `users` set `language`='".$_SESSION['_config']['language']."'
							where `id`='".$_SESSION['profile']['id']."'";
			mysql_query($query);
		} else {
			$_SESSION['_config']['language'] = $_SESSION['profile']['language'];

			putenv("LANG=".$_SESSION['_config']['language']);
			setlocale(LC_ALL, $_SESSION['_config']['language']);

			$domain = 'messages';
			bindtextdomain("$domain", $_SESSION['_config']['filepath']."/locale");
			textdomain("$domain");
		}
	}

	if(array_key_exists("id",$_REQUEST) && $_REQUEST['id'] == "logout")
	{
		$normalhost=$_SESSION['_config']['normalhostname'];
		$_SESSION['profile']['loggedin'] = 0;
		$_SESSION['profile'] = "";
		foreach($_SESSION as $key => $value)
		{
	                unset($_SESSION[$key]);
	                unset($$key);
        	        session_unregister($key);
		}

		header("location: https://".$normalhost."/index.php");
		exit;
	}

	if($_SESSION['profile']['loggedin'] < 1)
	{
		unset($_SESSION['_config']['oldlocation']);

		foreach($_REQUEST as $key => $val)
		{
			if($_SESSION['_config']['oldlocation'])
				$_SESSION['_config']['oldlocation'] .= "&";

			$key = str_replace(array("\n", "\r"), '', $key);
			$val = str_replace(array("\n", "\r"), '', $val);
			$_SESSION['_config']['oldlocation'] .= "$key=$val";
		}
		$_SESSION['_config']['oldlocation'] = substr($_SERVER['SCRIPT_NAME'], 1)."?".$_SESSION['_config']['oldlocation'];
		$hostname=$_SERVER['HTTP_HOST'];
		$hostname = str_replace(array("\n", "\r"), '', $hostname);
		header("location: https://".$hostname."/index.php?id=4");
		exit;
	}
?>
