/*
        Author: Enrique Sánchez Vicente
*/

var max = -2;
var min = -898;

var RANGO_MOVIMIENTO = 5;

var coordenadas = Array();

coordenadas["trebol"] = -4;
coordenadas["uva"] = -116;
coordenadas["cerezas"] = -228;
coordenadas["uvas"] = -340;
coordenadas["bar"] = -452;
coordenadas["campana"] = -564;
coordenadas["diamante"] = -676;
coordenadas["fresa"] = -788;
coordenadas["limon"] = -900;


var CAJONES = Array();

CAJONES["trebol"] = "cajonTrebol";
CAJONES["uva"] = "cajonUva";
CAJONES["cerezas"] = "cajonCerezas";
CAJONES["uvas"] = "cajonUvas";
CAJONES["bar"] = "cajonBar";
CAJONES["campana"] = "cajonCampana";
CAJONES["diamante"] = "cajonDiamante";
CAJONES["fresa"] = "cajonFresa";
CAJONES["limon"] = "cajonLimon";

var isRodando;
var isRetencion;



window.onload = function(){
    
    
    if(window.parent.isCambioApuesta){ //se mira a ver si se ha cambiado la apuesta para cambiar los estilos

        sonidoCambioApuesta(window.parent.isCambioApuesta);

    }

    if(window.parent.retencionDenegada){
        sonidoRetencionDenegada();
    }

    //si hay retenciones en los rodillos se cambia su aspecto
    evaluarEstadoRetenciones();


    //se recogen los elementos rodillo
    var rodillo1 = document.getElementById("im1");
    var rodillo2 = document.getElementById("im2");
    var rodillo3 = document.getElementById("im3");

    isRodando = [null,null,null];
    isRetencion = false;

    if(window.parent.isBet){

        //se deshabilita el formulario para que el usuario no pueda hacer nada
        if(!window.parent.bet1 || !window.parent.bet2 || !window.parent.bet3){
            deshabilitarBotones(true);
        }
        

        //si no hay retencion se activa la animación de giro para cada rodillo
        if(!window.parent.bet1){
            isRodando[0] = true;
            setTimeout(function(){girarRodillo(rodillo1, window.parent.figura1, 0);}, calcularInicio());
        }else{
            isRodando[0] = false;
            isRetencion = true;
        }

        if(!window.parent.bet2){
            isRodando[1] = true;
            setTimeout(function(){girarRodillo(rodillo2, window.parent.figura2, 1);}, calcularInicio());
        }else{
            isRodando[1] = false;
            isRetencion = true;

        }

        if(!window.parent.bet3){
            isRodando[2] = true;
            setTimeout(function(){girarRodillo(rodillo3, window.parent.figura3, 2);}, calcularInicio());
        }else{
            isRodando[2] = false;
            isRetencion = true;
        }
    
    }
}

function deshabilitarBotones(isdisable){

    var botonera = document.getElementById("botonera");

    var hijos = botonera.children;

    for(let i=0; i < hijos.length; i++){

        hijos[i].disabled = isdisable;

    }

}



function calcularInicio(){

    return Math.floor(Math.random() * 600) + 150;

}


function girarRodillo(rodillo, figura, indice){

    //calculamos un numero de giros aleatorio entre 1 y 4
    let nVueltas = Math.floor(Math.random() * 3) + 1;

    vuelta(rodillo, nVueltas, figura, indice);
    
}


function vuelta(rodillo, nVueltas, figura, indice){

    var intervalo = null;
    var pos = min;

    rodillo.style.top = pos + "px";
    

    clearInterval(intervalo);

    intervalo = setInterval(girar, 1);


    function girar() {

        
        if(nVueltas <= 0 && evaluarParada(pos, figura)){ //si ya estanis es la ultima vuelta
            clearInterval(intervalo);

            isRodando[indice] = false;

            console.log(isRodando);

            sonidoRodillo();

            evaluarHabilitacion();

        }

        if (pos >= max) { //otra vuelta

        nVueltas--;

        clearInterval(intervalo);

        rodillo.style.top = min;

        vuelta(rodillo, nVueltas, figura, indice);

        }else {

            pos+=RANGO_MOVIMIENTO;
            
            rodillo.style.top = pos + "px";

        }
    }


}


