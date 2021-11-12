
document.addEventListener('DOMContentLoaded', (event) => {
    //the event occurred
    let theCatSelect = document.querySelector('#SelectCatDevices');
    theCatSelect.addEventListener('change', changeCat);
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

        data.forEach( function(model) {
            let opt = document.createElement("option");
            opt.text = model["displayName"];
            opt.value = model["name"];
            selectModels.add(opt,null);
        });

    });
}
