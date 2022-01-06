<!DOCTYPE html>
<html lang="en" style="">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0,minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>list</title>
    <link rel="stylesheet" href="../../css/rest.css">
    <link rel="stylesheet" href="../../css/list.css">
</head>
<body style="">
<div class="listDiv" id='iframeID' style="background:{{ $bgColor }};">
    @foreach ($data as $val)
    <div class="content" id="id{{$val['id']}}">
        <div class="imgP">
            @if(env('PAY_CALLBACK_URL'))
                <div style='width: 100%;' onclick="location='{{env('PAY_CALLBACK_URL')}}/activityDetail/{{$val['id']}}'" >
                  <img src="{{ $val['img']}}" alt="" class='titleImg' style='width: 100%;' >
                </div>
            @else
                <div style='width: 100%;' onclick="location='{{url('activityDetail')}}/{{$val['id']}}'">
                  <img src="{{ $val['img']}}" alt=""  class='titleImg' style='width: 100%;' >
                </div>
            @endif
            <span onclick="kefuclick()" class="kefuSpan">
               <img src="../../img/kefu.png" alt="">
            </span>
        </div>
        @if(env('PAY_CALLBACK_URL'))
            <div class="titleDiv"  onclick="location='{{env('PAY_CALLBACK_URL')}}/activityDetail/{{$val['id']}}'">
        @else
            <div class="titleDiv"  onclick="location='{{url('activityDetail')}}/{{$val['id']}}'">
        @endif
            <ul>
                <li>
                    <p class="titleP">
                        {{$val['name']}}
                    </p>
                </li>
                <li>
                    <p class="timeP">
                        发布时间：<span>{{$val['created_at']}}</span>
                    </p>
                </li>
            </ul>
            @if(env('PAY_CALLBACK_URL'))
                <a onclick="location='{{env('PAY_CALLBACK_URL')}}/activityDetail/{{$val['id']}}'" class="titleBtn">
            @else
                <a onclick="location='{{url('activityDetail')}}/{{$val['id']}}'" class="titleBtn">
            @endif
                <img src="../../img/right.png" alt="">
                </a>
            </div>
    </div>
    @endforeach
</div>
</body>
<script src="../../js/jquery-3.4.1.min.js"></script>
<script>
    //声明一个控制点击的变量
    var upLoadClicktag = 0;
    //点击事件
    function hrefclick(val) {
        if(upLoadClicktag==0){
            //改版变量
            upLoadClicktag = 1;
            window.open(val,'_blank')
            //3秒过后可以再次点击
            setTimeout(function () { upLoadClicktag = 0 }, 500);
        }
    }

    var url=window.location.pathname.split("/");
    function kefuclick(){
        if (url[2]=='u3d'){
            document.location = "uniwebview://open?kf=1"
        }else if (url[2]=='mobile') {
            document.location = 'testkey://a=1&b=2';
        }else if (url[2]=='h5') {
            parent.postMessage("kf",'*')
        }else{

        }
    }

    function redirectUrl(url)
    {
        top.location.href = url;
    }
    function refresh(){
        var w =  document.body.clientWidth;//获取页面可见高度
        for (var i =0 ;i <document.getElementsByClassName('titleImg').length;i++){
            document.getElementsByClassName('titleImg')[i].style.height =w*0.302+'px';
        }
    }
    window.addEventListener("resize", refresh);
    refresh();
</script>
<script>
    $(window).scroll(function(){
        if($(document).scrollTop()!=0){
            localStorage.setItem("offsetTop", $(window).scrollTop());
        }
    });
    //onload时，取出并滚动到上次保存位置
    window.onload = function(){
        var activityUrl = localStorage.getItem("activityUrl");
        if(location.href != activityUrl) { console.log(location.href)
            reloadList();
            localStorage.setItem("activityUrl", location.href);
        }
        var offset = localStorage.getItem("offsetTop");
        $(document).scrollTop(offset);
    };
    //删除定位
    function reloadList() {
        localStorage.removeItem('offsetTop');
        $(document).scrollTop(0);
        location.reload();
    }
</script>
</html>
</html>
