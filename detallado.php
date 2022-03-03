<?php
require_once("funciones.php");	
require_once("conexionBD.php");
$link=conectarse(); 

//***Leer variables del sistema******
$estado=mysql_query("select * from general",$link);
$leer= mysql_fetch_array($estado);

//****** Verificamos si existe la cookie *****/
if(isset($_COOKIE['VisitaDatAdmin'])) {
if(isset($_GET['id'])) {
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
	echo '<html>';
	echo '<head>';
	echo '<style type="text/css" media="print"> .nover {display:none}</style>';
	echo '<title>'.$leer['institucion'].' - Consulta visitas</title>';
	echo '<link href="estilo2.css" rel="stylesheet" type="text/css" />';
	echo '</head>';
	echo '<body>';
	$resp=mysql_query(sprintf("select * from registro,visitas,dependencias where md5(registro.id)=%s and registro.id_visita=visitas.id and registro.id_dependencia=dependencias.id",comillas($_GET['id'])),$link);
	if ($row = mysql_fetch_array($resp)) {
		echo '<h1>'.$leer['institucion'].'</h1>';
		echo '<h2>CONSULTA DE VISITAS</h2>';

		// **** Mostrar foto del visitante ****
		echo '<div>';
		$dir_imagenes="fotos";
		$nombre_imagen=$row['id_visita'];
		if (file_exists($dir_imagenes."/".$nombre_imagen.".jpg")) {
			escala($dir_imagenes.'/'.$nombre_imagen.'.jpg',250);
		}
		echo '</div>';

		// **** Mostrar datos visitante ***** 
		echo '<table>';
		echo '<thead><tr><th colspan="3">DATOS DEL VISITANTE</th></tr></thead>';
		
		echo '<tr>';
		echo '<td><strong>Fecha de visita</strong><br />';
		echo $row['fecha'].'</td>';
		echo '<td colspan="2"><strong>Hora</strong><br />';
		echo $row['hora'].'</td>';
		echo '</tr>';

		echo '<tr>';
		echo '<td><strong>Nombres y apellidos</strong><br />';
		echo $row['nombres'].' '.$row['apellidos'].'</td>';
		echo '<td><strong>Documento</strong><br />';
		echo $row['documento'].'</td>';		
		echo '<td><strong>Empresa</strong><br />';
		echo $row['empresa'].'</td>';
		echo '</tr>';

		echo '<tr>';
		echo '<td><strong>Telefono</strong><br />';
		echo $row['telefono'].'</td>';
		echo '<td><strong>Tarjeta #</strong><br />';
		echo $row['tarjeta'].'</td>';
		echo '<td><strong>Motivo ingreso</strong><br />';
		echo $row['motivo'].'</td>';
                echo '</tr>';

		echo '<tr>';
		echo '<td><strong>Dependencia</strong><br />';
		echo $row['nombre'].'</td>';
		echo '<td><strong>Persona que visita</strong><br />';
		echo $row['recibe'].'</td>';
		echo '<td><strong>Observaciones</strong><br />';
		echo $row['observaciones'].'</td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td colspan="3">';
		echo '<div class="nover cen">';
		echo '<br /><input type="button" name="imprime2" value="Imprimir" title="Imprimir datos visitante" onclick="window.print();">';
		echo '</div>';
		echo '</td>';
		echo '</tr></table>';
	}
	else {
		echo '<table>';
		echo '<tr><td class="cen" colspan="2"><strong>No hay datos para este visitante</strong></td></tr>';
		echo '</table>';
	}
	echo '</body>';
	echo '</html>';
}
}
else {
        include_once("encabezado.html");
        echo '<table>';
        echo '<tr><td class="cen"><strong>Su sesión ha finalizado, por favor vuelva a ingresar al sistema</strong></td></tr>';
        echo '</table></div></body></html>';
}
mysql_close($link);
?>
