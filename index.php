
<?php

    /*
        

        Author: Enrique Sánchez Vicente

        EJECUTAR CON EL SONIDO ACTIVADO

        -Para ejecutar correctamente debe de estar todo el arbol de directorios volcado en la raiz del servidor, 
         no como una subcarpeta.
    */

    include("./tragaperras.php");
    include("./gestorUsuarios.php");

    session_start();
    //session_destroy();

    //recoge la ip del usuario
    $ipUsuario = $_SERVER['REMOTE_ADDR'];

    //crea un gestor de usuarios con la ip
    $gestorUsuario = new GestorUsuarios($ipUsuario);
    
    //cargamos el objeto tragaperras de la sesión si existe y si no miramos los ficheros de guardado
    if(!isset($_SESSION["traga"])){

        //mira si existe el archivo en el directorio de guardado
        if($gestorUsuario->existe()){
            //recupera el objeto tragaperras del fichero
            $traga = unserialize($gestorUsuario->recuperar());
        }else{
            //si no lo crea nuevo, en este caso es la primera conexión del cliente
            $traga = new Tragaperras(); 
        }

        
    }else{
        //si se puede recuperar de la sesión se prioriza a una lectura de fichero para agilizar el proceso
        $traga = unserialize($_SESSION["traga"]);
    }

    $isJugando = false;
    
    $isCambioApuesta = false;

    //Eventos de botones 
    if(isset($_POST["btnBet"])){
        jugada($traga);
        $isJugando = true;
    }else if(isset($_POST["btnIns"])){
        $traga->ingresar();
    }else if(isset($_POST["btnAp"])){
        $traga->cambiarApuesta();
        $isCambioApuesta = true;
    }else if(isset($_POST["btnRet1"])){
        $traga->ActRetenciones(0);
    }else if(isset($_POST["btnRet2"])){
        $traga->ActRetenciones(1);
    }else if(isset($_POST["btnRet3"])){
        $traga->ActRetenciones(2);
    }

    //cambiamos los estilos de la puesta dependiendo de el que tenga el estado de la máquina
    switch($traga->apuesta){
        case 0:
            $colorApuesta = "rgb(38, 231, 54)";
            $colorApuestaBorde = "rgb(23, 152, 20)";
            break;

        case 1:
            $colorApuesta = "rgb(0, 170, 255)";
            $colorApuestaBorde = "#08afc5";
            break;

        case 2:
            $colorApuesta = "rgb(255, 0, 0)";
            $colorApuestaBorde = "rgb(160, 19, 19)";
            break;
    }


    function jugada($traga){
        $traga->jugar();
    }

    function imprimirRetenciones($traga){

        for ($i=0; $i < count($traga->retenciones); $i++) { 
            if($traga->retenciones[$i]){
                echo("<br>Retenciones $i");
            }
            
        }

        echo("<br>");

        for ($i=0; $i < count($traga->retencionesJS); $i++) { 
            if($traga->retencionesJS[$i]){
                echo("<br>RetencionesJS $i");
            }
        }

    }

    

?>

