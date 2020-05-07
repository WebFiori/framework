function smoothHide(el){
    var o = 1;
    var intrvalId = setInterval(function(){
        if(o > 0){
            o = o - 0.02;
            el.style['opacity'] = o;
        } else {
            clearInterval(intrvalId);
            el.style['display'] = 'none';
        }
    },15);
};
function addDragSupport(source){
    source.setAttribute("dg",true);
    var pos1 = 0, pos2 = 0, pos3 = 0, pos4 = 0;
    var boxNum = source.getAttribute("data-box-number");
    if (boxNum === null) {
        source.onmousedown = mouseDown;
    } else {
        source.children[0].onmousedown = mouseDown;
    }
    function mouseDown(e) {
        e = e || window.event;
        e.preventDefault();
        pos3 = e.clientX;
        pos4 = e.clientY;
        document.onmouseup = dragStopped;
        document.onmousemove = dragStarted;
    };
    function dragStarted(e) {
        e = e || window.event;
        e.preventDefault();
        pos1 = pos3 - e.clientX;
        pos2 = pos4 - e.clientY;
        pos3 = e.clientX;
        pos4 = e.clientY;
        source.style.top = (source.offsetTop - pos2) + "px";
        source.style.left = (source.offsetLeft - pos1) + "px";
    };
    function dragStopped(){
        document.onmouseup = null;
        document.onmousemove = null;
    };
};