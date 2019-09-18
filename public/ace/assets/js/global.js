// 设计稿横向尺寸
var psWidth = 750;
var bodyWidth = psWidth / 100;
var deviceWidth = document.documentElement.clientWidth;
// 设置font-size
if (deviceWidth > 750) {
    deviceWidth = 750;
    document.documentElement.style.fontSize = deviceWidth / 7.5 + 'px';
} else {
    document.documentElement.style.fontSize = deviceWidth / bodyWidth + "px";
}
// 判断移动设备是安卓还是ios
function andriodOrios() {
    var Navigator = navigator.userAgent;
    if (Navigator.indexOf('Android') > -1 || Navigator.indexOf('Linux') > -1) {
        return "andriod"
    } else if (Navigator.match(/iPhone|iPad|iPd/i)) {
        return "ios"
    }
}
// app版本过低时跳转下载
function toDownloadApp() {
    var type=andriodOrios();
    if(type=="ios"){
        // window.location.href="https://itunes.apple.com/cn/app/%E7%AB%A5%E6%A2%A6%E6%97%A0%E5%BF%A7-%E8%AF%95%E7%AE%A1%E5%A9%B4%E5%84%BF%E7%A4%BE%E5%8C%BA/id1251854250?mt=8";
        // window.location.href="itms-apps://itunes.apple.com/app/id1251854250";
        window.location.href="https://itunes.apple.com/cn/app/id1251854250";
    }else if(type=="andriod"){
        window.location.href="http://sj.qq.com/myapp/detail.htm?apkName=com.app.childus";
    }
}
// 新版本没登录或者登录过期
function toLoginPage(){
    var type=andriodOrios();
    if(type=="ios"){
        window.location.href="BestTongM://gotoLogin";
    }else if(type=="andriod"){
        window.android.toLogin();
    }
}
// 获取get带过来的参数
function GetRequest() {
    var url = location.search; //获取url中"?"符后的字串 
    // var url = "https://www.tm51.com/share/index.html?token=1fb5bc5b6dc57da0afc9d3945fbbd4ffa521d067&client=3&isNew=1" //获取url中"?"符后的字串 
    // var url = "https://www.tm51.com/share/index.html?token=&client=&isNew=1" //获取url中"?"符后的字串 
    // var url = "https://www.tm51.com/share/index.html" //获取url中"?"符后的字串 
    var theRequest = new Object();
    if (url.indexOf("?") != -1) {
        var str = url.substr(url.indexOf("?") + 1);
        strs = str.split("&");
        for (var i = 0; i < strs.length; i++) {
            theRequest[strs[i].split("=")[0]] = unescape(strs[i].split("=")[1]);
        }
    }
    return theRequest;
}
///////////////////////////////////////////////////////////////////////////////////////////////////////
// 接口列表
var urlList = {
    postDetails:" /web/post/postDetail"
}
// 接口域名
// var globalApi = "http://test.mnapi.tm51.com";
var globalApi = "https://mnapi.tm51.com";
// 生成sign
function signs(param) {
    var arr = []
    for (var key in param) {
        arr.push(key)
    }
    return hex_sha1(arr.sort().join('').replace(/,/g, "") + "02350a1c314479f5ef6356032e9fdadb")
};
// 请求数据公共函数
function getData(type, url, params) {
    var data = Object.assign({
        sign: signs(params),
    }, params)
    if (type == "get") {
        var arr = [];
        for (var i in data) {
            arr.push(i + "=" + data[i])
        }
        data = arr.join("&");
        return axios({
            method: "get",
            url: globalApi + url + "?" + data,
        })
    } else if (type == "post") {
        return axios({
            method: "post",
            url: globalApi + url,
            data: data
        })
    }
}