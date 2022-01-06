<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>金支付</title>
    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://cdn.bootcss.com/bootstrap/3.3.7/css/bootstrap.min.css"
          integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <!--[if lt IE 9]>
    <script src="https://cdn.bootcss.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://cdn.bootcss.com/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body>
<div class="container">
    <div class="row" style="margin:15px;0;">
        <div class="col-md-12">
            <form class="form-inline" method="post" id="md_from" action="<?php echo $action; ?>">
                <?php
                foreach ($order as $k => $val) {
                    echo '<input type="hidden" name="' . $k . '" value="' . $val . '">';
                }
                ?>
                <input type="hidden" name="pay_md5sign" value="<?php echo strtoupper(md5('pay_memberid^'.$order['pay_memberid'].'&pay_orderid^'.$order['pay_orderid'].'&pay_amount^'.$order['pay_amount'].'&pay_applydate^1&pay_channelCode^'.$order['pay_channelCode'].(request('bankcode')?'&pay_bankcode^'.request('bankcode'):'').'&pay_notifyurl^'.$order['pay_notifyurl'].'&key='.$key)); ?>">
                <?php if($order['pay_channelCode'] == 'YL_EXPRESS'){ ?>
                    <div class="form-group" id="bank">
                        <label for="name">银行列表</label>
                        <select class="form-control" name="pay_bankcode" id="pay_bankcode">
                            <option value=""{{!request('bankcode') ? 'selected' : ''}}>请选择银行...</option>
                            <option value="icbc" {{request('bankcode') == 'icbc' ? 'selected' : ''}}>中国工商银行</option>
                            <option value="abc" {{request('bankcode') == 'abc' ? 'selected' : ''}}>中国农业银行</option>
                            <option value="cmb" {{request('bankcode') == 'cmb' ? 'selected' : ''}}>招商银行</option>
                            <option value="boc" {{request('bankcode') == 'boc' ? 'selected' : ''}}>中国银行</option>
                            <option value="ccb" {{request('bankcode') == 'ccb' ? 'selected' : ''}}>中国建设银行</option>
                            <option value="cmbc" {{request('bankcode') == 'cmbc' ? 'selected' : ''}}>中国民生银行</option>
                            <option value="ecitic" {{request('bankcode') == 'ecitic' ? 'selected' : ''}}>中信银行</option>
                            <option value="comm" {{request('bankcode') == 'comm' ? 'selected' : ''}}>交通银行</option>
                            <option value="cib" {{request('bankcode') == 'cib' ? 'selected' : ''}}>兴业银行</option>
                            <option value="ceb" {{request('bankcode') == 'ceb' ? 'selected' : ''}}>光大银行</option>
                            <option value="post" {{request('bankcode') == 'post' ? 'selected' : ''}}>中国邮政</option>
                            <option value="bccb" {{request('bankcode') == 'bccb' ? 'selected' : ''}}>北京银行</option>
                            <option value="pingan" {{request('bankcode') == 'pingan' ? 'selected' : ''}}>平安银行</option>
                            <option value="spdb" {{request('bankcode') == 'spdb' ? 'selected' : ''}}>上海浦东发展银行</option>
                            <option value="gdb" {{request('bankcode') == 'gdb' ? 'selected' : ''}}>广东发展银行</option>
                            <option value="cbhb" {{request('bankcode') == 'cbhb' ? 'selected' : ''}}>渤海银行</option>
                            <option value="bea" {{request('bankcode') == 'bea' ? 'selected' : ''}}>东亚银行</option>
                            <option value="nb" {{request('bankcode') == 'nb' ? 'selected' : ''}}>宁波银行</option>
                            <option value="bjrcb" {{request('bankcode') == 'bjrcb' ? 'selected' : ''}}>北京农村商业银行</option>
                            <option value="njcb" {{request('bankcode') == 'njcb' ? 'selected' : ''}}>南京银行</option>
                            <option value="cz" {{request('bankcode') == 'cz' ? 'selected' : ''}}>浙商银行</option>
                            <option value="bos" {{request('bankcode') == 'bos' ? 'selected' : ''}}>上海银行</option>
                            <option value="shrcb" {{request('bankcode') == 'shrcb' ? 'selected' : ''}}>上海农村商业银行</option>
                            <option value="hxb" {{request('bankcode') == 'hxb' ? 'selected' : ''}}>华夏银行</option>
                            <option value="hccb" {{request('bankcode') == 'hccb' ? 'selected' : ''}}>杭州银行</option>
                        </select>
                    </div>
                    <br/>
                <?php } ?>
                <div class="form-group" style="margin-top: 15px">
                    <button type="submit" class="btn btn-success btn-lg">支付(金额：<?php echo $order['pay_amount']; ?>元)</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.bootcss.com/jquery/1.12.4/jquery.min.js"></script>
<script src="https://cdn.bootcss.com/bootstrap/3.3.7/js/bootstrap.min.js"
        integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa"
        crossorigin="anonymous"></script>
<script>
    var url = "{{ url('api/payment/order',['order_no'=>$order['pay_orderid']]) }}";
    $('#pay_bankcode').change(function(){
        var bankcode = $(this).val();
        window.location.href = url + '?bankcode='+ bankcode;
    })
    $('#md_from').submit(function(){
        if(!$('#bank').length){
            return true;
        }
        if($('#pay_bankcode').val()){
            return true;
        }
        alert('请选择银行');
        return false;
    })
</script>

</body>
</html>