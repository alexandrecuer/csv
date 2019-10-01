<style>
pre {
    width:100%;
    height:200px;
    margin:0px;
    padding:0px;
    color:#fff;
    background-color:#300a24;
    overflow: scroll;
    overflow-x: hidden;
}
</style>

<div id="wrapper">

  <div style="height:10px"></div>

  <div style="padding:20px">

    <h2>Csv Module</h2>
    <p>Import csv files in Emoncms</p>
    
    <div id="csv_list"></div>
    
    <div id="csv_options"></div>
    
    <pre><div id="result"></div></pre>

  </div>
</div>

<script>
//console.warn(path);

var csvs = [];
$.ajax({ url: path+"csv/list", dataType: 'json', async: false, success: function(result) {csvs = result;} });

if(typeof(csvs)=="string")
    var csv_select = csvs;
else {
    var csv_select = "<select id='csv_select'><option value='-1'>Select csv:</option>";
    for (var z in csvs) {
        csv_select += "<option value="+z+">"+csvs[z]['name']+"</option>";
    }
    csv_select +="</select>";
}

$("#csv_list").html(csv_select);

$("#csv_select").change(function(){
    var csv_id = $(this).val();
    if (!csvs[csv_id]) {
        $("#csv_options").html("");
        return false;
    }
    
    //the object containing all the feed names
    var ftocreate;
    $.ajax({
        type: "POST",
        url: path+"csv/getfeeds", 
        data: JSON.stringify(csvs[csv_id]['name']),
        dataType: 'text', 
        async: false, 
        success: function(result) {
            ftocreate=JSON.parse(result);
            //console.log(result); 
        } 
    });
    
    var csv_options = "<select id='feed_select'><option value='-1'>Select column to import:</option>";
    for (var z in ftocreate) {
        csv_options += "<option value="+z+">"+ftocreate[z]+"</option>";
    }
    csv_options +="<select><br><button id='import' class='btn'>Import</button><br><br>";
    
    $("#csv_options").html(csv_options);
    
    $("#import").click(function(){
        var feed_number = $("#feed_select").val();
        var params = {};
        params['name']=csvs[csv_id]['name'];
        params['feednbr']=feed_number;
        //console.warn(params);
        $.ajax({
            type: "POST",
            url: path+"csv/create", 
            data: JSON.stringify(params),
            dataType: 'text', 
            async: false, 
            success: function(result) {
                $("#result").html(result);
                //replace(/"|\\/g, '')
            } 
        });
    });
    
});

    



</script>
