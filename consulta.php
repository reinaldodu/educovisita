<?php
require_once("funciones.php");	
require_once("conexionBD.php");
$link=conectarse(); 
//***Leer variables del sistema******
$estado=mysql_query("select * from general",$link);
$leer= mysql_fetch_array($estado);

//****** Verificamos si existe la cookie *****/
if(isset($_COOKIE['VisitaDatAdmin'])) {
	$resp=mysql_query("select * from dependencias",$link);
        while($row = mysql_fetch_array($resp)) {
                $dependencias[$row["id"]]=$row["nombre"];
        }
	
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
	echo '<html>';
	echo '<head>';
	echo '<title>'.$leer['institucion'].' - Consulta de visitantes</title>';
	echo '<link href="estilo3.css" rel="stylesheet" type="text/css" />';
	echo '</head>';
	echo '<body>';
	echo '<h1>'.$leer['institucion'].'</h1>';
	echo '<h2>CONSULTA DE VISITAS</h2>';
	echo '<div align="center">';
	//*****Seleccionar fecha********
	echo '<table><tr><td class="center">';
	echo '<form name="consulta" action="consulta.php" method="post">';
	echo '<strong>Seleccione una fecha </strong>';
	$ffdia=date("d");
	echo '<label for="fdia">Día:</label>';
	echo '<select name="fdia" title="Seleccione el día">';
	for($i=1;$i<=31;$i++) {
     		if ($i==$ffdia) {
			echo '<option value="'.$i.'" selected>'.$i.'</option>';
		}	
		else {
			echo '<option value="'.$i.'">'.$i.'</option>';
		}
	}
	echo '</select> ';

	$ffmes=date("m");
	echo '<label for="fmes">Mes:</label>';
	echo '<select name="fmes" title="Seleccione el mes">';
	for($i=1;$i<=12;$i++) {
     		if ($i==$ffmes) {
			echo '<option value="'.$i.'" selected>'.$i.'</option>';
		}	
		else {
			echo '<option value="'.$i.'">'.$i.'</option>';
		}
	}
	echo '</select> ';

	$ffano=date("Y");
	echo '<label for="fano">Año:</label>';
	echo '<select name="fano" title="Seleccione el año">';
	for($i=$ffano;$i>=1990;$i--) {
     		echo '<option value="'.$i.'">'.$i.'</option>';
	}
	echo '</select> ';
	echo '<input type="submit" name="envia_reg" value="Ok" title="Ver registros de la fecha seleccionada" /></form>';
	echo '</td></tr></table>';
	//*****Muestra tabla con registros del sistemas *******
	if (isset($_POST['envia_reg']) or isset($_GET['orden'])) {
	$ContReg=0;
	$fecha=$_POST['fano'].'-'.$_POST['fmes'].'-'.$_POST['fdia'];
	if (isset($_GET['orden'])){
		$fecha=$_GET['fecha'];
	}
	echo '<br /><p class="cen"><strong>Registros fecha:'.$fecha;
	echo '</strong></p><table>';
	echo '<thead><tr><th>No.</th>';
	echo '<th><a class="orden" href="consulta.php?fecha='.$fecha.'&orden=hora" title="Ordenar por hora">Hora</a></th>';
	echo '<th><a class="orden" href="consulta.php?fecha='.$fecha.'&orden=visitante" title="Ordenar por visitante">Visitante</a></th>';
	echo '<th><a class="orden" href="consulta.php?fecha='.$fecha.'&orden=documento" title="Ordenar por documento">Documento</a></th>';
	echo '<th><a class="orden" href="consulta.php?fecha='.$fecha.'&orden=dependencia" title="Ordenar por dependencia">Dependencia</a></th>';
	// Verificar si el usuario es administrador
	$verifica=mysql_query(sprintf("select tipo from acceso WHERE id=%d",$_COOKIE['VisitaDatAdmin']),$link);
	$es_adm= mysql_fetch_array($verifica);

	if ($es_adm['tipo']=="ADM") {
		echo '<th colspan="3">Opciones</th></tr></thead>';
	}
	else {
		echo '<th colspan="2">Opciones</th></tr></thead>';
	}
	//Consultas de acuerdo al criterio de orden
	if ($_GET['orden']=="hora" or !isset($_GET['orden'])) {
		$resp=mysql_query(sprintf("select hora,nombres,apellidos,documento,id_dependencia, registro.id as id  from registro,visitas where fecha=%s and registro.id_visita=visitas.id ORDER by hora",comillas($fecha)),$link);
	}
	if ($_GET['orden']=="visitante") {
		$resp=mysql_query(sprintf("select hora,nombres,apellidos,documento,id_dependencia, registro.id as id  from registro,visitas where fecha=%s and registro.id_visita=visitas.id ORDER by nombres,apellidos",comillas($fecha)),$link);
	}
	if ($_GET['orden']=="documento") {
		$resp=mysql_query(sprintf("select hora,nombres,apellidos,documento,id_dependencia, registro.id as id  from registro,visitas where fecha=%s and registro.id_visita=visitas.id ORDER by documento",comillas($fecha)),$link);
	}
	if ($_GET['orden']=="dependencia") {
		$resp=mysql_query(sprintf("select hora,nombres,apellidos,documento,id_dependencia, registro.id as id  from registro,visitas where fecha=%s and registro.id_visita=visitas.id ORDER by id_dependencia,hora",comillas($fecha)),$link);
	}

	while($row = mysql_fetch_array($resp)) {
		$ContReg=$ContReg+1;
		echo '<tr>';	
		echo '<td class="cen">'.$ContReg.'</td>';
		echo '<td class="cen">'.$row['hora'].'</td>';
		echo '<td>'.$row['nombres'].' '.$row['apellidos'].'</td>';
		echo '<td class="cen">'.$row['documento'].'</td>';
		echo '<td>'.$dependencias[$row['id_dependencia']].'</td>';

		echo '<td><a href="detallado.php?id='.md5($row['id']).'" title="Clic para ver reporte detallado de la visita"><img src="iconos/preview.gif" width="20px" border="0" alt="Detallado" /></a></td>';
		echo '<td><a href="editar.php?id='.md5($row['id']).'" title="Clic para editar la visita"><img src="iconos/lapiz.png" width="20px" border="0" alt="Editar" /></a></td>';
		if ($es_adm['tipo']=="ADM") {	
			echo '<td><a href="borrar.php?id='.md5($row['id']).'" title="Clic para eliminar la visita"><img src="iconos/delete.png" border="0" alt="Eliminar" /></a></td>';
		}
		echo '</tr>';	
	}
	if($ContReg==0) {
		echo '<tr><td class="cen" colspan="8"><strong>No existen registros para esta fecha</strong></td></tr>';
	}
	}
	echo '</table><br />';
	echo '</div>';
	echo '</body>';
	echo '</html>';
}
else {
	include_once("encabezado.html");
      	echo '<table>';
        echo '<tr><td class="cen"><strong>Su sesión ha finalizado, por favor vuelva a ingresar al sistema</strong></td></tr>';
        echo '</table></div></body></html>';
}
mysql_close($link);
?>
