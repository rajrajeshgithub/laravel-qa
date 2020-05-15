<?PHP session_start(); ?>
<?PHP
	$pageName;
	$str_haction;
	$str_variableName;
	$str_variableValue;
	$str_removeVariableName;
	pageLoad();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Config Variables</title>
<style type="text/css">
body{font-family:Verdana, Geneva, sans-serif;font-size:10px;}
input{padding:0px;margin:0px;}

#tbl_main{width:60%;border:solid 1px #666666;}
#tbl_main th{background:#666666;padding:5px;color:#FFFFFF;}
#tbl_main td{border:solid 1px #666666;padding:10px;vertical-align:top;text-align:center;}

#td_applicationSection{width:50%;}
#td_sessionSection{width:50%;}
#tbl_variable{width:100%}
#tbl_variable{border:solid 1px #BBBBBB;width:100%;}
#tbl_variable tr:hover td{background:#CCCCCC;}
#tbl_variable th{background:#BBBBBB;padding:1px 3px 1px 3px;color:#000000;white-space:nowrap;}
#tbl_variable td{background:#DDDDDD;padding:1px 3px 1px 3px;color:#000000;border:0px;text-align:left;vertical-align:middle;}
#tbl_variable .txt{font-size:9px;padding:0px 2px 0px 2px;width:90%;}
#tbl_variable .btn{font-size:9px;padding:0px 2px 0px 2px;width:50px;}

#td_actionSection{}
#td_actionSection input{padding:2px;margin:2px;;width:220px;}
</style>
<script language="javascript" type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
<script language="javascript" type="text/javascript">
function validate_form(frmObj)
{
	if(frmObj.find("#txt_name").val()=="")
	{
		alert("Enter Variable Name");
		frmObj.find("#txt_name").focus();
		return(false);
	}
	if(frmObj.find("#txt_value").val()=="")
	{
		alert("Enter Variable Value");
		frmObj.find("#txt_value").focus();
		return(false);
	}
	return(true);
}

$(document).ready(function()
{
	$("#bottomRow #debugger").click();
	$("#bottomRow .main span").html("Debugger");
	
	$(".btnAction").click(function()
	{
		submitForm(this.form, "cvariables.php", "post", $(this).attr("haction"));
	});
	
	$(".btnDelete").click(function()
	{
		$(this.form).find("#txt_removeName").val($(this).closest("tr").find("#txt_variableName").val());
		submitForm(this.form, "cvariables.php", "post", $(this).attr("haction"));
	});
	
	$(".btnAdd").click(function()
	{
		if(validate_form($(this.form)))
		{
			submitForm(this.form, "cvariables.php", "post", $(this).attr("haction"));
		}
	});
});

function submitForm(frmObj,formAction,formMethod,haction)
{ 
	var frmObj;
	//frmObj=document.getElementById(formId);
	frmObj.haction.value=haction;
	frmObj.action=formAction;
	frmObj.method=formMethod;
	frmObj.submit();
}
</script>
</head>
<body>
	<?PHP showPage(); ?>
</body>
</html>


<?PHP
function pageLoad()
{
	getValues();
	pageAction();
}

function getValues()
{
	global $pageName,$str_haction,$str_variableName,$str_variableValue,$str_removeVariableName;
	
	$pageName = "cvariables.php";
	$str_haction = (isset($_REQUEST["haction"])?$_REQUEST["haction"]:"");
	$str_variableName = (isset($_REQUEST["txt_name"])?$_REQUEST["txt_name"]:"");
	$str_removeVariableName = (isset($_REQUEST["txt_removeName"])?$_REQUEST["txt_removeName"]:"");
	$str_variableValue = (isset($_REQUEST["txt_value"])?$_REQUEST["txt_value"]:"");
	
}

