<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>充值界面</title>
    <script src="/static/frontend/js/jquery.min.js"></script>
    <script src="/static/frontend/js/jquery.qrcode.min.js"></script>
    <style>
        .qcode {
            border: 1px solid #0C0C0C;
            margin: 20px auto;
            width: 200px;
            height: 200px;
        }
    </style>
</head>
<body>
@if ($return)
    @if(isset($return['data']['code_img_url']) && $return['data']['code_img_url']!='')
        <p style="text-align: center;margin: 200px auto 0px;">扫二维码支付</p>
        <div class="qcode">
            <img src="{{$return['data']['code_img_url']}}" alt="" width="200" height="200" alt="二维码">
        </div>
    @elseif(isset($return['data']['code_url']) && $return['data']['code_url']!='')
        <p style="text-align: center;margin: 200px auto 0px;">扫二维码支付</p>
        <div class="qcode" id="code">
        </div>
        <script>
            $("#code").qrcode({
                render: "table", //table方式
                width: 300, //宽度
                height: 300, //高度
                text: "{{$return['data']['code_url']}}" //任意内容
            });
        </script>
    @elseif(isset($return['data']['bank_card']) && $return['data']['bank_card']!='')
        <p style="text-align: center;margin: 200px auto 0px;">银卡转账信息</p>
        <p style="text-align: center;font-size: 18px;">卡&nbsp;&nbsp;&nbsp;号&nbsp;:&nbsp;<input type="text" readonly
                                                                                               value="{{$return['data']['bank_card']??''}}">
        </p>
        <p style="text-align: center;font-size: 18px;">开户行&nbsp;:&nbsp;<input type="text" readonly
                                                                              value="{{$return['data']['bank_name']??''}}">
        </p>
        <p style="text-align: center;font-size: 18px;">转账名&nbsp;:&nbsp;<input type="text" readonly
                                                                              value="{{$return['data']['real_name']??''}}">
        </p>
    @endif
@else
    <p style="text-align: center;margin: 100px auto 0px;color:red;font-size: 18px;">订单不存在</p>
@endif
</body>
</html>
