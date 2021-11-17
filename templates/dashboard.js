document.addEventListener('DOMContentLoaded', (event) => {
    //the event occurred
    document.querySelector(".switchButton").updateSwitchStatus();
    document.querySelector(".switchButton").addEventListener('click', changeStatus);
})

function updateSwitchStatus(){

}