function pageAction()
{	
	global $str_haction;
	switch($str_haction)
	{
		case "addSessionVariable": handle_addSessionVariable();break;
		case "addCookieVariable": handle_addCookieVariable();break;
		
		case "removeSessionVariable": handle_removeSessionVariable();break;
		case "removeCookieVariable": handle_removeCookieVariable();break;
		
		case "removeAllSessionVariables": handle_removeAllSessionVariables();break;
		case "removeAllCookieVariables": handle_removeAllCookieVariables();break;
		case "removeAllVariables": handle_removeAllVariables();break;
	}
}
?>


<?PHP function showPage()
{
	global $pageName;
	?>
	<table border="0" cellspacing="3" cellpadding="0" align="center" id="tbl_main">
	  <tr>
		<th colspan="2">CONFIG VARIABLES</th>
	  </tr>
	  <tr>
		<td id="td_sessionSection">
			<form id="frm_session" name="frm_session">
				<input type="hidden" id="haction" name="haction" />
                <input type="hidden" id="txt_removeName" name="txt_removeName" />
				<?PHP showSessionVariables(); ?>
			</form>
		</td>
        <td id="td_cookieSection">
			<form id="frm_cookie" name="frm_cookie">
				<input type="hidden" id="haction" name="haction" />
                <input type="hidden" id="txt_removeName" name="txt_removeName" />
				<?PHP showCookieVariables() ?>
			</form>
		</td>
	  </tr>
	  <tr>
		<td colspan="2" id="td_actionSection">
			<form id="frm_action" name="frm_action">
				<input type="hidden" id="haction" name="haction" />
				<input type="button" value="Remove All Session Variables" class="btnAction" haction="removeAllSessionVariables" /><br />
				<input type="button" value="Remove All Cookie Variables" class="btnAction" haction="removeAllCookieVariables" /><br />
				<input type="button" value="Remove All Config Variables" class="btnAction" haction="removeAllVariables" /><br /><br />
				<input type="button" value="Refresh" onclick="window.location='<?PHP echo($pageName); ?>'" />
			</form>
		</td>
	  </tr>
	  <tr>
		<td colspan="2" style="text-align:left">
        	<strong>Root path : </strong><?PHP echo($_SERVER['DOCUMENT_ROOT']."/"); ?><br />
            <strong>Current path : </strong><?PHP echo(realpath("")); ?><br />
			<strong>Gateway Interface : </strong><?PHP echo($_SERVER['GATEWAY_INTERFACE']); ?><br />
            <strong>Host Name : </strong><?PHP echo($_SERVER['HTTP_HOST']); ?><br />
            <strong>Host IP : </strong><?PHP echo($_SERVER['REMOTE_ADDR']); ?><br />
            <strong>Browser Type: </strong><?PHP echo($_SERVER['HTTP_USER_AGENT']); ?><br />
            <strong>Remote Port : </strong><?PHP echo($_SERVER['REMOTE_PORT']); ?><br />
            <strong>Server Port : </strong><?PHP echo($_SERVER['SERVER_PORT']); ?><br />
            <strong>Server Software : </strong><?PHP echo($_SERVER['SERVER_SOFTWARE']); ?><br />
            <strong>Server Signature : </strong><?PHP echo($_SERVER['SERVER_SIGNATURE']); ?><br />
        </td>
	  </tr>
	</table>
	<?PHP
	}
?>


