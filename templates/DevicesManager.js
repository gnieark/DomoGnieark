
document.addEventListener('DOMContentLoaded', (event) => {
    //the event occurred
    document.querySelector('#SelectCatDevices').addEventListener('change', changeCat);
    document.querySelector('#addDeviceModel').addEventListener('change', changeModel);

})

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

function changeModel(e)
{
    let selectedCat = getSelectedValueOnSelect( document.querySelector('#SelectCatDevices') );
    let selectedModel = getSelectedValueOnSelect( document.querySelector('#addDeviceModel') );
    fetch("index.php?menu=DevicesManagerAPI&list=needed-to-configure&category=" + selectedCat  + "&model=" + selectedModel )
    .then (response => response.json())
    .then ( data => { 

    });
}
