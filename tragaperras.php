<?php

    /*
        Author: Enrique Sánchez Vicente
    */


    class Tragaperras{

        const INGRESO_ESTANDAR = 20;
        const RETENCION_SIMPLE = 30;
        const RETENCION_DOBLE = 60;
        
        const COORDENADAS = array(
            "trebol" => -4,
            "uva" => -116,
            "cerezas" => -228,
            "uvas" => -340,
            "bar" => -452,
            "campana" => -564,
            "diamante" => -676,
            "fresa" => -788,
            "limon" => -900
        );

        const PREMIOS = array(

            "cerezas" => [0.40, 1.20, 2.00],
            "uva" => [0.80, 2.00, 4.00],
            "uvas" => [1.60, 4.80, 8.00],
            "limon" => [2.40, 7.20, 12.00],
            "fresa" => [3.20, 9.60, 16.00],
            "campana" => [4.00, 12.00, 20.00],
            "trebol" => [10.00, 30.00, 50.00],
            "bar" => [20.00, 60.00, 100.00],
            "diamante" => [100.00, 300.00, 500.00]
            
        );

        const APUESTAS = array(0.20, 0.60, 1.00);

        public $apuesta;
        public $banco;

        public $tirada;
        public $tiradaNum;
        public $figurasAnt;
        
        public $retenciones;
        public $retencionesJS;
        public $isRet;
        public $modoRet;
        public $contRet;
        public $retencionDenegada;
        private $denegarProxRet;

        public $hayPremio;
        public $valorPremio;
        
        
        function __construct(){

            $this->banco = 20.00;
            $this->apuesta = 0;
            $this->balance = 0;

            $this->figurasAnt = array();
            $this->figurasAnt[0] = "uvas";
            $this->figurasAnt[1] = "cerezas";
            $this->figurasAnt[2] = "limon";
            
            $this->tirada = array();
            $this->tirada[0] = "cerezas";
            $this->tirada[1] = "trebol";
            $this->tirada[2] = "trebol";

            $this->tiradaNum = array(0,0,0);

            $this->retenciones = array(false, false, false);
            $this->retencionesJS = array(false, false, false);

            $this->modoRet = false;
            $this->contRet = 0;
            $this->retencionDenegada = false;
            $this->denegarProxRet  = false;

            $this->hayPremio = false;
            $this->valorPremio = 0.00;

        }

        //ingreso de más dinero del evento INGRESAR
        public function ingresar(){
            $this->banco += self::INGRESO_ESTANDAR;
        }

        //cambio de apuesta del evento APUESTA
        public function cambiarApuesta(){

            $this->protocoloSinRet();

            if($this->apuesta == 0){
                $this->apuesta = 1;
            }else if($this->apuesta == 1){
                $this->apuesta = 2;
            }else if($this->apuesta == 2){
                $this->apuesta = 0;
            }

        }

        //conserva el estado de las figuras anteriores para tomar decisiones
        public function intercambiarTiradas(){
            $this->figurasAnt = $this->tirada;
        }


        //accion de juego del evento JUGAR
        public function jugar(){

            //solo se juega si hay dinero disponible para suplir la apuesta
            if($this->banco >= self::APUESTAS[$this->apuesta]){

                $this->banco -= self::APUESTAS[$this->apuesta]; //se resta la tirada del banco

                if($this->modoRet){//se contabilizan las retenciones consecutivas

                    $this->contRet++;
    
                }else{
    
                    $this->contRet = 0;
                }

                $figuras = array_keys(self::COORDENADAS);//se extraen las figuras en un array de String


                
                    $this->generarTirada(0, $figuras);

                    $this->generarTirada(1, $figuras);

                    $this->generarTirada(2, $figuras);

                    
                    //si hay modo retencion y no hay seleccionadas retenciones se desactiva el modo retenciones
                    if($this->modoRet){ 
                        $this->evaluarContinuidadRetencion();
                    }
                   
                    
            
            $this->evaluarTirada();//se evalua la combinacion obtenida
            

            }
            


        }

        private function generarTirada($rodillo, $figuras){

            if(!$this->retenciones[$rodillo]){//se gira el  rodillo si no hay retenciones

                //echo("<br>Generado $rodillo");
                $rand1 = $this->bet($figuras); //se saca una figura aleatoria
                $this->tirada[$rodillo] = $figuras[$rand1];
                $this->tiradaNum[$rodillo] = $rand1;
                
            }else{ //si hay retenciones en el rodillo se reflejan en las retenciones del JS del front 
                $this->retencionesJS[$rodillo] = true;
                
            }

        }

        private function evaluarContinuidadRetencion(){

            if(!$this->retenciones[0] && !$this->retenciones[1] && !$this->retenciones[2]){
                $this->protocoloSinRet();
            }

        }

        public function desactivarRetenciones(){

            $this->isRet = false;

            $this->retencionesJS[0] = false;
            $this->retencionesJS[1] = false;
            $this->retencionesJS[2] = false;
                        
        }

        private function evaluarTirada(){


            //PREMIO
            if($this->tiradaNum[0] == $this->tiradaNum[1] && $this->tiradaNum[1] == $this->tiradaNum[2] ){

                //se borran las retenciones el estado
                if(!$this->modoRet){
                    $this->protocoloSinRet();
                }else{
                    $this->denegarProxRet  = true;//denegará la proxima retención siempre
                }
                
                
                //se disponen las varibles y la flag para que el front lo refleje en la tirada pero no sea inmediato al cargar el html
                $this->hayPremio = true;
                $this->valorPremio = self::PREMIOS[$this->tirada[0]][$this->apuesta];

            //RETENCION IZQUIERDA
            }else if($this->tiradaNum[0] == $this->tiradaNum[1] && $this->tiradaNum[1] != $this->tiradaNum[2] && $this->tiradaNum[0] != $this->tiradaNum[2]){

                //se retiene en un % de las veces para la retencion doble
                if($this->evaluarRet(self::RETENCION_DOBLE) || $this->contRet <= 1){
                    //si evaluamos por primera vez una retencion siempre se tira pero si es mayor a uno manda la provabilidad de 60

                    $this->retenciones[0] = true;
                    $this->retenciones[1] = true;
                    $this->retenciones[2] = false;

                    

                    $this->isRet = true;
                    $this->modoRet = true;
                
                //si no sale por provabilidad se desecha la retencion
                }else{
                   $this->protocoloFinRetenciones();
                }
 
            //RETENCION DERECHA
            }else if($this->tiradaNum[1] == $this->tiradaNum[2] && $this->tiradaNum[0]!= $this->tiradaNum[1] && $this->tiradaNum[0] != $this->tiradaNum[2]){


                if($this->evaluarRet(self::RETENCION_DOBLE) || $this->contRet <= 1){

                    $this->retenciones[0] = false;
                    $this->retenciones[1] = true;
                    $this->retenciones[2] = true;

                    

                    $this->isRet = true;
                    $this->modoRet = true;

                }else{
                    $this->protocoloFinRetenciones();
                }

            //RETENCION LADOS
            }else if($this->tiradaNum[0] == $this->tiradaNum[2] && $this->tiradaNum[1] != $this->tiradaNum[0] && $this->tiradaNum[1] != $this->tiradaNum[2]){


                if($this->evaluarRet(self::RETENCION_DOBLE) || $this->contRet <= 1){

                    $this->retenciones[0] = true;
                    $this->retenciones[1] = false;
                    $this->retenciones[2] = true;

                    

                    $this->isRet = true;
                    $this->modoRet = true;

                }else{
                    $this->protocoloFinRetenciones();
                }

            //RETENCION DE UNA SOLA FIGURA, solo puede ser por accion del usuario
            //se retiene en un % de las veces para la retencion simple
            }else if($this->modoRet && $this->contRet > 1){
                //las retenciones no tontempladas arriba, es decir las menos comunes tienen una provabilidad menor de conservarse
                if($this->evaluarRet(self::RETENCION_SIMPLE)){
                    $this->protocoloFinRetenciones();
                }
            }
            

            
            
        }

        private function protocoloFinRetenciones(){

            $this->retencionDenegada = true;

            $figuras = array_keys(self::COORDENADAS);

            $this->protocoloSinRet();

            $this->generarTirada(0, $figuras);
            $this->generarTirada(1, $figuras);
            $this->generarTirada(2, $figuras);

            
            
        }

        

        private function protocoloSinRet(){

            //echo("<br>Destruir");

            $this->isRet = false;
            $this->modoRet = false;

            $this->contRet = 0;

            $this->retencionesJS[0] = false;
            $this->retencionesJS[1] = false;
            $this->retencionesJS[2] = false;

            $this->retenciones[0] = false;
            $this->retenciones[1] = false;
            $this->retenciones[2] = false;


        }

        /*accion del cambio de estado de las retenciones de los eventos RETENCION
          el boton de la pulsacion del evento se recibe como argumento tipo entero
        */
        public function ActRetenciones($nRet){

            //solo actua si está el modo retención activado
            if($this->modoRet){

                //si la retencion que el usuario ha pulsado estaba activa simplemente se desactiva
                if($this->retenciones[$nRet]){//si la retencion está activa, se desactiva

                    $this->retenciones[$nRet] = false;
                    //$this->retencionesJS[$nRet] = false;

                }else{//si no esta activa 

                    if($nRet==0){

                        if($this->retenciones[1] &&  $this->retenciones[2]){
                            $this->retenciones[1] = false;
                            $this->retenciones[2] = false;
                   
                        }else{
                            $this->retenciones[$nRet] = true;

                        }

                    }else if($nRet==1){

                        if($this->retenciones[0] &&  $this->retenciones[2]){
                            $this->retenciones[0] = false;
                            $this->retenciones[2] = false;
                        
                        }else{
                            $this->retenciones[$nRet] = true;

                        }

                    }else if($nRet==2){

                        if($this->retenciones[0] &&  $this->retenciones[1]){
                            $this->retenciones[0] = false;
                            $this->retenciones[1] = false;
              
                        }else{
                            $this->retenciones[$nRet] = true;
                        }

                    }

                }


                $this->igualarRetenciones();

                $this->isRet = false;//se le indica al js que no busque retenciones despues de la tirada
 
            }
        }

        //las retneciones lógicas del front se igualana a las del estado actual de la máquina
        private function igualarretenciones(){

            $this->retencionesJS[0] = $this->retenciones[0];
            $this->retencionesJS[1] = $this->retenciones[1];
            $this->retencionesJS[2] = $this->retenciones[2];

        }


        private function bet($figuras){
            $num = rand(0,count($figuras) - 1);
            return $num;

        }

        public function sumarPremio(){


            $this->banco += $this->valorPremio;
            $this->valorPremio = 0;
        }

        private function evaluarRet($porcentaje){

            if($this->denegarProxRet){ //se deniega siempre si la flag está true.
                $this->denegarProxRet  = false;
                return false;
            }

            $rand = random_int(0, 100);

            if($rand <= $porcentaje){
                return true;
            }else{
                return false;
            }
            
        }

       



    }

?>