<?PHP
function showSessionVariables()
{
	$html;
	$str_session;
	$cnt;
	global $pageName;
	
	$cnt = count($_SESSION);
	$html = "<table id='tbl_variable' align='center' cellspacing='1' cellpadding='0'>";
	$html = $html . "<tr><th colspan='3'>" . $cnt . "&nbsp;Session Variables</th></tr>";
	
	if($_SESSION)
	{
		foreach($_SESSION as $str_session => $val)
		{
			if(is_string($val))
			{
				$html = $html . "<tr><td width='30%'>" . $str_session . "<input type='hidden' id='txt_variableName' name='txt_variableName' value='". $str_session ."' /></td><td>" . $val . "</td><td width='1'><input type='button' class='btn btnDelete' value='Remove' haction='removeSessionVariable' /></td></tr>";	
			}
			elseif(is_array($val))
			{
				$html = $html . "<tr><td width='30%'>" . $str_session . "<input type='hidden' id='txt_variableName' name='txt_variableName' value='". $str_session ."' /></td><td><pre>" . print_r($val, 1) . "</pre></td><td width='1'><input type='button' class='btn btnDelete' value='Remove' haction='removeSessionVariable' /></td></tr>";	
			}
		}
	}
	$html = $html . "<tr><td><input type='text' class='txt' id='txt_name' name='txt_name' placeholder='Name' /></td><td><input type='text' class='txt' id='txt_value' name='txt_value' placeholder='Value' /></td><td width='1'><input type='button' class='btn btnAdd' value='Add' haction='addSessionVariable' /></td></tr>";
	$html = $html . "</table>";
	echo($html);
}

function showCookieVariables()
{
	$html;
	$str_cookie;
	$cnt;
	global $pageName;
	
	$cnt = count($_COOKIE);
	$html = "<table id='tbl_variable' align='center' cellspacing='1' cellpadding='0'>";
	$html = $html . "<tr><th colspan='3'>" . $cnt . "&nbsp;Cookie Variables</th></tr>";
	
	if($_COOKIE)
	{
		foreach ($_COOKIE as $str_cookie => $val)
		{
			$html = $html . "<tr><td width='30%'>" . $str_cookie . "<input type='hidden' id='txt_variableName' name='txt_variableName' value='". $str_cookie ."' /></td><td>" . $val . "</td><td width='1'><input type='button' class='btn btnDelete' value='Remove' haction='removeCookieVariable' /></td></tr>";
		}
	}
	$html = $html . "<tr><td><input type='text' class='txt' id='txt_name' name='txt_name' placeholder='Name' /></td><td><input type='text' class='txt' id='txt_value' name='txt_value' placeholder='Value' /></td><td width='1'><input type='button' class='btn btnAdd' value='Add' haction='addCookieVariable' /></td></tr>";
	$html = $html . "</table>";
	echo($html);
}


/*---------Add One Variable----------*/
function handle_addSessionVariable()
{
	global $str_variableName,$str_variableValue,$pageName;
	
	if($str_variableName != "" && $str_variableValue != "")
	{
		$_SESSION[$str_variableName] = $str_variableValue;
	}
	header("location:cvariables.php");
	exit();
}

function handle_addCookieVariable()
{
	global $str_variableName,$str_variableValue,$pageName;
	
	if($str_variableName != "" && $str_variableValue !="")
	{
		$_COOKIE[$str_variableName] = $str_variableValue;
		setCookie($str_variableName, $str_variableValue, "/");
	}
	header("location:cvariables.php");
	exit();
}
/*-----------------------------------*/


/*--------Remove One Variable--------*/

function handle_removeSessionVariable()
{
	global $str_removeVariableName;
	unset($_SESSION[$str_removeVariableName]);
	header("location:cvariables.php");
	exit();
}

function handle_removeCookieVariable()
{
	global $str_removeVariableName;
	setCookie($str_removeVariableName, "", time()-3600, "/");
	header("location:cvariables.php");
	exit();
}
/*-----------------------------------*/


/*--------Remove All Variable--------*/

function handle_removeAllSessionVariables()
{
	session_destroy();
	header("location:cvariables.php");
	exit();
}

function handle_removeAllCookieVariables()
{
	foreach ($_COOKIE as $str_cookie => $val)
	{
		setCookie($str_cookie, "", time()-3600, "/");
	}
	header("location:cvariables.php");
	exit();
}

function handle_removeAllVariables()
{
	session_destroy();
	foreach ($_COOKIE as $str_cookie => $val)
	{
		setCookie($str_cookie, "", time()-3600, "/");
	}
	header("location:cvariables.php");
	exit();
}
/*-----------------------------------*/
?>