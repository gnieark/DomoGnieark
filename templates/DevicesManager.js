
document.addEventListener('DOMContentLoaded', (event) => {
    //the event occurred
    document.querySelector('#SelectCatDevices').addEventListener('change', changeCat);
    document.querySelector('#addDeviceModel').addEventListener('change', changeModel);

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
    return select.options[ select.selectedIndex ].value;
}
function changeCat(e)
{
    let selectedCat = getSelectedValueOnSelect( document.querySelector('#SelectCatDevices') );

    let selectModels = document.querySelector('#addDeviceModel');
    
    fetch ("index.php?menu=DevicesManagerAPI&list=models&category=" + selectedCat )
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
            opt.text = model["displayName"];
            opt.value = model["name"];
            selectModels.add(opt,null);
        });

    });
}
function createInputLine(key,data)
{
    let p = createElem("p",{});
    let label = createElem("label",{"for" : "custom" + key});
    label.innerHTML = data.displayName;
    p.appendChild(label);

    switch (data.type)
    {
        case 'string':
            let inputStr = createElem("input",{"type": "text", "name": key, "id": "custom" + key});
            p.appendChild(inputStr);
            break;
        case 'int':
            let inputInt = createElem("input", {"type": "number", "min": data.min,"max": data.max, "name": key, "id": "custom" + key});
            p.appendChild(inputInt);
            break;
        case 'enum':
            

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
    fetch("index.php?menu=DevicesManagerAPI&list=needed-to-configure&category=" + selectedCat  + "&model=" + selectedModel )
    .then (response => response.json())
    .then ( data => { 
        for (var k in data){
            if (typeof data[k] !== 'function') {
                customAwnsersContainer.appendChild(createInputLine(k,data[k]));
            }
        }
    });
}
