document.addEventListener('DOMContentLoaded', (event) => {
    //the event occurred
    document.querySelector(".switchButton").getSwitchStatus();
    document.querySelector(".switchButton").addEventListener('click', changeStatus);
})

function getSwitchStatus(){
    
}