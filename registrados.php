<?php
require_once("funciones.php");	
require_once("conexionBD.php");
$link=conectarse(); 
//***Leer variables del sistema******
$estado=mysql_query("select * from general",$link);
$leer= mysql_fetch_array($estado);

//****** Verificamos si existe la cookie *****/
if(isset($_COOKIE['VisitaDatAdmin'])) {
	// Verificar si el usuario es administrador
        $verifica=mysql_query(sprintf("select tipo from acceso WHERE id=%d",$_COOKIE['VisitaDatAdmin']),$link);
        $es_adm= mysql_fetch_array($verifica);
echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
        echo '<html>';
        echo '<head>';
        echo '<title>'.$leer['institucion'].' - Consulta de visitantes</title>';
        echo '<link href="estilo3.css" rel="stylesheet" type="text/css" />';
        echo '</head>';
        echo '<body>';
        echo '<h1>'.$leer['institucion'].'</h1>';
        echo '<h2>VISITANTES REGISTRADOS</h2>';
        echo '<div align="center">';
	//******Configuración de total de registros por página*****
	$paginas = 50;

	$actual = (!isset ($_GET['pg']))?1:$_GET['pg'];
	$sql = mysql_query ("SELECT * FROM visitas");
	$total = mysql_num_rows ($sql);
	if ($actual == 1) {
		$desde = "0";
	}
	elseif ($actual != 1) {
		$desde = $actual * $paginas - $paginas;
	}
	$tp = ($total / $paginas);
	if (strstr($tp,'.')){ 
		$tp = explode (".",$tp);
		$tp = ($tp[0]+1);
	}
	$resp = mysql_query ("SELECT * FROM visitas ORDER BY apellidos LIMIT ".$desde.",".$paginas."");
	//Tabla con los registros de visitantes
	echo '<table>';
	echo '<thead><tr><th>No.</th>';
	echo '<th>Visitante</th>';
	echo '<th>Documento</th>';
	if ($es_adm['tipo']=="ADM") {
		echo '<th colspan="3">Opciones</th></tr></thead>';
	}
	else {
		echo '<th colspan="2">Opciones</th></tr></thead>';
	}
	$ContFila=($actual-1)*$paginas;
	while ($row = mysql_fetch_array ($resp)) {
		$ContFila=$ContFila+1;
		echo '<tr>';
		echo '<td>'.$ContFila.'</td>';
		echo '<td>'.$row["nombres"].' '.$row["apellidos"].'</td>';
		echo '<td>'.$row["documento"].'</td>';
		$dato=$row["nombres"].' '.$row["apellidos"].' - Doc:'.$row['documento'];
		echo '<td><a href="visitante.php?visitas='.md5($row['id']).'&datos='.$dato.'" title="Ver visitas para este visitante"><img src="iconos/preview.gif" width="20px" alt="Ver visitas" /></a></td>';
		echo '<td><a href="visitante.php?editar='.md5($row['id']).'" title="Editar"><img src="iconos/lapiz.png" width="20px" alt="Editar" /></a></td>';
		if ($es_adm['tipo']=="ADM") {
			echo '<td><a href="visitante.php?eliminar='.md5($row['id']).'" title="Eliminar"><img src="iconos/delete.png" alt="Eliminar" /></a></td>';
		}
		echo '</tr>';
	}
	echo '</table>';
	//Mostrar barra de paginado
	$pag = ($tp == 1) ? página : páginas;
	$reg = ($total == 1) ? registro : registros;
	echo 'Encontrados <strong>'.$total.'</strong> '.$reg.' en <strong> '.$tp.'</strong> '.$pag.'<br />';
	$anterior = true;
	$siguiente = true;
	if (($actual == 1) AND ($actual == $tp)) {
		$anterior = false;
		$siguiente = false;
	}
	elseif ($actual == $tp) {
		$anterior = true;
		$siguiente = false;
	}
	elseif ($actual == 1) {
		$anterior = false;
		$siguiente = true;
	}
	if ($anterior) {
		echo "<a href=\"registrados.php?pg=".($actual-1)."\">&lt; Página anterior</a> | ";
	}
	else {
		echo "|";
	}
	for ($i = 1; $i <= $tp;$i++) {
		if ($i == $actual) {
			echo " <b>".$i."</b> | ";
		}
		else {
			echo "<a href=\"registrados.php?pg=".$i."\"> ".$i."</a> |";
		}
	}
	if ($siguiente) {
		echo " <a href=\"registrados.php?pg=".($actual+1)."\"> Página siguiente &gt;</a>";
	}
}
else {
        include_once("encabezado.html");
        echo '<table>';
        echo '<tr><td class="cen"><strong>Su sesión ha finalizado, por favor vuelva a ingresar al sistema</strong></td></tr>';
        echo '</table></div></body></html>';
}
?>
