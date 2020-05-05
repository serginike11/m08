<?php

$bHayFicheros = 0;
$sCabeceraTexto = "";
$sAdjuntos = "";


    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        #Reemplazar este correo por el correo electrónico del destinatario
        $mail_to = "info@coutomixtour.com";

        # Envío de datos
        // $subject = trim($_POST["subject"]);
        $name = str_replace(array("\r","\n"),array(" "," ") , strip_tags(trim($_POST["name"])));
        $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);

        if ($email) $sCabeceras = "From:".$email."\n";
        else $sCabeceras = "";
        $sCabeceras .= "MIME-version: 1.0\n";

        // $phone = trim($_POST["phone"]);
        $message = trim($_POST["message"]);

        if ( empty($name) OR !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            # Establecer un código de respuesta y salida.
            http_response_code(400);
            echo "Por favor completa el formulario y vuelve a intentarlo.";
            exit;
        }

        # Contenido del correo
        $content = "Nombres: $name\n";
        $content .= "E-mail: $email\n\n";
        // $content .= "Telefono: $phone\n";
        $content .= "Mensaje:\n$message\n";

        # Recopilamos el fichero $sAdjuntosforeach ($_FILES as $vAdjunto)
        foreach ($_FILES['adjunto'] as $vAdjunto){
          if ($bHayFicheros == 0)
          {
            $bHayFicheros = 1;
            $sCabeceras .= "Content-type: multipart/mixed;";
            $sCabeceras .= "boundary=\"--_Separador-de-mensajes_--\"\n";
            $sCabeceraTexto = "----_Separador-de-mensajes_--\n";
            $sCabeceraTexto .= "Content-type: text/plain;charset=iso-8859-1\n";
            $sCabeceraTexto .= "Content-transfer-encoding: 7BIT\n";
            $content = $sCabeceraTexto . $content;
          }
          if ($vAdjunto["size"] > 0)
          {
            $sAdjuntos .= "\n\n----_Separador-de-mensajes_--\n";
            $sAdjuntos .= "Content-type: ".$vAdjunto["type"].";name=\"".$vAdjunto["name"]."\"\n";;
            $sAdjuntos .= "Content-Transfer-Encoding: BASE64\n";
            $sAdjuntos .= "Content-disposition: attachment;filename=\"".$vAdjunto["name"]."\"\n\n";
            $oFichero = fopen($vAdjunto["tmp_name"], 'r');
            $sContenido = fread($oFichero, filesize($vAdjunto["tmp_name"]));
            $sAdjuntos .= chunk_split(base64_encode($sContenido));
            fclose($oFichero);
          }
        }
        if ($bHayFicheros)
        $content .= $sAdjuntos."\n\n----_Separador-de-mensajes_----\n";

        # Envía el correo.
        $success = mail($mail_to, "Solicitud web para Coutomixtour", $content, $sCabeceras);
        if ($success) {
            # Establece un código de respuesta 200 (correcto).
            http_response_code(200);
            echo "¡Gracias! Tu mensaje ha sido enviado.";
        } else {
            # Establezce un código de respuesta 500 (error interno del servidor).
            http_response_code(500);
            echo "Oops! Algo salió mal, no pudimos enviar tu mensaje, inténtalo más tarde o escríbenos directamente a info@coutomixtour.com";
        }

    } else {
        # No es una solicitud POST, establezce un código de respuesta 403 (prohibido).
        http_response_code(403);
        echo "Ha habido un problema con tu envío, inténtalo de nuevo.";
    }

?>
