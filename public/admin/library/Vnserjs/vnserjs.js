/**
 * Created by Administrator on 16-7-14.
 */
var _global = {};
/**
 * 限制数字
 * */
function limitNum(t){
    //限制输入数字
    var con = $(t).val().replace(/[^\d]/g,'');
    $(t).val(con);
}
/**
 * 检测是否为空
 * */
function checkNull(t){
    //检测是否为空
    t = t==undefined?'[type=text],[type=password],textarea':t;
    var flag = true;
    $(t).each(function(){
        var thisObj = $(this);
        if(thisObj.prop('disabled'))
            return;
        var border = thisObj.attr('org_border');
        if(border == undefined){
            border = {};
            border['border-width'] = thisObj.css('border-width');
            border['border-style'] = thisObj.css('border-style');
            border['border-color'] = thisObj.css('border-color');
            thisObj.attr('org_border',JSON.stringify(border))
        }else{
            border = jQuery.parseJSON(border);
        }
        thisObj.css(border);
        if(thisObj.val().trim().length == 0){
            thisObj.css(border);
            thisObj.css('border-color','red');
            flag = false;
            return false;
        }
    })
    return flag;
}
/*
* 全选方法
* */
function Check(all,son){
    this.allchk = $(all);
    this.sonchk = $(son);
    this.allcheck = function(){
       this.sonchk.prop('checked',this.allchk.prop('checked'));
    }
    this.soncheck = function(){
        //子子节点总长
        var count = this.sonchk.length;
        //已选子子节点总长
        var counted = 0;
        for(k in this.sonchk){
            if(this.sonchk[k].checked)
                counted++;
        }
        this.allchk.prop('checked',count == counted);
    }
    this.allchk.on('click',this.allcheck.bind(this));
    this.sonchk.on('click',this.soncheck.bind(this));
}

/*
* 检查文件名是否合法
* */
function checkFileExt(filename,ext_arr) {
    var flag = false; //状态
    var arr = ext_arr;
    //取出上传文件的扩展名
    var index = filename.lastIndexOf(".");
    var ext = filename.substr(index+1);
    //循环比较
    for(var i=0;i<arr.length;i++) {
        if(ext.toLocaleLowerCase() == arr[i].toLocaleLowerCase ()) {
            return true; //一旦找到合适的，立即退出循环
        }
    }
    return false;
}
