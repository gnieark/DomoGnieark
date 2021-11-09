
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
    alert(selectedCat);
}