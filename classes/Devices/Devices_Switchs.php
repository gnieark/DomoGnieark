<?php
class Devices_Switchs extends Devices {
    
    protected $availableMethods = ["On", "Off", "Get_Status"];
    const SGV_SWITCH_OFF = '<svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
    viewBox="0 0 490 490" style="enable-background:new 0 0 490 490;" xml:space="preserve"><g>
    <path d="M350.2,384.8c77.2,0,139.8-62.6,139.8-139.8s-62.6-139.8-139.8-139.8H139.8C62.6,105.2,0,167.8,0,245
    s62.6,139.8,139.8,139.8H350.2z M48,245c0-51.7,41.9-93.7,93.7-93.7c51.7,0,93.7,41.9,93.7,93.7s-41.9,93.7-93.7,93.7
    C90,338.7,48,296.7,48,245z"/>
    </g>
    </svg>';
    const SVG_SWITCH_ON = '<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
    viewBox="0 0 280.028 280.028" style="enable-background:new 0 0 280.028 280.028;" xml:space="preserve">
<g>
   <path style="fill:#3DB39E;" d="M87.509,52.505h105.01c48.331,0,87.509,39.178,87.509,87.509c0,48.313-39.178,87.509-87.509,87.509
       H87.509C39.186,227.522,0,188.327,0,140.014C0,91.691,39.186,52.505,87.509,52.505z"/>
   <path style="fill:#EBEBEB;" d="M192.519,78.758c33.831,0,61.256,27.425,61.256,61.256c0,33.84-27.425,61.256-61.256,61.256
       c-33.822,0-61.256-27.416-61.256-61.256C131.263,106.183,158.697,78.758,192.519,78.758z"/>
       </g></svg>';
    const SVG_SWITCH_UNKNOW= '<svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
    viewBox="0 0 490 490" style="enable-background:new 0 0 490 490;" xml:space="preserve"><g>
    <path style="fill:grey;" d="M350.2,384.8c77.2,0,139.8-62.6,139.8-139.8s-62.6-139.8-139.8-139.8H139.8C62.6,105.2,0,167.8,0,245
    s62.6,139.8,139.8,139.8H350.2z M48,245c0-51.7,41.9-93.7,93.7-93.7c51.7,0,93.7,41.9,93.7,93.7s-41.9,93.7-93.7,93.7
    C90,338.7,48,296.7,48,245z"/>
    </g>
    </svg>';

}