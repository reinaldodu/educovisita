<?php
require_once("funciones.php");	
require_once("conexionBD.php");
$link=conectarse(); 

//****** Verificamos si existe la cookie *****/
if(isset($_COOKIE['VisitaDatAdmin'])) {
if (!isset($_POST['envia_actualizacion'])) {	
	$resp=mysql_query("select * from dependencias order by nombre",$link);	
	while($row5 = mysql_fetch_array($resp)) {
		$dependencias[$row5["id"]]=$row5["nombre"];
	}
	include_once("editar.html");	
}
else {	

//********************************
// DAR FORMATO A LOS DATOS
//********************************
	$fmotivo=borra_espacios($_POST['mot_vis']);
	$ftarjeta=borra_espacios($_POST['tar_vis']);
	$fobservaciones=borra_espacios($_POST['obs_vis']);
	$frecibe=borra_espacios($_POST['rec_vis']);
	$fdependencia=$_POST['dep_vis'];
	$id_vis=$_POST['identificador'];
	
//********************************
// GUARDAMOS LOS DATOS EN LA BD
//********************************

$cons_sql  = sprintf("UPDATE registro SET motivo=%s,tarjeta=%s,observaciones=%s,recibe=%s,id_dependencia=%d WHERE md5(id)=%s", comillas($fmotivo), comillas($ftarjeta), comillas($fobservaciones), comillas($frecibe),$fdependencia,comillas($id_vis));
mysql_query($cons_sql,$link);

//******Guardamos los datos de control ******
$ffecha=date("Y-m-d");
$fhora=date("G:i:s");
$fip = $_SERVER['REMOTE_ADDR']; 
$faccion="Edita_Visita (Doc:".$_POST['doc_vis']."/".$_POST['date_vis']."/".$_POST['time_vis'].")";
$cons_sql6  = sprintf("INSERT INTO control(fecha,hora,ip,accion,id_usuario) VALUES(%s,%s,%s,%s,%d)", comillas($ffecha), comillas($fhora), comillas($fip), comillas($faccion),$_COOKIE['VisitaDatAdmin']);
mysql_query($cons_sql6,$link);
include_once("confirma2.html");	
mysql_close($link);
}
}
else {
        include_once("encabezado.html");
        echo '<table>';
        echo '<tr><td class="cen"><strong>Su sesión ha finalizado, por favor vuelva a ingresar al sistema</strong></td></tr>';
        echo '</table></div></body></html>';
}
?>
