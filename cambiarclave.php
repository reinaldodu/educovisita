<?php
require_once("funciones.php");	
require_once("conexionBD.php");
$link=conectarse(); 
//****** Verificamos si existe la cookie *****/
if(isset($_COOKIE['VisitaDatAdmin'])) {
if (!isset($_POST['envia_cambioclave'])) {	
	include_once("cambiarclave.html");	
}
else {	

//*****************************************************
// VALIDAMOS ALGUNOS VALORES EN LA BD ANTES DE GUARDAR
//*****************************************************

//Validar los campos requeridos
valida(array("requerido"=>"clave_actual,clave1_nueva,clave2_nueva"));

if (strlen($_POST['clave1_nueva']) < 4) {
        include_once("encabezado.html");
	print "<strong>La contrase�a debe ser como m�nimo de 4 caracteres<br />";
        print"<br /><a href='javascript:history.go(-1)'>Volver al formulario</a></strong></div></body></html>";
        exit;
}

if ($_POST['clave1_nueva']!=$_POST['clave2_nueva']) {
        include_once("encabezado.html");
        print "<strong>La confirmaci�n de la contrase�a est� mal escrita<br />";
        print"<br /><a href='javascript:history.go(-1)'>Volver al formulario</a></strong></div></body></html>";
        exit;
}
$fclave=md5($_POST['clave1_nueva']);
$fclave_actual=md5($_POST['clave_actual']);

//********************************
// GUARDAMOS LOS DATOS EN LA BD
//********************************

//$tipo="ADM";
$resp=mysql_query(sprintf("select id from acceso where md5(id)=%s and password=%s",comillas($_POST['identificador']),comillas($fclave_actual)),$link);
if ($row = mysql_fetch_array($resp)) {
	$id_adm=$row['id'];
}
else {
        include_once("encabezado.html");
        print "<strong>La contrase�a actual no corresponde<br />";
       	print"<br /><a href='javascript:history.go(-1)'>Volver al formulario</a></strong></div></body></html>";
       	exit;
}

//******Guardamos los datos de acceso ******
$cons_sql  = sprintf("UPDATE acceso SET password=%s WHERE id=%d", comillas($fclave), $id_adm);
mysql_query($cons_sql,$link);

//******Guardamos los datos de control ******
$ffecha=date("Y-m-d");
$fhora=date("G:i:s");
$fip = $_SERVER['REMOTE_ADDR']; 
$faccion="Admin_CambioClave";
$cons_sql2  = sprintf("INSERT INTO control(fecha,hora,ip,accion,id_usuario) VALUES(%s,%s,%s,%s,%d)", comillas($ffecha), comillas($fhora), comillas($fip), comillas($faccion),$id_adm);
mysql_query($cons_sql2,$link);
include_once("confirma2.html");	
mysql_close($link);
}
}
else {
        include_once("encabezado.html");
        echo '<table>';
        echo '<tr><td class="cen"><strong>Su sesi�n ha finalizado, por favor vuelva a ingresar al sistema</strong></td></tr>';
        echo '</table></div></body></html>';
}
?>