function evaluarParada(pos, figura){

    let centro = coordenadas[figura];

    let top = centro + 3;
    let bottom = centro - 3;

    if(pos <= top && pos >=bottom){
        return true;
    }else{
        return false;
    }

}

function evaluarHabilitacion(){

    parar = true;

    for(let i=0; i < isRodando.length; i++){
        if(isRodando[i]){
            parar=false;
            break;
        };
    }

    if(parar){//se han finalizado todos los procesos de giro

        if(window.parent.isRet){
            buscarRetenciones();
        }

        if(isRetencion || window.parent.isRet){
            

            sonidoRet = setInterval(function(){
                new Audio('./sounds/retencion.mp3').play();
                clearInterval(sonidoRet); 
            }, 7);



        }


        if(window.parent.hayPremio){
            sumarPremio();
        }

        deshabilitarBotones(false);


    }

}

function sonidoRetencionDenegada(){
    new Audio('./sounds/fallo.mp3').play();
}

function sumarPremio(){

    
    new Audio('./sounds/premio.mp3').play();

    document.getElementById(CAJONES[window.parent.figura1]).className = "premiosItemIluminado td";

    document.getElementById("logo").className = "marcoLogoLuminoso";

    document.getElementById("rodillo1").className = "marcoRodilloPremio";
    document.getElementById("rodillo2").className = "marcoRodilloPremio";
    document.getElementById("rodillo3").className = "marcoRodilloPremio";


    let banco = document.getElementById("letrasBanco");
    
    let valor = (parseFloat(banco.innerHTML.substr(0,banco.innerHTML.length-2)) + window.parent.valorPremio).toFixed(2);

    banco.innerHTML = valor + "€";


}

function buscarRetenciones(){
    /*
     si hay retencion que se produce por primera vez en esta tirada 
     entonces la variable isRet estara en true y será el front el encargado 
     de buscar la retencion para producir los cambios en la interfaz gráfica.
    */

     if(window.parent.figura1 == window.parent.figura2 && window.parent.figura2 != window.parent.figura3){
        //retencion izquierda
        cambiarAspectoRod1();
        cambiarAspectoRod2();

     }else if(window.parent.figura2 == window.parent.figura3 && window.parent.figura2 != window.parent.figura1){
        //retencion derecha
        cambiarAspectoRod2();
        cambiarAspectoRod3();

        
     }else if(window.parent.figura1 == window.parent.figura3 && window.parent.figura1 != window.parent.figura2){
        //retencion lados
        cambiarAspectoRod1();
        cambiarAspectoRod3();

        
     }


}

function evaluarEstadoRetenciones(){

     /*
     Si no hay primera tirada con retencion miramos si el back ha dicho que hay 
     que alguna retencion en algun rodillo.
    */

  

        if(window.parent.bet1){

            cambiarAspectoRod1();
    
        }
    
        if(window.parent.bet2){
    
            cambiarAspectoRod2();
    
        }
    
        if(window.parent.bet3){
    
            cambiarAspectoRod3();
    
        }

    

    

}


function cambiarAspectoRod1(){

    var marcoRodillo1 = document.getElementById("rodillo1");
    var btnRet1 = document.getElementById("btnRet1");

    cambiarAspectoElementosRetencion(marcoRodillo1, btnRet1);

}

function cambiarAspectoRod2(){

    var marcoRodillo2 = document.getElementById("rodillo2");
    var btnRet2 = document.getElementById("btnRet2");

    cambiarAspectoElementosRetencion(marcoRodillo2, btnRet2);

}

function cambiarAspectoRod3(){

    var marcoRodillo3 = document.getElementById("rodillo3");
    var btnRet3 = document.getElementById("btnRet3");
    
    cambiarAspectoElementosRetencion(marcoRodillo3, btnRet3);

}

function cambiarAspectoElementosRetencion(rodillo, boton){

    boton.className = "botonRet";
    rodillo.className = "marcoRodilloRet";

}

function sonidoRodillo(){
    
    new Audio('./sounds/golpe_rodillo.mp3').play();
}

function sonidoCambioApuesta(apuesta){


    if(apuesta==0){

        new Audio('./sounds/apuesta1.mp3').play();

    }else if(apuesta==1){

        new Audio('./sounds/apuesta2.mp3').play();

    }else if(apuesta==2){

        new Audio('./sounds/apuesta3.mp3').play();

    }

}






