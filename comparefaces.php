<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COMPARAR ROSTROS</title>
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">

    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css">

        <link rel="stylesheet" type="text/css" href="style.css">

</head>
<style type="text/css">
    body > div {
        text-align: center;
    }
    img{
        max-height: 400px;
        max-width: 400px;
    }
    #resultados{
        font-size: 2em;
        font-weight: bold;
    }
</style>
<body>
<h1>Identificar foto con la de un alumno</h1>
<p>Aquí, se ingresará una foto con un rostro, el cual será comparado con los demás rostros en el grupo de personas de origen, y se obtendrá un resultado diciendo si pertenece o no al grupo dependiendo del grado de confiabilidad de similitud con algún rostro registrado anteriormente</p>
<a class="btn btn-default" href="listgroups.html">Volver a tabla de Grupos</a>
<button onclick="window.history.back();" class="btn btn-primary">Volver a tabla de personas</button>
<hr>
<div>
<h2 id="persongroup"></h2>
</br>
<p>Ingrese una URL de una foto de la persona a comparar con el grupo de personas</p>
<input type="text" id="foto">
<button id="send">Comparar</button></br></br>
<img id="visual" src="">
<p id="resultados"></p>
</div>

<script type="text/javascript">
var groupname = location.search.split('group=')[1];

$("#foto").on("change",function(){
        var link = $("#foto").val();
        $("#visual").attr("src",link);
    });

 $(document).ready(function(){
    document.getElementById("persongroup").innerHTML = "Grupo de Personas "+groupname;
    });

 $("#send").on("click",function(){
    detectarRostro();
 });

    function personaObtenida(confidence,person){
        var params = {
            // Request parameters
        };
      
        $.ajax({
            url: "https://westcentralus.api.cognitive.microsoft.com/face/v1.0/persongroups/"+groupname+"/persons/"+person+"?" + $.param(params),
            beforeSend: function(xhrObj){
                // Request headers
                xhrObj.setRequestHeader("Ocp-Apim-Subscription-Key","70885d89410546a092ca1347194cd7bc");
            },
            type: "GET"
        })
        .done(function(data) {
            document.getElementById("resultados").innerHTML = "CON UN COEFICIENTE DE CONFIANZA DE "+confidence+" LA FOTO INTRODUCIDA SE ASEMEJA AL DE "+data.name;
        })
        .fail(function() {
            alert("error al obtener la identificación de la persona encontrada");
        });
    }



    function compararRostros(facetocompare){
        var params = {
            // Request parameters
        };
        
        var str = '{"personGroupId":"'+groupname+'", "faceIds":["'+facetocompare+'"],"maxNumOfCandidatesReturned":1,"confidenceThreshold":0.5}';
        var req = JSON.parse(str);
        var req2 = JSON.stringify(req);

        $.ajax({
            url: "https://westcentralus.api.cognitive.microsoft.com/face/v1.0/identify?" + $.param(params),
            beforeSend: function(xhrObj){
                // Request headers
                xhrObj.setRequestHeader("Content-Type","application/json");
                xhrObj.setRequestHeader("Ocp-Apim-Subscription-Key","d51f69b3fcb74199aac608a19b165a28");
            },
            type: "POST",
            // Request body
            data: req2
        })
        .done(function(data) {
            console.log("COMPARAR ROSTROS\n"+JSON.stringify(data));
            if(data[0].candidates[0] == undefined){
                document.getElementById("resultados").innerHTML = "NO EXISTEN REGISTROS SIMILARES";
            }else{
            var confianza = data[0].candidates[0].confidence;
            var obtenido = data[0].candidates[0].personId;
        }
            personaObtenida(confianza,obtenido);
        })
        .fail(function() {
            console.log("error en la comparación de rostros");
        });
   }

   function detectarRostro(){
        var params = {
            // Request parameters
            "returnFaceId": "true",
            "returnFaceLandmarks": "false",
            "returnFaceAttributes": "",
        };
        var photoinput = $("#foto").val();
        var str = '{"url":"'+photoinput+'"}';
        var req = JSON.parse(str);
        var req2 = JSON.stringify(req);
      
        $.ajax({
            url: "https://westcentralus.api.cognitive.microsoft.com/face/v1.0/detect?" + $.param(params),
            beforeSend: function(xhrObj){
                // Request headers
                xhrObj.setRequestHeader("Content-Type","application/json");
                xhrObj.setRequestHeader("Ocp-Apim-Subscription-Key","d51f69b3fcb74199aac608a19b165a28");
            },
            type: "POST",
            // Request body
            data: req2
        })
        .done(function(data) {
            entrenarGrupo();
            console.log("DETECTAR ROSTRO\n"+JSON.stringify(data));
            var idface = data[0].faceId;
            compararRostros(idface);
        })
        .fail(function() {
            alert("error en la detección del rostro");
        });
    }

    function entrenarGrupo(){
        var params = {
            // Request parameters
        };
      
        $.ajax({
            url: "https://westcentralus.api.cognitive.microsoft.com/face/v1.0/persongroups/"+groupname+"/train?" + $.param(params),
            beforeSend: function(xhrObj){
                // Request headers
                xhrObj.setRequestHeader("Ocp-Apim-Subscription-Key","d51f69b3fcb74199aac608a19b165a28");
            },
            type: "POST"
        })
        .done(function(data) {
            console.log("SE ENTRENÓ GRUPO EXITOSAMENTE: ESTE PASO ES DE SUMA IMPORTANCIA PARA COMENZAR A HACER COMPARACIONES DE ROSTROS");
        })
        .fail(function() {
            console.log("ERROR AL ENTRENAR GRUPO");
        });
    }

</script>
</body>
</html>

