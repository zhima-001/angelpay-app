(function (doc, win) {
    let docEL = doc.documentElement;
    let resizeEvent = 'orientationchange' in window ? 'orientationchange' : 'resize',
        recalc = function () {
            const clientWidth = docEL.clientWidth;
            if (!clientWidth) return;
            if (clientWidth > 750) {
                docEL.style.fontSize = '100px'
            } else {
                docEL.style.fontSize = (clientWidth / 750) * 100 + 'px'
            }
        }
        recalc();
        win.addEventListener(resizeEvent,recalc,false);
        doc.addEventListener("DOMContentLoaded",recalc,false)
})(document, window)