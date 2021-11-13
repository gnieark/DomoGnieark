
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
    fetch("/DevicesManagerAPI/category/" +  selectedCat + "/model/" + selectedModel + "/needed-to-configure")
    .then (response => response.json())
    .then ( data => { 
        for (var k in data){
            if (typeof data[k] !== 'function') {
                customAwnsersContainer.appendChild(createInputLine(k,data[k]));
            }
        }
    });
}
