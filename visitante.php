<?php
require_once("funciones.php");	
require_once("conexionBD.php");
$link=conectarse(); 

//***Leer variables del sistema******
$estado=mysql_query("select * from general",$link);
$leer= mysql_fetch_array($estado);

echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
echo '<html>';
echo '<head>';
echo '<style type="text/css" media="print"> .nover {display:none}</style>';
echo '<title>'.$leer['institucion'].' - Consulta visitante</title>';
if (isset($_GET['visitas'])) {
	echo '<link href="estilo3.css" rel="stylesheet" type="text/css" />';
}
else {
	echo '<link href="estilo2.css" rel="stylesheet" type="text/css" />';
}
echo '</head>';
//****** Verificamos si existe la cookie *****/
if(isset($_COOKIE['VisitaDatAdmin'])) {
        // Verificar si el usuario es administrador
        $verifica=mysql_query(sprintf("select tipo from acceso WHERE id=%d",$_COOKIE['VisitaDatAdmin']),$link);
        $es_adm= mysql_fetch_array($verifica);
	$resp=mysql_query("select * from dependencias",$link);
        while($row4 = mysql_fetch_array($resp)) {
                $dependencias[$row4["id"]]=$row4["nombre"];
        }
	if (!isset($_POST['envia_documento'])) {
		if(!isset($_GET['editar']) and !isset($_GET['eliminar']) and !isset($_GET['visitas'])and !isset($_POST['edita_visitante'])) {
			echo '<body OnLoad="document.reg_doc.ver_doc.focus();">';
			echo '<h1>'.$leer['institucion'].'</h1>';
			echo '<h2>Consulta de visitante</h2>';
			echo '<form name="reg_doc" action="visitante.php" method="post">';
			echo '<table>';
			echo '<tr>';
			echo '<td class="cen"><label for="ver_doc">';
			echo '<strong>Documento:</strong>';
			echo '</label>';
			echo '<input type="text" name="ver_doc" size="50" maxlength="50" title="Escriba el número del documento del visitante" />';
			echo '</td></tr>';
			echo '<tr><td class="cen"><input type="submit" name="envia_documento" value="Consultar documento" title="Consultar documento" />';
			echo '</td></tr>';
			echo '</table></form>';
		}
	}
	else {
		$resp=mysql_query(sprintf("select * from visitas where documento=%s",comillas($_POST['ver_doc'])),$link);
		if ($row = mysql_fetch_array($resp)) {
			echo '<body>';
			echo '<h1>'.$leer['institucion'].'</h1>';
	                echo '<h2>CONSULTA DE VISITANTE</h2>';
	
        	        // **** Mostrar foto del visitante ****
			echo '<div>';
			$dir_imagenes="fotos";
			$nombre_imagen=$row['id'];
			if (file_exists($dir_imagenes."/".$nombre_imagen.".jpg")) {
				escala($dir_imagenes.'/'.$nombre_imagen.'.jpg',250);
			}
			echo '</div>';

	                // **** Mostrar datos visitante *****
        	        echo '<table>';
                	echo '<thead><tr><th colspan="2">DATOS DEL VISITANTE</th></tr></thead>';

	                echo '<tr>';
        	        echo '<td><strong>Nombres y apellidos</strong><br />';
                	echo $row['nombres'].' '.$row['apellidos'].'</td>';
	                echo '<td><strong>Documento</strong><br />';
        	        echo $row['documento'].'</td>';
	                echo '</tr>';
	                
	                echo '<tr>';
			echo '<td><strong>Telefono</strong><br />';
        	        echo $row['telefono'].'</td>';
	                echo '<td><strong>Empresa</strong><br />';
        	        echo $row['empresa'].'</td>';
	                echo '</tr>';
        	        echo '</table>';
        	        echo '<br />';
			$dato=$row['nombres']." ".$row['apellidos']." - Doc:".$row['documento'];
			echo '<div class="cen">';
			echo '<a href="visitante.php?visitas='.md5($row['id']).'&datos='.$dato.'" title="Ver visitas para este visitante"><img src="iconos/tiempo.png" width="35px" alt="Ver visitas" />Visitas</a>';
			echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			echo '<a href="visitante.php?editar='.md5($row['id']).'" title="Editar visitante"><img src="iconos/edit.png" alt="Editar" />Editar</a>';
			echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			if ($es_adm['tipo']=="ADM") {
				echo '<a href="visitante.php?eliminar='.md5($row['id']).'" title="Eliminar visitante"><img src="iconos/delete.png" alt="Eliminar" />Eliminar</a>';
			}
			echo '</div>';

		}
		else {
			include_once("encabezado.html");
			print "<strong>El documento no aparece registrado en el sistema<br />";
			print"<br /><a href='javascript:history.go(-1)'>Volver al formulario</a></strong></div></body></html>";
			exit;
		}
	}

	if (isset($_GET['editar'])) {
		 //Borrar foto temporal.jpg si existe
	        if (file_exists("fotos/temporal.jpg")){
        	        unlink("fotos/temporal.jpg");
	        }
		 $resp=mysql_query(sprintf("select * from visitas where md5(id)=%s",comillas($_GET['editar'])),$link);
                if ($row = mysql_fetch_array($resp)) {
			//Formulario de actualización de datos
			include_once("visitante.html");
		}
	}
	
	if (isset($_POST['edita_visitante'])) {
		//Dar formato a los datos
		$fnom_vis=cambia_mayuscula(borra_espacios($_POST['nom_vis']));
		$fape_vis=cambia_mayuscula(borra_espacios($_POST['ape_vis']));
		$fdoc_vis=borra_espacios($_POST['doc_vis']);
		$fempr_vis=cambia_mayuscula(borra_espacios($_POST['empr_vis']));
		$ftel_vis=borra_espacios($_POST['tel_vis']);
		$id_vis=$_POST['id_vis'];

		//Validar los campos requeridos
		valida(array("requerido"=>"nom_vis,doc_vis"));

		//******Actualizamos los datos del visitante ******
		$cons_sql  = sprintf("UPDATE visitas SET nombres=%s,apellidos=%s,documento=%s,empresa=%s,telefono=%s WHERE id=%d", comillas($fnom_vis), comillas($fape_vis), comillas($fdoc_vis), comillas($fempr_vis), comillas($ftel_vis),$id_vis);
		mysql_query($cons_sql,$link);
		//******Guardamos los datos de control ******
		$ffecha=date("Y-m-d");
		$fhora=date("G:i:s");
		$fip = $_SERVER['REMOTE_ADDR'];
		$faccion="Actualiza_Visitante Doc:".$fdoc_vis;
		$cons_sql2  = sprintf("INSERT INTO control(fecha,hora,ip,accion,id_usuario) VALUES(%s,%s,%s,%s,%d)", comillas($ffecha), comillas($fhora), comillas($fip), comillas($faccion),$_COOKIE['VisitaDatAdmin']);
		mysql_query($cons_sql2,$link);
		// Guardamos la foto capturada
		if (file_exists("fotos/temporal.jpg")){
		        rename("fotos/temporal.jpg","fotos/". $id_vis .".jpg");
		}
		include_once("confirma.html");
	}
	if (isset($_GET['eliminar'])) {
		$resp=mysql_query(sprintf("select * from visitas where md5(id)=%s",comillas($_GET['eliminar'])),$link);
                if ($row = mysql_fetch_array($resp)) {
			include_once("encabezado.html");

                        echo '<table><tr><td class="cen"><strong>¿Desea eliminar el visitante '.$row['nombres'].' '.$row['apellidos'].' identificado con documento # '.$row['documento'].'? ';
                        echo '<a href="visitante.php?eliminar='.$_GET['eliminar'].'&elimina=1" title="Eliminar visitante del sistema">Si</a>&nbsp&nbsp&nbsp&nbsp';
                        print"<a href='javascript:history.go(-1)'>No</a>";
                        echo '</strong></td></tr>';
                        echo '</table></div></body></html>';
                }
                else {
                        echo '<strong>No hay datos para este visitante</strong>';
                }
	}
	//*****Eliminar visitante******
        if((isset($_GET['eliminar']))and(isset($_GET['elimina']))and($_GET['elimina']=="1")) {
		//Eliminar visitante de la tabla visitas
                $resp2=mysql_query(sprintf("delete from visitas where md5(id)=%s",comillas($_GET['eliminar'])),$link);
		//Eliminar registros de visitas, de la tabla registro
		$resp3=mysql_query(sprintf("delete from registro where md5(id_visita)=%s",comillas($_GET['eliminar'])),$link);
		//Eliminar foto del visitante, si existe
	        if (file_exists("fotos/".$row['id'].".jpg")){
        	        unlink("fotos/".$row['id'].".jpg");
	        }

                //******Guardamos los datos de control ******
                $ffecha=date("Y-m-d");
                $fhora=date("G:i:s");
                $fip = $_SERVER['REMOTE_ADDR'];
                $faccion="Admin_Elimina_Visitante (Doc:".$row['documento'].")";
                $cons_sql  = sprintf("INSERT INTO control(fecha,hora,ip,accion,id_usuario) VALUES(%s,%s,%s,%s,%d)", comillas($ffecha), comillas($fhora), comillas($fip), comillas($faccion),$_COOKIE['VisitaDatAdmin']);
mysql_query($cons_sql,$link);
                //**** Redireccionar página web después de eliminar *****
                echo '<script type="text/javascript">';
                echo 'window.location="visitante.php"';
                echo '</script>';
        }

	//*****Muestra la lista de visitas realizadas por el visitante seleccionado *******
        if (isset($_GET['visitas']) or isset($_GET['orden'])) {
        	$ContReg=0;
	        $visitas=$_GET['visitas'];
		$dato=$_GET['datos'];
		echo '<body>';
		echo '<h1>'.$leer['institucion'].'</h1>';
		echo '<h2>VISITAS REALIZADAS POR: '.$dato.'</h2>';
	        echo '<div align="center">';
		echo '</strong></p><table>';
        	echo '<thead><tr><th>No.</th>';
	        echo '<th><a class="orden" href="visitante.php?visitas='.$visitas.'&orden=fecha&datos='.$dato.'" title="Ordenar por fecha">Fecha</a></th>';
        	echo '<th><a class="orden" href="visitante.php?visitas='.$visitas.'&orden=hora&datos='.$dato.'" title="Ordenar por hora">Hora</a></th>';
	        echo '<th><a class="orden" href="visitante.php?visitas='.$visitas.'&orden=dependencia&datos='.$dato.'" title="Ordenar por dependencia">Dependencia</a></th>';

        	if ($es_adm['tipo']=="ADM") {
                	echo '<th colspan="3">Opciones</th></tr></thead>';
	        }
        	else {
                	echo '<th colspan="2">Opciones</th></tr></thead>';
	        }
        	//Consultas de acuerdo al criterio de orden
		if ($_GET['orden']=="fecha" or !isset($_GET['orden'])) {
        	        $resp=mysql_query(sprintf("select * from registro where md5(id_visita)=%s ORDER by fecha,hora",comillas($visitas)),$link);
	        }
		if ($_GET['orden']=="hora") {
                	$resp=mysql_query(sprintf("select * from registro where md5(id_visita)=%s ORDER by hora",comillas($visitas)),$link);
	        }
        	if ($_GET['orden']=="dependencia") {
                	$resp=mysql_query(sprintf("select * from registro where md5(id_visita)=%s ORDER by id_dependencia,fecha,hora",comillas($visitas)),$link);
	        }

        	while($row5 = mysql_fetch_array($resp)) {
                	$ContReg=$ContReg+1;
	                echo '<tr>';
        	        echo '<td class="cen">'.$ContReg.'</td>';
	                echo '<td class="cen">'.$row5['fecha'].'</td>';
        	        echo '<td class="cen">'.$row5['hora'].'</td>';
                	echo '<td>'.$dependencias[$row5['id_dependencia']].'</td>';

	                echo '<td><a href="detallado.php?id='.md5($row5['id']).'" title="Clic para ver reporte de la visita"><img src="iconos/preview.gif" width="20px" border="0" alt="Detallado" /></a></td>';
        	        echo '<td><a href="editar.php?id='.md5($row5['id']).'" title="Clic para editar la visita"><img src="iconos/lapiz.png" width="20px" border="0" alt="Editar" /></a></td>';
                	if ($es_adm['tipo']=="ADM") {
                        	echo '<td><a href="borrar.php?id='.md5($row5['id']).'" title="Clic para eliminar la visita"><img src="iconos/delete.png" border="0" alt="Eliminar" /></a></td>';
	                }
        	        echo '</tr>';
	        }
		print "<br /><strong><a href='javascript:history.go(-1)'>Volver</a></strong>";
		if($ContReg==0) {
        	        echo '<tr><td class="cen" colspan="8"><strong>No existen registros para esta fecha</strong></td></tr>';
	        }
        echo '</table></div>';
        }
}
else {
        include_once("encabezado.html");
        echo '<table>';
        echo '<tr><td class="cen"><strong>Su sesión ha finalizado, por favor vuelva a ingresar al sistema</strong></td></tr>';
        echo '</table></div></body></html>';
}

echo '</body></html>';
mysql_close($link);
?>
