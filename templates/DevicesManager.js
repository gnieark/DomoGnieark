
document.addEventListener('DOMContentLoaded', (event) => {
    //the event occurred
    document.querySelector('#SelectCatDevices').addEventListener('change', changeCat);
    document.querySelector('#addDeviceModel').addEventListener('change', changeModel);
    changeCat();
    changeModel();
})

function createElem(type,attributes)
{
    var elem=document.createElement(type);
    for (var i in attributes)
    {elem.setAttribute(i,attributes[i]);}
    return elem;
}

function getSelectedValueOnSelect( select )
{
    if( select.selectedIndex == -1 ){
        return -1;
    }
    return select.options[ select.selectedIndex ].value;
}
function changeCat(e)
{
    let selectedCat = getSelectedValueOnSelect( document.querySelector('#SelectCatDevices') );
    if (selectedCat == -1){
        return;
    }

    let selectModels = document.querySelector('#addDeviceModel');
    
    fetch ("/DevicesManagerAPI/category/" +  selectedCat + "/models")
    .then (response => response.json())
    .then ( data => {      
        //empty the select:
        while (selectModels.hasChildNodes()) {
            selectModels.removeChild(selectModels.lastChild);
        }
        let opt = document.createElement("option");
        opt.text = "";
        opt.value = "";
        selectModels.add(opt,null);
        data.forEach( function(model) {
            let opt = document.createElement("option");
            opt.text = model["display_name"];
            opt.value = model["name"];
            selectModels.add(opt,null);
        });

    });
}
function createInputLine(key,data)
{
    let p = createElem("p",{});
    let label = createElem("label",{"for" : "custom" + key});
    label.innerHTML = data.display_name;
    p.appendChild(label);

    switch (data.type)
    {
        case 'string':
            let inputStr = createElem("input",{"type": "text", "name": key, "id": "custom" + key});
            if (typeof data.default !== 'undefined') {
                inputStr.value = data.default;
            }
            p.appendChild(inputStr);
            break;
        case 'int':
            let inputInt = createElem("input", {"type": "number", "min": data.min,"max": data.max, "name": key, "id": "custom" + key});
            if (typeof data.default !== 'undefined') {
                inputInt.value = data.default;
            }
            p.appendChild(inputInt);
            break;
        case 'enum':
            let inputSelect = createElem("select",{"name": key, "id": "custom" + key});
            data.enum.forEach(function(item){
              let opt = document.createElement("option");
              opt.value = item;
              opt.text = item;
              if ((typeof data.default !== 'undefined') &&  (data.default == item)) {
                opt.selected = "selected";
              }

              inputSelect.add(opt);
            });
            p.appendChild(inputSelect);
            break;
        default:

            break;

    }

    return p;
}
function changeModel(e)
{
    let customAwnsersContainer = document.querySelector('#deviceCustomAwnswers');
    while (customAwnsersContainer.hasChildNodes()) {
        customAwnsersContainer.removeChild(customAwnsersContainer.lastChild);
    }
    let selectedCat = getSelectedValueOnSelect( document.querySelector('#SelectCatDevices') );
    let selectedModel = getSelectedValueOnSelect( document.querySelector('#addDeviceModel') );
    if ( (selectedModel == -1) || (selectedModel == '') ){
        return false;
    }  
    fetch("/DevicesManagerAPI/category/" +  selectedCat + "/model/" + selectedModel)
    .then (response => response.json())
    .then ( data => { 
        let mqttServer_needed = false;
        if( typeof data.mqttServer_needed !== 'undefined' ){
            if ( data.mqttServer_needed == true){
                mqttServer_needed = true;
            }  
        }

        let autoDiscoverMethod = false;
        if( typeof data.autoDiscoverMethod !== 'undefined' ){
            if( data.autoDiscoverMethod == true ){
                autoDiscoverMethod = true;
            }  
        }

        if(mqttServer_needed)
        {
            let p = createElem("p",{});
            let label = createElem("label",{});
            label.innerHTML = "Choose a mqtt server";
            p.appendChild(label);
            let select = createElem("select",{"name":"mqttserver_id", "id" : "mqttserver_id"});
            p.appendChild(select);
            customAwnsersContainer.appendChild(p);

            //add a select with configured mqqt servers
            fetch("/DevicesManagerAPI/category/mqtt/model/mqttServer/devices")
            .then (response => response.json())
            .then ( devices => {
                for( var k in devices ){
                    let opt = document.createElement("option");
                    opt.value = devices[k]["id"];
                    opt.text = devices[k]["display_name"];
                    document.getElementById("mqttserver_id").appendChild(opt);
                }

            });
        }
        if(autoDiscoverMethod)
        {
            //make two tabs (one for autodiscover and the normal config)
            let autodiscoverContainer = createElem("article", {"class" : "autodiscover"});
            let autoDiscoverTitle = createElem("h4",{});
            autoDiscoverTitle.innerHTML = "Autodiscover Method";
            autodiscoverContainer.appendChild(autoDiscoverTitle);
            fetch("/DevicesManagerAPI/AutoDiscover-mqtt")
            .then (response => response.json())
            .then ( devices => {
                for( var k in devices ){

                }

            });
            //deviceCustomAwnswers
            document.getElementById("deviceCustomAwnswers").parentNode.insertBefore(autodiscoverContainer,document.getElementById("deviceCustomAwnswers"));
        }
        for (var k in data["needed-to-configure"]){
            if (typeof data["needed-to-configure"][k] !== 'function') {
                customAwnsersContainer.appendChild(createInputLine(k,data["needed-to-configure"][k]));
            }
        }

    });
}
