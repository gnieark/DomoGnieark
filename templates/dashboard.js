document.addEventListener('DOMContentLoaded', (event) => {
    //the event occurred
    document.querySelectorAll(".switchButton").forEach((img) => {
        updateSwitchStatus(img);
        img.addEventListener('click', onoff) }
        
        );

    document.querySelector(".switchButton").addEventListener('click', onoff);
})

function updateSwitchStatus(img){
    let id = img.id.split('-')[1];
    fetch ("/DevicesManagerAPI/device/" +  id + "/status")
    .then (response => response.json())
    .then ( data => {      
        console.log(data);

    });
}
function onoff(e){
    console.log(e);
}