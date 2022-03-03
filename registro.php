<?php
require_once("funciones.php");	
require_once("conexionBD.php");
$link=conectarse(); 
//***Leer variables del sistema******
$estado=mysql_query("select * from general",$link);
$leer= mysql_fetch_array($estado);
//****** Verificamos si existe la cookie *****/
if(isset($_COOKIE['VisitaDatAdmin'])) {
if (!isset($_POST['envia_registro'])) {	
	$resp=mysql_query("select * from dependencias order by nombre",$link);
        while($row = mysql_fetch_array($resp)) {
                $dependencias[$row["id"]]=$row["nombre"];
        }
	//Borrar foto temporal.jpg si existe
	if (file_exists("fotos/temporal.jpg")){ 
		unlink("fotos/temporal.jpg");
	} 
	// Mostrar formulario de registro de visitas
	include_once("registro.html");	
}
else {	

//*****************************************************
// VALIDAMOS ALGUNOS VALORES EN LA BD ANTES DE GUARDAR
//*****************************************************


//Dar formato a los datos
$fnom_vis=cambia_mayuscula(borra_espacios($_POST['nom_vis']));
$fape_vis=cambia_mayuscula(borra_espacios($_POST['ape_vis']));
$fdoc_vis=borra_espacios($_POST['doc_vis']);
$fempr_vis=cambia_mayuscula(borra_espacios($_POST['empr_vis']));
$ftel_vis=borra_espacios($_POST['tel_vis']);
$fmot_vis=borra_espacios($_POST['mot_vis']);
$fdep_vis=$_POST['dep_vis'];
$ftar_vis=borra_espacios($_POST['tar_vis']);
$frec_vis=cambia_mayuscula(borra_espacios($_POST['rec_vis']));
$fobs_vis=borra_espacios($_POST['obs_vis']);
$ffecha=date("Y-m-d");
$fhora=date("G:i:s");
$fid_dep=$_POST['dep_vis'];

 //*****Validamos que no exista un documento duplicado****
$resp3=mysql_query(sprintf("select id from visitas  where documento=%s",comillas($fdoc_vis)),$link);
if($row3 = mysql_fetch_array($resp3)) {
	include_once("encabezado.html");
	print "<strong>Este documento ya se encuentra registrado en el sistema<br />";
	print"<br /><a href='javascript:history.go(-1)'>Volver al formulario</a></strong></div></body></html>";
	exit;
}

//Validar los campos requeridos
valida(array("requerido"=>"nom_vis,doc_vis"));

//********************************
// GUARDAMOS LOS DATOS EN LA BD
//********************************

//******Guardamos los datos del nuevo visitante ******
$cons_sql  = sprintf("INSERT INTO visitas(nombres,apellidos,documento,empresa,telefono) VALUES(%s,%s,%s,%s,%s)", comillas($fnom_vis), comillas($fape_vis), comillas($fdoc_vis), comillas($fempr_vis), comillas($ftel_vis));
mysql_query($cons_sql,$link);
//****obtener el id del visitante guardado
$id_vis=mysql_insert_id($link);

//******Guardamos los datos de registro ******
$cons_sql  = sprintf("INSERT INTO registro(fecha,hora,motivo,tarjeta,observaciones,recibe,id_dependencia,id_visita) VALUES(%s,%s,%s,%s,%s,%s,%d,%d)", comillas($ffecha), comillas($fhora), comillas($fmot_vis), comillas($ftar_vis), comillas($fobs_vis),comillas($frec_vis),$fid_dep,$id_vis);
mysql_query($cons_sql,$link);

//******Guardamos los datos de control ******
$fip = $_SERVER['REMOTE_ADDR']; 
$faccion="Registro_Visitante_Nuevo";
$cons_sql2  = sprintf("INSERT INTO control(fecha,hora,ip,accion,id_usuario) VALUES(%s,%s,%s,%s,%d)", comillas($ffecha), comillas($fhora), comillas($fip), comillas($faccion),$_COOKIE['VisitaDatAdmin']);
mysql_query($cons_sql2,$link);

// Guardamos la foto capturada
if (file_exists("fotos/temporal.jpg")){ 
	rename("fotos/temporal.jpg","fotos/". $id_vis .".jpg");
} 
include_once("confirma.html");	
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
