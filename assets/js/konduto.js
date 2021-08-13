const period = 300;
const limit = 20 * 1e3;
let nTry = 0;
const intervalID = setInterval(() => {
    const kondutoObj = (window).Konduto;
    let clear = limit / period <= ++nTry;
    if (
        typeof kondutoObj !== "undefined" &&
        typeof kondutoObj.getVisitorID !== "undefined"
    ) {
        const visitorID = kondutoObj.getVisitorID();
        //this.kondutoVisitorID = visitorID;
        console.log("loadKonduto");
        console.log(visitorID);
        document.getElementById('antifraud_token').setAttribute('value',visitorID);
        clear = true;
    }
    if (clear) {
        clearInterval(intervalID);
    }
}, period);