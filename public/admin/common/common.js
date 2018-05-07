function o2o_open(title,url,w,h){
    if (w == null || w == '') {
        w=800;
    };
    if (h == null || h == '') {
        h=($(window).height() - 50);
    };
    layer.open({
        type: 2,
        title: title,
        maxmin: true,
        area: [w+'px', h +'px'],
        content: url,
    });

}