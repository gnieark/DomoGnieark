document.addEventListener('DOMContentLoaded', (event) => {
    //the event occurred
    document.querySelectorAll(".switchButton").forEach((img) => {
        setInterval(updateSwitchStatus, 2000,img);
        img.addEventListener('click', turn) }
        
        );

    document.querySelector(".switchButton").addEventListener('click', onoff);
})

function updateSwitchStatus(img){
    let id = img.id.split('-')[1];
    fetch ("/DevicesManagerAPI/device/" +  id + "/status")
    .then (response => response.json())
    .then ( data => { 
        switch (data["status"]){
            case "on":
                img.src = "/imgs/switch-on.svg";
                img.alt = "light is on";
                img.addEventListener('click', turnOff);
                img.removeEventListener('click', turnOn);
                img.removeEventListener('click', turn);
                break;
            case "off":
                img.src  = "/imgs/switch-off.svg";
                img.alt = "light is off";
                img.addEventListener('click', turnOn);
                img.removeEventListener('click', turnOff);
                img.removeEventListener('click', turn);
                break;
            default:
                img.src  = "/imgs/switch-unknow.svg";
                img.alt = "light status is unknowed";
                img.addEventListener('click', turn);
                img.removeEventListener('click', turnOn);
                img.removeEventListener('click', turnOff);
                break;

        }     
    });
}
function turnOff(e){
    let id = e.currentTarget.id.split('-')[1];
    sendturnRequest(id,"off");
}
function turnOn(e){
    let id = e.currentTarget.id.split('-')[1];
    sendturnRequest(id,"on");
}
function turn(e){
    let id = e.currentTarget.id.split('-')[1];
    sendturnRequest(id,"turn");
}
function sendturnRequest(deviceId, onOffTurn)
{
    fetch ("/DevicesManagerAPI/device/" +  deviceId , 
        {method: "POST"
        ,headers: { 'Accept': 'application/json'
                  ,'Content-Type': 'application/json'
                  }
        ,body: JSON.stringify({"status": onOffTurn})
        })
        .then (response => response.json())
        .then ( data => { 
           if(data["error"] !== 0){
               alert ("An error has occured");
           } 
        });
}
