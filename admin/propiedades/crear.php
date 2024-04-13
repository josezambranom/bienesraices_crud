<?php

    require '../../includes/funciones.php';
    $auth = estaAutenticado();

    if(!$auth) {
        header('Location: /');
    }

    require '../../includes/config/database.php';
    $db = conectarDB();

    // COnsulta de vendedores
    $consulta = "SELECT * FROM vendedores";
    $resultado = mysqli_query($db, $consulta);

    // Arreglo con mensajes de errores
    $errores = [];

    $titulo = "";
    $precio = "";
    $descripcion = "";
    $habitaciones = "";
    $wc = "";
    $estacionamiento = "";
    $vendedorID = "";

    // Ejecutar el código después de que el usuario envía el form
    if($_SERVER['REQUEST_METHOD'] === 'POST') {

        $titulo = mysqli_real_escape_string($db, $_POST['titulo']);
        $precio = mysqli_real_escape_string($db, $_POST['precio']);
        $descripcion = mysqli_real_escape_string($db, $_POST['descripcion']);
        $habitaciones = mysqli_real_escape_string($db, $_POST['habitaciones']);
        $wc = mysqli_real_escape_string($db, $_POST['wc']);
        $estacionamiento = mysqli_real_escape_string($db, $_POST['estacionamiento']);
        $vendedorID = mysqli_real_escape_string($db, $_POST['vendedor']);
        $creado = date('Y/m/d');

        // Asignar files a una variable
        $imagen = $_FILES['img'];   

        if(!$titulo) {
            $errores[] = "Debes añadir un título";
        }

        if(!$precio) {
            $errores[] = "El precio es obligatorio";
        }

        if(strlen($descripcion) < 50) {
            $errores[] = "La descripción es obligatoria y debe tener al menos 50 caracteres";
        }

        if(!$habitaciones) {
            $errores[] = "El número de habitaciones es obligatorio";
        }

        if(!$wc) {
            $errores[] = "El número de baños es obligatorio";
        }

        if(!$estacionamiento) {
            $errores[] = "El número de estacionamientos es obligatorio";
        }

        if(!$vendedorID) {
            $errores[] = "Elije un vendedor";
        }

        if(!$imagen['name'] || $imagen['error']){
            $errores[] = "La imagen es obligatoria";
        }

        // Validar por tamaño (1Mb máximo)
        $medida = 1000 * 1000;

        if($imagen['size'] > $medida) {
            $errores[] = "La imagen es muy pesada";
        }

        // echo "<pre>";
        // var_dump($errores);
        // echo "</pre>";

        // Revisar que el array de errores este vacio
        if(empty($errores)) {

            // Subida de archivos

            // Crear carpeta
            $carpetaImagenes = '../../imagenes/';

            if(!is_dir($carpetaImagenes)) {
                mkdir($carpetaImagenes);
            }

            // Generar nombre unico
            $nombreImagen = md5(uniqid(rand(), true)) . ".jpg";


            // Subir la imagen
            move_uploaded_file($imagen['tmp_name'], $carpetaImagenes . $nombreImagen);

            // Insertar en DB
            $query = "INSERT INTO propiedades (titulo, precio, imagen, descripcion, habitacion, wc, estacionamiento, creado,
            vendedores_id) VALUES ('$titulo', '$precio', '$nombreImagen', '$descripcion', '$habitaciones', '$wc', '$estacionamiento',
             '$creado','$vendedorID')";

            $result = mysqli_query($db, $query);
            if($result) {
                // Redireccionar al usuario
                header('Location: /admin?result=1'); // Funciona cuando no hay html antes, solo cuando sea necesario lo mas poco
            }
        }
        
    }
    
    incluirTemplate('header');
?>

    <main class="contenedor seccion">
        <h1>Crear</h1>

        <a class="boton boton-verde" href="/admin">Volver</a>

        <?php foreach($errores as $error): ?>
            <div class="alerta error">
                <?php echo $error; ?>
            </div>
        <?php endforeach; ?>

        <form class="formulario" method="POST" action="/admin/propiedades/crear.php" enctype="multipart/form-data">
            <fieldset>
                <legend>Información general</legend>

                <label for="titulo">Título</label>
                <input type="text" name="titulo" id="titulo" placeholder="Titulo propiedad" 
                    value = "<?php echo $titulo ?>" >

                <label for="precio">Precio</label>
                <input type="number" name="precio" id="precio" placeholder="Precio propiedad"
                    value = "<?php echo $precio ?>">

                <label for="img">Imagen</label>
                <input type="file" name="img" id="img" accept="image/jpeg, image/png">

                <label for="descripcion">Descripción</label>
                <textarea name="descripcion" id="descripcion"><?php echo $descripcion; ?></textarea>
            </fieldset>

            <fieldset>
                <legend>Información propiedad</legend>
                
                <label for="habitaciones">Habitaciones</label>
                <input type="number" name="habitaciones" id="habitaciones" placeholder="Ej: 3" min="1" max="9" 
                    value = "<?php echo $habitaciones ?>">

                <label for="wc">Baños</label>
                <input type="number" name="wc" id="wc" placeholder="Ej: 3" min="1" max="9" 
                    value = "<?php echo $wc ?>">

                <label for="estacionamiento">Estacionamiento</label>
                <input type="number" name="estacionamiento" id="estacionamiento" placeholder="Ej: 3" min="1" 
                    max="9" value = "<?php echo $estacionamiento ?>" >
            </fieldset>

            <fieldset>
                <legend>Vendedor</legend>

                <select name="vendedor">
                    <option value="">-- Seleccione --</option>
                    <?php while($vendedor = mysqli_fetch_assoc($resultado)): ?>
                        <option <?php echo $vendedorID === $vendedor['id'] ? 'selected' : ''; ?> value="<?php echo $vendedor['id']?>">
                            <?php echo $vendedor['nombre']. " " . $vendedor['apellido'] ?>
                        </option>
                    <?php endwhile ?>
                </select>
            </fieldset>

            <input class="boton boton-amarillo" type="submit" value="Crear propiedad">
        </form>

    </main>

<?php
    incluirTemplate('footer');
?>