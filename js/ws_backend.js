function process_order(){

    var e = document.getElementById("ws_rate");
   
    var strId = e.options[e.selectedIndex].value;
    var s = document.getElementById("swipbox");

    var cur_url = document.URL.split("&webshipr_process=true")[0].split("&webshipr_reprocess=true")[0].split("&webshipr_change=true")[0].split("&webshipr_droppoint=true")[0];

    if(s !== null){
        var strS = s.options[s.selectedIndex].value;
    }
    

    if(s !== null){
        window.location = cur_url + "&webshipr_process=true&ws_rate="+strId+"&swipbox="+strS;
    }else{
        window.location = cur_url + "&webshipr_process=true&ws_rate="+strId;
    }
}

function reprocess_order(){
    var e = document.getElementById("ws_rate");
    var strId = e.options[e.selectedIndex].value;

    var cur_url = document.URL.split("&webshipr_process=true")[0].split("&webshipr_reprocess=true")[0].split("&webshipr_change=true")[0].split("&webshipr_droppoint=true")[0];

    window.location = cur_url + "&webshipr_reprocess=true&ws_rate="+strId;
}

function change_order(){
    var e = document.getElementById("ws_rate");
    var strId = e.options[e.selectedIndex].value;
    var strName = e.options[e.selectedIndex].text;

    var cur_url = document.URL.split("&webshipr_process=true")[0].split("&webshipr_reprocess=true")[0].split("&webshipr_change=true")[0].split("&webshipr_droppoint=true")[0];

    window.location = cur_url + "&webshipr_change=true&ws_rate="+strId+"&name="+strName;
}

function set_droppoint(){
    var e = document.getElementById("ws_droppoint");
    var adrInfo = e.options[e.selectedIndex].value.split(".");

    var cur_url = document.URL.split("&webshipr_process=true")[0].split("&webshipr_reprocess=true")[0].split("&webshipr_change=true")[0].split("&webshipr_droppoint=true")[0];

    window.location = cur_url + "&webshipr_droppoint=true&dp_id="+adrInfo[0]+
        "&dp_street="+adrInfo[1]+
        "&dp_zip="+adrInfo[2]+
        "&dp_city="+adrInfo[3]+
        "&dp_name="+adrInfo[4]+
        "&dp_country="+adrInfo[5];
}
