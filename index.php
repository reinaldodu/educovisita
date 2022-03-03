<?php
require_once("funciones.php");
require_once("conexionBD.php");
$link=conectarse();
//***Leer variables del sistema******
$estado=mysql_query("select * from general",$link);
$leer= mysql_fetch_array($estado);
if (!isset($_POST['envia_acceso'])) {
        include_once("ingreso.html");
}
else {

//******VALIDACION DE INGRESO AL SISTEMA******
if ($_POST['usuario']=="") {
	include_once("encabezado.html");
	print "<strong>No ha escrito el nombre de usuario<br />";
	print"<br /><a href='javascript:history.go(-1)'>Volver al formulario</a></strong></div></body></html>";
	exit;
}

if ($_POST['clave']=="") {
	include_once("encabezado.html");
	print "<strong>No ha escrito la contraseña de acceso<br />";
	print"<br /><a href='javascript:history.go(-1)'>Volver al formulario</a></strong></div></body></html>";
	exit;
}
$clave=md5($_POST['clave']);

//******Funcion para guardar los datos de control ******
function LogControl($faccion, $iduser) {
	require_once("conexionBD.php");
	$link=conectarse();
	$ffecha=date("Y-m-d");
	$fhora=date("G:i:s");
	$fip = $_SERVER['REMOTE_ADDR'];
	$cons_sql  = sprintf("INSERT INTO control(fecha,hora,ip,accion,id_usuario) VALUES(%s,%s,%s,%s,%d)", comillas($ffecha), comillas($fhora), comillas($fip), comillas($faccion),$iduser);
	mysql_query($cons_sql,$link);
} 
	
	$resp=mysql_query(sprintf("select id,tipo,nombres,apellidos from acceso where usuario=%s and password=%s",comillas($_POST['usuario']),comillas($clave)),$link);
	if ($row= mysql_fetch_array($resp)) {
		//**** Creamos la cookie
		setcookie("VisitaDatAdmin", $row['id'], time()+9600);
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
		echo '<html>';
		echo '<head>';
		echo '<title>'.$leer['institucion'].' - Administración EducoVisita</title>';
		echo '<link href="estilo4.css" rel="stylesheet" type="text/css" />';
		echo '</head>';
		echo '<body>';
        include_once("java.html");
		echo '<div align="center">';
		$faccion="Ingreso-".$_POST['usuario'];
		LogControl($faccion,$row['id']);
		echo '<h2>EDUCOVISITA - BIENVENIDO(A): '.$row['nombres'].' '.$row['apellidos'].'</h2>';
		echo '<table style="font-weight:bold";>';
		echo '<thead><tr><th>CONTROL DE VISITAS</th></tr></thead>';
		echo '<tr><td><a href="javascript:NuevaVentana(\'registro.php\')" title="Registrar nuevo visitante"><img src="iconos/foto.png" border="0" alt="Folder" /> Nuevo visitante</a></td></tr>';
		echo '<tr><td><a href="javascript:NuevaVentana(\'registro2.php\')" title="Registrar por documento"><img src="iconos/datos.png" border="0" alt="Folder" /> Registrar por documento</a></td></tr>';
		echo '<tr><td><a href="javascript:NuevaVentana(\'consulta.php\')" title="Consulta de visitas"><img src="iconos/tiempo.png" border="0" alt="Folder" /> Consulta de visitas</a></td></tr>';
		echo '<tr><td><a href="javascript:NuevaVentana(\'visitante.php\')" title="Consultar visitante"><img src="iconos/folder.png" border="0" alt="Folder" /> Consultar visitante</a></td></tr>';
		if ($row['tipo']=="ADM") {
			echo '<tr><td><a href="javascript:NuevaVentana(\'administradores.php?id='.md5($row['id']).'\')" title="Usuarios del sistema"><img src="iconos/users.png" border="0" alt="Datos" /> Usuarios del sistema</a></td></tr>';
            		echo '<tr><td><a href="javascript:NuevaVentana(\'configuraciones.php\')" title="Configuración general"><img src="iconos/hoja.png" border="0" alt="Config" /> Configuración general</a></td></tr>';
			echo '<tr><td><a href="javascript:NuevaVentana(\'bitacora.php\')" title="Bitácora del sistema"><img src="iconos/find.png" border="0" alt="bitácora" /> Bitácora del sistema</a></td></tr>';
			echo '<tr><td><a href="javascript:NuevaVentana(\'cambiarclave.php?id='.md5($row['id']).'\')" title="Cambiar contraseña de acceso"><img src="iconos/clave.png" border="0" alt="Clave" /> Cambiar contraseña</a></td></tr>';
		}
		echo '<tr><td><a href="salir.php" title="Salir del sistema"><img src="iconos/salir.png" border="0" alt="Salir" /> Salir del sistema</a></td></tr>';
		echo '</table>';
		echo '</div>';
		echo '</body>';
		echo '</html>';

	}
	else {
		setcookie("VisitaDatAdmin", "", time()-3600);
		include_once("encabezado.html");
		$faccion="Fallido_".$_POST['usuario'];
		LogControl($faccion,0);
		echo '<table>';
		echo '<tr><td class="cen" colspan="2"><strong>Datos de ingreso inválidos<br /><br />';
		echo '<a href="javascript:history.go(-1)">Volver a intentar</a></strong></td></tr>';
		echo '</table></div></body></html>';
	}
	mysql_close($link);
}
?>
