<?

// get settings
$NETCAT_FOLDER = join(strstr(__FILE__, "/") ? "/" : "\\", array_slice(preg_split("/[\/\\\]+/", __FILE__), 0, -6)).( strstr(__FILE__, "/") ? "/" : "\\" );

require_once ($NETCAT_FOLDER."vars.inc.php");
require_once ($ADMIN_FOLDER."function.inc.php");
//require_once ($MODULE_FOLDER."cache/function.inc.php");
//require_once ($MODULE_FOLDER."cache/nc_cache_admin.class.php");

require_once(dirname(__FILE__).'/pBanner.class.php');

if (!empty($_GET['id'])) {
	$id = $_GET['id']*1;

	$b = new pBanner(2011);
	$b->redirect($id);
	

}