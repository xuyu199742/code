<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>content</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0,minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="{{ URL::asset('css/rest.css')}}">
    <link rel="stylesheet" href="{{ URL::asset('css/content.css')}}">
</head>
<body>
<div class="bodyDiv"  style="background:{{ $bgColor }}">
    <div class="content">
        <p >
            <img src="{{ $data['img']}}" alt="" class='titleImg'>
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
            <a  href="javascript:;" class="titleBtn">
                <img src="{{ URL::asset('img/bottom.png')}}" alt="">
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
        <a href="javascript:;" class="backA">
            <img src="{{ URL::asset('img/close.png')}}" alt="">
        </a>
    </div>
</div>
</body>
<script src="{{ URL::asset('js/jquery-3.4.1.min.js')}}"></script>
<script>
  function refresh(){
    var w =  document.body.clientWidth;//获取页面可见高度
    for (var i =0 ;i <document.getElementsByClassName('titleImg').length;i++){
      document.getElementsByClassName('titleImg')[i].style.height =w*0.302+'px';
    }
  }
  window.addEventListener("resize", refresh);
  refresh()
</script>
</html>