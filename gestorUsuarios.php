<?php

    class GestorUsuarios{

        const CARACTERES_PROIVIDOS = array("\\", "/", ":", "*", "?", '"', ">", "<", "|");

        const DIRECTORIO = "./users/";

        private $ip;
        private $file;

        function __construct($ip){

            for ($i=0; $i < count(self::CARACTERES_PROIVIDOS); $i++) { 
                $ip = str_replace(self::CARACTERES_PROIVIDOS[$i], "", $ip);
            }
            //en este punto la ip no tendra carazteres prohividos para poderguardar un fichero
            $this->ip = $ip;

            $this->file = self::DIRECTORIO.$this->ip.".us";

        }

        public function existe(){
            return file_exists($this->file);
        }

        public function recuperar(){

            //recupera la información del fichero guardado
            $archivo = fopen($this->file, "r");

            $contenido = fread($archivo, filesize($this->file));

            fclose($archivo);

            return $contenido;
        }

        public function guardar($objetoSerializado){

            //conforma el fichero de guardado

            if(file_exists($this->file)){
                unlink($this->file);
            }

            $archivo = fopen($this->file, "w");

            fwrite($archivo, $objetoSerializado);

            fclose($archivo);

        }



    }

?>