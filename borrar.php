<?php
require_once("funciones.php");	
require_once("conexionBD.php");
$link=conectarse(); 
//****** Verificamos si existe la cookie *****/
if(isset($_COOKIE['VisitaDatAdmin'])) {
	
	//******Mostrar mensaje para eliminar visita*******
	if(isset($_GET['id'])) {
		$resp=mysql_query(sprintf("select * from visitas,registro where md5(registro.id)=%s and registro.id_visita=visitas.id",comillas($_GET['id'])),$link);
	        if ($row = mysql_fetch_array($resp)) {
			include_once("encabezado.html");
			
			echo '<table><tr><td class="cen"><strong>¿Desea eliminar la visita de '.$row['nombres'].' '.$row['apellidos'].' con fecha '.$row['fecha'].' y hora '.$row['hora'].'? ';
			echo '<a href="borrar.php?id='.$_GET['id'].'&elimina=1" title="Eliminar visita del sistema">Si</a>&nbsp&nbsp&nbsp&nbsp';
			print"<a href='javascript:history.go(-1)'>No</a>";
			echo '</strong></td></tr>';
        		echo '</table></div></body></html>';
		}
		else {
		        echo '<strong>No hay datos para esta visita</strong>';
		}
	}
	
	//*****Eliminar estudiante******
	if((isset($_GET['id']))and(isset($_GET['elimina']))and($_GET['elimina']=="1")) {
		$resp2=mysql_query(sprintf("delete from registro where md5(id)=%s",comillas($_GET['id'])),$link);

		//******Guardamos los datos de control ******
		$ffecha=date("Y-m-d");
		$fhora=date("G:i:s");
		$fip = $_SERVER['REMOTE_ADDR'];
		$faccion="Admin_Elimina_Visita (Doc:".$row['documento'].")";
		$cons_sql  = sprintf("INSERT INTO control(fecha,hora,ip,accion,id_usuario) VALUES(%s,%s,%s,%s,%d)", comillas($ffecha), comillas($fhora), comillas($fip), comillas($faccion),$_COOKIE['VisitaDatAdmin']);
mysql_query($cons_sql,$link);
		//**** Redireccionar página web después de eliminar *****
		echo '<script type="text/javascript">'; 
		echo 'window.location="consulta.php"';
		echo '</script>';
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
