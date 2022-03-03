<?php
//**** Eliminar cookie de sesión *****
setcookie("VisitaDatAdmin", "", time()-3600);

//**** Redireccionar página web *****
header ("Location: index.php"); 
exit();
?>
