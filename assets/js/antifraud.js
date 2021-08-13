
function getAntiFraudType() {
    return window.antifraude_type
}
function getAntiFraudId() {
    return window.antifraude_id
}
function loadKonduto() {
    try {
        (() => {
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
        })();
        return;
    } catch (error) {
        console.log(error)
        return;
    }
}

function loadClearSale(publicKey) {
    try {
        (() => {
            const period = 300;
            const limit = 20 * 1e3;
            let nTry = 0;
            const intervalID = setInterval(() => {
                const csdpObj = (window).csdp;
                const csdmObj = (window).csdm;

                let clear = limit / period <= ++nTry;
                if (
                    typeof csdpObj !== "undefined" &&
                    typeof csdmObj !== "undefined" &&
                    typeof publicKey !== "undefined"
                ) {
                    (window).csdp("app", publicKey);
                    (window).csdp("outputsessionid", "antifraud_token");
                    (window).csdm('app', publicKey);
                    (window).csdm('mode', 'manual');
                    (window).csdm("send", "checkout");
                    // console.log('clearsaleSessionId');
                    // var visitorID = document.getElementById('clearsaleSessionId').getAttribute('value');
                    // console.log(visitorID);
                    // document.getElementById('antifraud_token').innerHTML = visitorID;
                    clear = true;
                }
                if (clear) {
                    clearInterval(intervalID);
                }
            }, period);
        })();
        return;
    } catch (error) {
        return;
    }
}
if (getAntiFraudType()=='konduto') {
    try {
        const pk = getAntiFraudId();
        // @ts-ignore
        window.__kdt = window.__kdt || [];
        // @ts-ignore
        window.__kdt.push({public_key: pk});
        ((a, b, c, d, e, f, g) => {
            a['KdtObject'] = e;
            a[e] = a[e] || function () {
                (a[e].q = a[e].q || []).push(arguments)
            }
            // @ts-ignore
            a[e].l = 1 * new Date();
            f = b.createElement(c),
                g = b.getElementsByTagName(c)[0];
            // @ts-ignore
            f.src = d;
            // @ts-ignore
            f.async = true;
            // @ts-ignore
            g.parentNode.insertBefore(f, g)
        })(window, document, 'script', 'https://i.k-analytix.com/k.js?now=' + Date.now(), 'csdp');
        loadKonduto();
    } catch (error) {
    }
}
// CLear Sale
console.log(getAntiFraudType())
if (getAntiFraudType()=='clearsale') {
    ((a, b, c, d, e, f, g) => {
        a['CsdpObject'] = e;
        a[e] = a[e] || function () {
            (a[e].q = a[e].q || []).push(arguments)
        }
        // @ts-ignore
        a[e].l = 1 * new Date();
        f = b.createElement(c),
            g = b.getElementsByTagName(c)[0];
        // @ts-ignore
        f.src = d;
        // @ts-ignore
        f.async = true;
        // @ts-ignore
        g.parentNode.insertBefore(f, g)
    })(window, document, 'script', '//device.clearsale.com.br/p/fp.js?now=' + Date.now(), 'csdp');
    (function (a, b, c, d, e, f, g) {
        a['CsdmObject'] = e;
        a[e] = a[e] || function () {
            (a[e].q = a[e].q || []).push(arguments)
        };
        // @ts-ignore
        a[e].l = 1 * new Date();
        f = b.createElement(c);
        g = b.getElementsByTagName(c)[0];
        // @ts-ignore
        f.src = d;
        // @ts-ignore
        f.async = true;
        // @ts-ignore
        g.parentNode.insertBefore(f, g);
    })(window, document, 'script', '//device.clearsale.com.br/m/cs.js?now=' + Date.now(), 'csdm')
    loadClearSale(getAntiFraudId());
}