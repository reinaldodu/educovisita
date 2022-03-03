<?php
require_once("funciones.php");	
require_once("conexionBD.php");
$link=conectarse(); 

//***Leer variables del sistema******
$estado=mysql_query("select * from general",$link);
$leer= mysql_fetch_array($estado);

//****** Verificamos si existe la cookie *****/
if(isset($_COOKIE['VisitaDatAdmin'])) {
	if (!isset($_POST['envia_documento']) and !isset($_POST['envia_registro'])) {
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
		echo '<html>';
		echo '<head>';
		echo '<style type="text/css" media="print"> .nover {display:none}</style>';
		echo '<title>'.$leer['institucion'].' - Consulta visitas</title>';
		echo '<link href="estilo2.css" rel="stylesheet" type="text/css" />';
		echo '</head>';
		echo '<body OnLoad="document.reg_doc.ver_doc.focus();">';
		echo '<h1>'.$leer['institucion'].'</h1>';
		echo '<h2>Registro de visita</h2>';
		echo '<form name="reg_doc" action="registro2.php" method="post">';
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
		echo '</body></html>';
	}
	//else {
	if (isset($_POST['envia_documento'])) {

		$resp=mysql_query(sprintf("select * from visitas where documento=%s",comillas($_POST['ver_doc'])),$link);
		if ($row = mysql_fetch_array($resp)) {
			$resp6=mysql_query("select * from dependencias order by nombre",$link);
			while($row6 = mysql_fetch_array($resp6)) {
                		$dependencias[$row6["id"]]=$row6["nombre"];
        		}
			// **** Mostrar foto del visitante ****
			echo '<div>';
			$dir_imagenes="fotos";
			$nombre_imagen=$row['id'];
			if (file_exists($dir_imagenes."/".$nombre_imagen.".jpg")) {
				escala($dir_imagenes.'/'.$nombre_imagen.'.jpg',250);
			}
			echo '</div>';
			include_once("registro2.html");
		}
		else {
			include_once("encabezado.html");
			print "<strong>El documento no aparece registrado en el sistema<br />";
			print"<br /><a href='javascript:history.go(-1)'>Volver al formulario</a></strong></div></body></html>";
			exit;
		}
	}	

	if (isset($_POST['envia_registro'])) {
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

			//Validar los campos requeridos
			valida(array("requerido"=>"nom_vis,doc_vis"));

			//********************************
			// GUARDAMOS LOS DATOS EN LA BD
			//********************************

			//******Actualizamos los datos del visitante ******
			$cons_sql  = sprintf("UPDATE visitas SET nombres=%s,apellidos=%s,documento=%s,empresa=%s,telefono=%s WHERE id=%d", comillas($fnom_vis), comillas($fape_vis), comillas($fdoc_vis), comillas($fempr_vis), comillas($ftel_vis),$_POST['id_vis']);
			mysql_query($cons_sql,$link);

			//******Guardamos los datos de registro ******
			$cons_sql  = sprintf("INSERT INTO registro(fecha,hora,motivo,tarjeta,observaciones,recibe,id_dependencia,id_visita) VALUES(%s,%s,%s,%s,%s,%s,%d,%d)", comillas($ffecha), comillas($fhora), comillas($fmot_vis), comillas($ftar_vis), comillas($fobs_vis),comillas($frec_vis),$fid_dep,$_POST['id_vis']);
			mysql_query($cons_sql,$link);

			//******Guardamos los datos de control ******
			$fip = $_SERVER['REMOTE_ADDR'];
			$faccion="Registro_Visitante";
			$cons_sql2  = sprintf("INSERT INTO control(fecha,hora,ip,accion,id_usuario) VALUES(%s,%s,%s,%s,%d)", comillas($ffecha), comillas($fhora), comillas($fip), comillas($faccion),$_COOKIE['VisitaDatAdmin']);
			mysql_query($cons_sql2,$link);
			include_once("confirma.html");

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
