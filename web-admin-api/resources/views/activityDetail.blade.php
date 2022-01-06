<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>content</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0,minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="../../css/rest.css">
    <link rel="stylesheet" href="../../css/content.css">
</head>
<body>
<div class="bodyDiv"  style="background:{{ $bgColor }}">
    <div class="content">
        <p class='clickBack' onclick="clickBack()">
            <img src="{{ $data['img']}}" alt=""  class='titleImg'>
        </p>
        <div class="titleDiv">
            <ul>
                <li>
                    <p class="titleP">
                        {{$data['name']}}
                    </p>
                </li>
                <li>
                    <p class="timeP">
                        发布时间：<span>{{$data['created_at']}}</span>
                    </p>
                </li>
            </ul>
            <a href="javascript:;" class="titleBtn clickBack" onclick="clickBack()">
                <img src="../../img/bottom.png" alt="">
            </a>
        </div>
    </div>
    <div class="contentDiv">
        <div class="textP">
            @if($data['is_img'] == 1)
                <img src="{{$data['img2']}}"  style="margin: 0 auto;display: block"/>
            @else
                <?php echo($data['content']);?>
            @endif
        </div>
    </div>
    <div class="footDiv">
        <a href="javascript:;" class="backA clickBack" onclick="clickBack()">
            <img src="../../img/close.png" alt="">
        </a>
    </div>
</div>
</body>
<script src="../../js/jquery-3.4.1.min.js"></script>
<script>
  function redirectUrl(url)
  {
      top.location.href = url;
  }
  function clickBack(url) {
//      var ref = "";
//      if (document.referrer.length > 0) {
//          ref = document.referrer;
//      }
//      if (ref.length == 0 && opener.location.href.length > 0) {
//          ref = opener.location.href;
//      }
//      alert(ref)
//      top.location.href = ref + url;
      history.back(-1);
  }

  function refresh(){
        var w =  document.body.clientWidth;//获取页面可见高度
        for (var i =0 ;i <document.getElementsByClassName('titleImg').length;i++){
          document.getElementsByClassName('titleImg')[i].style.height =w*0.302+'px';
        }
      }
  window.addEventListener("resize", refresh);
  refresh()
</script>
<script>
  $(window).scroll(function(){
    if($(document).scrollTop()!=0){
      localStorage.setItem("offsetTop", $(window).scrollTop());
    }
  });
  //onload时，取出并滚动到上次保存位置
  window.onload = function(){
    var offset = localStorage.getItem("offsetTop");
    $(document).scrollTop(offset);
  };
</script>
</html>