<!DOCTYPE html>
<html>

    <head>
   
    <meta charset="UTF-8"/>
    <title>Tragaperras</title>
    <link rel="icon" href="./img/miniItems/cereza.jpg" type="image/icon">

    <meta name="viewport" content="width=device-width, initial-scale=-0.8">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fruktur&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" type="text/css" href="./styles/formato.css"/>

    <style>

        p.txtPremios{
              color: <?php echo($colorApuesta) ?>;  
        }

        div#tablaPremios{
              border: groove <?php echo($colorApuestaBorde) ?> 15px;
        }

        <?php

            echo("img#im1{

                top: ".calcularPosAnterior($traga->figurasAnt[0], $traga)."px;

            }");

            echo("img#im2{

                top: ".calcularPosAnterior($traga->figurasAnt[1], $traga)."px;

            }");

            echo("img#im3{

                top: ".calcularPosAnterior($traga->figurasAnt[2], $traga)."px;

            }");

   
            function calcularPosAnterior($figura, $traga){
                return $traga::COORDENADAS[$figura];
            }

        ?>

    </style>

    <!--    SCRIPT PARA PASAR VARIABLES DEL PHP AL JAVASCRIPT  -->

    <script type="text/javascript">

        var isBet = <?php if($isJugando){echo("true");}else{echo("false");}; ?>;

        var isRet =  <?php if($traga->isRet){echo("true");}else{echo("false");}; ?>;

        var retencionDenegada = <?php 

            if($traga->retencionDenegada){
                $traga->retencionDenegada = false;
                echo("true");
            }else{
                echo("false");
            }

        ?> ;

        var isCambioApuesta = <?php if($isCambioApuesta){echo("true");}else{echo("false");} ?>;

        var apuesta = <?php echo($traga->apuesta); ?>;

        var bet1 = <?php if($traga->retencionesJS[0]){echo("true");}else{echo("false");}; ?>;
        var bet2 = <?php if($traga->retencionesJS[1]){echo("true");}else{echo("false");}; ?>;
        var bet3 = <?php if($traga->retencionesJS[2]){echo("true");}else{echo("false");}; ?>;

        var figura1 = "<?php echo($traga->tirada[0]); ?>";
        var figura2 = "<?php echo($traga->tirada[1]); ?>";
        var figura3 = "<?php echo($traga->tirada[2]); ?>";

        var hayPremio =  <?php if($traga->hayPremio){echo("true");}else{echo("false");}; ?> ;
        var valorPremio = <?php echo($traga->valorPremio); ?>;

    </script>

    <script type="text/javascript" src="./js/logica.js"></script>   

    </head>

    <body>

        <div id="bastidor">

            <div id="logo" class="marcoLogo">
                <img id="logo" src="./img/logo.png"/>
            </div>

            <div id="panelBanco">
                <p id="letrasBanco">    
                    <?php echo (number_format($traga->banco, 2)."€") ?> 
                </p>
            </div>

            <div id="tablero">

                <div id="rodillo1" class="marcoRodillo">
                    <img id="im1" class="rodillo" src="./img/rodillo.png"/>
                </div>

                <div id="rodillo2" class="marcoRodillo">
                    <img id="im2" class="rodillo" src="./img/rodillo.png"/>
                </div>

                <div id="rodillo3" class="marcoRodillo">
                    <img id="im3" class="rodillo" src="./img/rodillo.png"/>

                </div>

            </div>

            
            
            <div id="tablaPremios">

                <div class="tr">

                    <div id="cajonUvas" class="premiosItem td">
                        <img src="./img/miniItems/uvas.jpg" class="imagenPremios"/>
                        <p class="txtPremios">
                            <?php echo($traga::PREMIOS["uvas"][$traga->apuesta]."€"); ?>
                        </p>
                    </div>

                    <div id="cajonCampana" class="premiosItem td">
                        <img src="./img/miniItems/campana.jpg" class="imagenPremios"/>
                        <p class="txtPremios">
                            <?php echo($traga::PREMIOS["campana"][$traga->apuesta]."€"); ?>
                        </p>
                    </div>

                    <div id="cajonDiamante" class="premiosItem td">
                        <img src="./img/miniItems/diamante.jpg" class="imagenPremios"/>
                        <p class="txtPremios">
                            <?php echo($traga::PREMIOS["diamante"][$traga->apuesta]."€"); ?>
                        </p>
                    </div>

                </div>

                <div class="tr">

                    <div id="cajonUva" class="premiosItem td">
                        <img src="./img/miniItems/uva.jpg" class="imagenPremios"/>
                        <p class="txtPremios">
                            <?php echo($traga::PREMIOS["uva"][$traga->apuesta]."€"); ?>
                        </p>
                    </div>

                    <div id="cajonFresa" class="premiosItem td">
                        <img src="./img/miniItems/fresa.jpg" class="imagenPremios"/>
                        <p class="txtPremios">
                            <?php echo($traga::PREMIOS["fresa"][$traga->apuesta]."€"); ?>
                        </p>
                    </div>

                    <div id="cajonBar" class="premiosItem td">
                        <img src="./img/miniItems/bar.jpg" class="imagenPremios"/>
                        <p class="txtPremios">
                            <?php echo($traga::PREMIOS["bar"][$traga->apuesta]."€"); ?>
                        </p>
                    </div>

                </div>

                <div class="tr">

                    <div id="cajonCerezas" class="premiosItem td">
                        <img src="./img/miniItems/cereza.jpg" class="imagenPremios"/>
                        <p class="txtPremios">
                            <?php echo($traga::PREMIOS["cerezas"][$traga->apuesta]."€"); ?>
                        </p>
                    </div>

                    <div id="cajonLimon" class="premiosItem td">
                        <img src="./img/miniItems/limon.jpg" class="imagenPremios"/>
                        <p class="txtPremios">
                            <?php echo($traga::PREMIOS["limon"][$traga->apuesta]."€"); ?>
                        </p>
                    </div>

                    <div id="cajonTrebol" class="premiosItem td">
                        <img src="./img/miniItems/trebol.jpg" class="imagenPremios"/>
                        <p class="txtPremios">
                            <?php echo($traga::PREMIOS["trebol"][$traga->apuesta]."€"); ?>
                        </p>
                    </div>

                </div>

               

        </div>
            
            <form action="./index.php" method="post" id="botonera">

                <button id="btnIns" class="boton" name="btnIns">INGRESAR</button>
                <button id="btnAp" class="boton" name="btnAp">APUESTA</button>
                <button id="btnRet1" class="boton" name="btnRet1">&#11014</button>
                <button id="btnRet2" class="boton" name="btnRet2">&#11014</button>
                <button id="btnRet3" class="boton" name="btnRet3">&#11014</button>
                <button id="btnBet" class="boton" name="btnBet">JUGAR</button>
                    
                    

            </form>
            

        </div>
    </body>

</html>

<?php //finalizacion 

    if($traga->hayPremio){

        $traga->hayPremio = false;
        $traga->sumarPremio();
    }

    
    $traga->desactivarRetenciones();
    

    $traga->intercambiarTiradas();

    //Se guarda el estado en la sesión y en el fichero despues de cada recarga
    guardarEstado($traga, $gestorUsuario);

    function guardarEstado($traga, $gestorUsuario){

        //rerializa el objeto
        $tragaSerializada = serialize($traga);

        //lo guarda en la sesión para un acceso rápido en la siguiente peticion
        $_SESSION["traga"] = $tragaSerializada;
        //lo guarda en el fichero del usuario para conservar el estado siempre aunque se interrumpiera la conexion
        $gestorUsuario->guardar($tragaSerializada);
        
    }
?>


