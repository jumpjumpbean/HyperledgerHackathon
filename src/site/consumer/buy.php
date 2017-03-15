<?php
/**
 * 购买流程
 **/


defined('InNormandy') or exit('Access Invalid!');
class buyControl extends BaseBuyControl {

    public function __construct() {
        parent::__construct();
        Language::read('home_cart_index');
        if (!$_SESSION['member_id']){
            redirect('index.php?act=login&ref_url='.urlencode(request_uri()));
        }
        //验证该会员是否禁止购买
        if(!$_SESSION['is_buy']){
            showMessage(Language::get('cart_buy_noallow'),'','html','error');
        }
        Tpl::output('hidden_rtoolbar_cart', 1);
    }

    /**
     * 实物商品 购物车、直接购买第一步:选择收获地址和配送方式
     */
    public function buy_step1Op() {
        if(isset($_SESSION['first_to_page']) && ($_SESSION['first_to_page'] == 'NO')){
            $_SESSION['first_to_page'] = 'YES';
            showMessage(Language::get('resubmit_or_refresh_order_form'), 'index.php', 'html','error',1,5000);
        } else {
            //税费
            $all_taxes = ncPriceFormat($_POST['all_taxes']);
            Tpl::output('all_taxes',$all_taxes);

            //虚拟商品购买分流
            $this->_buy_branch($_POST);

            //得到购买数据
            $logic_buy = Logic('buy');
            $result = $logic_buy->buyStep1($_POST['cart_id'], $_POST['ifcart'], $_SESSION['member_id'], $_SESSION['store_id']);
            if(!$result['state']) {
                showMessage($result['msg'], '', 'html', 'error');
            } else {
                $result = $result['data'];
            }
            //获取贸易id与贸易类型数组
            $model = Model();
            $trade_type_list = $model->table('trade_type')->select();
            foreach ($trade_type_list as $trade_type_info) {
                $trade_type_arr[$trade_type_info['trade_type_id']] = $trade_type_info['trade_type_name'];
            }
            Tpl::output('trade_type_arr',$trade_type_arr);

            //获取所有商品（一般贸易除外）总税额
//        $buy_tax_model = Model('buy');
//        $all_goods_tax_total = $buy_tax_model->getStoreCartTaxTotal($result['store_cart_list']);
            // Tpl::output('all_goods_tax_total',$all_goods_tax_total);

            //
            $store_cart_list_trade = $this->_getStoreCartList_tradetype($result['store_cart_list']);
            Tpl::output('store_cart_list_trade',$store_cart_list_trade);

            //获取商品贸易类型，向订单结算页面抛出
            $trade_type_id =$this->_getCartList_tradetype($result['store_cart_list']);
            Tpl::output('trade_type_id',$trade_type_id);
            //tom modify 获取直邮商品总税额
            $directmail_tax_total = $this->_getStoreCartDirectMailTaxTotal($store_cart_list_trade,$result['freight_list_trade']);
            Tpl::output('directmail_tax_total',$directmail_tax_total);

            //商品金额计算(分别对每个商品/优惠套装小计、每个店铺小计)
            Tpl::output('store_cart_list', $result['store_cart_list']);
            Tpl::output('store_goods_total', $result['store_goods_total']);

            //取得店铺优惠 - 满即送(赠品列表，店铺满送规则列表)
            /*phyllis mod 判断如果是vip会员，不参与满送满减*/
            $model_member = Model('member');
            $member =  $model_member->getMemberInfoByID($_SESSION['member_id']);
            if($member['is_vip']){
                Tpl::output('store_premiums_list', null);
                Tpl::output('store_mansong_rule_list', null);
            }else{
                Tpl::output('store_premiums_list', $result['store_premiums_list']);
                Tpl::output('store_mansong_rule_list', $result['store_mansong_rule_list']);
            }

            //返回店铺可用的代金券
            Tpl::output('store_voucher_list', $result['store_voucher_list']);

            //返回需要计算运费的店铺ID数组 和 不需要计算运费(满免运费活动的)店铺ID及描述
            Tpl::output('need_calc_sid_list', $result['need_calc_sid_list']);
            Tpl::output('cancel_calc_sid_list', $result['cancel_calc_sid_list']);

            //将商品ID、数量、售卖区域、运费序列化，加密，输出到模板，选择地区AJAX计算运费时作为参数使用
            Tpl::output('freight_hash', $result['freight_list']);

            //输出用户默认收货地址
            Tpl::output('address_info', $result['address_info']);

            //输出有货到付款时，在线支付和货到付款及每种支付下商品数量和详细列表
            Tpl::output('pay_goods_list', $result['pay_goods_list']);
            Tpl::output('ifshow_offpay', $result['ifshow_offpay']);
            Tpl::output('deny_edit_payment', $result['deny_edit_payment']);

            //不提供增值税发票时抛出true(模板使用)
            Tpl::output('vat_deny', $result['vat_deny']);

            //增值税发票哈希值(php验证使用)
            Tpl::output('vat_hash', $result['vat_hash']);

            //输出默认使用的发票信息
            Tpl::output('inv_info', $result['inv_info']);

            /*tom modify
             //phyllis add
             //根据页面传过来的贸易类型id，获取贸易类型名称
              $trade_name = Model('trade_type')->getTradeNameById($_POST['trade_type']);
             //输出贸易类型名称
             Tpl::output('trade_type', $trade_name);
             //
             */
            //显示预存款、支付密码、充值卡
            Tpl::output('available_pd_amount', $result['available_predeposit']);
            Tpl::output('member_paypwd', $result['member_paypwd']);
            Tpl::output('available_rcb_amount', $result['available_rc_balance']);

            //删除购物车无效商品
            $logic_buy->delCart($_POST['ifcart'], $_SESSION['member_id'], $_POST['invalid_cart']);

            //标识购买流程执行步骤
            Tpl::output('buy_step','step2');

            Tpl::output('ifcart', $_POST['ifcart']);

            //店铺信息
            $store_list = Model('store')->getStoreMemberIDList(array_keys($result['store_cart_list']));
            Tpl::output('store_list',$store_list);
            $curl = curl_init(); // 启动一个CURL会话
            curl_setopt($curl, CURLOPT_URL, 'https://b22009c6959f42a08e56f7b743ba34c2-vp0.us.blockchain.ibm.com:5004/chaincode'); // 要访问的地址
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 对认证证书来源的检查
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false); // 从证书中检查SSL加密算法是否存在
            curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
            curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
            curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
            //curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
            curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
            curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
            curl_setopt($curl,CURLOPT_POSTFIELDS, '{
                  "jsonrpc": "2.0",
                  "method": "query",
                  "params": {
                    "type": 1,
                    "chaincodeID": {
                      "name": "26febf054ad7cd35c6f23f506dabd78f881ff692a49de7583946adb38c09410b135e9cf666e0a3780b93a75515321c52b8adba149dfe9ab7a88242450f869f2a"
                    },
                    "ctorMsg": {
                      "function": "query",
                      "args": [
                        "phyllis"
                      ]
                    },
                    "secureContext": "admin"
                  },
                  "id": 0
                }');
            $tmpInfo = curl_exec($curl); // 执行操作
            curl_close($curl); // 关闭CURL会话

            $tmpResult = json_decode ($tmpInfo,true);
            $finalResult = json_decode ($tmpResult['result']['message'],true);
//            var_dump($finalResult);
//            $member_model = Model('member');
//            $condition_member = array('');
//            $member_model->editMember();

            Tpl::output('gradevin', $finalResult['gradevin']);
            Tpl::output('normandy', $finalResult['normandy']);
            Tpl::showpage('buy_step1');
        }

    }
    /**
     * 生成订单
     *
     */
    public function buy_step2Op() {
        $token_age = time() - $_SESSION['token_time'];
        if(isset($_SESSION['token']) && ($_POST['token'] == $_SESSION['token']) && ($token_age <= 300)){
            unset($_SESSION['token']);
            $logic_buy = logic('buy');
            $result = $logic_buy->buyStep2($_POST, $_SESSION['member_id'], $_SESSION['member_name'], $_SESSION['member_email']);
            if(!$result['state']) {
                showMessage($result['msg'], 'index.php?act=cart', 'html', 'error');
            }

            $points = $_POST['points_num']*100;
            
            //区块链 生成订单时,调用花积分接口
            $curl = curl_init(); 
            curl_setopt($curl, CURLOPT_URL, 'https://b22009c6959f42a08e56f7b743ba34c2-vp0.us.blockchain.ibm.com:5004/chaincode'); // 要访问的地址
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); 
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false); 
            curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); 
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); 
            curl_setopt($curl, CURLOPT_AUTOREFERER, 1); 
            curl_setopt($curl, CURLOPT_POST, 1); 
            curl_setopt($curl, CURLOPT_TIMEOUT, 30); 
            curl_setopt($curl, CURLOPT_HEADER, 0); 
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 
            curl_setopt($curl,CURLOPT_POSTFIELDS, '{
              "jsonrpc": "2.0",
              "method": "invoke",
              "params": {
                "type": 1,
                "chaincodeID": {
                  "name": "26febf054ad7cd35c6f23f506dabd78f881ff692a49de7583946adb38c09410b135e9cf666e0a3780b93a75515321c52b8adba149dfe9ab7a88242450f869f2a"
                },
                "ctorMsg": {
                  "function": "spend",
                  "args": [
                    "phyllis", "normandy", "gradevin", "'.$points.'"
                  ]
                },
                "secureContext": "admin"
              },
              "id": 0
            }');
            $tmpInfo = curl_exec($curl); // 执行操作
            curl_close($curl); // 关闭CURL会话
            $tmpResult = json_decode ($tmpInfo,true);
            if($tmpResult['result']['status'] == 'OK'){
                //转向到商城支付页面
                redirect('index.php?act=buy&op=pay&pay_sn='.$result['data']['pay_sn']);
            }else{
                showMessage("积分更新失败,请重新下单", 'index.php', 'html','error',1,5000);
            }

        }else{
            showMessage(Language::get('resubmit_or_refresh_order_form'), 'index.php', 'html','error',1,5000);
        }

    }

    /**
     * 下单时支付页面
     */
    public function payOp() {
        $_SESSION['first_to_page'] = 'NO';
        $pay_sn = $_GET['pay_sn'];
        $order_model = Model('order');
        $data = $order_model->getForeignRedirectUrl($pay_sn);
        $proxy_type = $order_model->getOrderTypeByPaySN($pay_sn);
        if ($proxy_type > 0) {
            $url = 'index.php?act=prepare_order';
        } else {
            $url = 'index.php?act=member_order';
        }
        if ($data['redirect_status']) {
            Header("Location:". $data['redirect_url']);
            exit;
        } else {
            if (!preg_match('/^\d{18}$/', $pay_sn)) {
                showMessage(Language::get('cart_order_pay_not_exists'), $url, 'html', 'error');
            }

            //查询支付单信息
            $model_order = Model('order');
            $pay_info = $model_order->getOrderPayInfo(array('pay_sn' => $pay_sn, 'buyer_id' => $_SESSION['member_id']), true);
            if (empty($pay_info)) {
                showMessage(Language::get('cart_order_pay_not_exists'), $url, 'html', 'error');
            }
            Tpl::output('pay_info', $pay_info);

            //取子订单列表
            $condition = array();
            $condition['pay_sn'] = $pay_sn;
            $condition['order_state'] = array('in', array(ORDER_STATE_NEW, ORDER_STATE_PAY));
            $order_list = $model_order->getOrderList($condition, '', 'order_id,order_state,payment_code,order_amount,rcb_amount,pd_amount,order_sn', '', '', array(), true);
            if (empty($order_list)) {
                showMessage('未找到需要支付的订单', $url, 'html', 'error');
            }

            //重新计算在线支付金额
            $pay_amount_online = 0;
            $pay_amount_offline = 0;
            //订单总支付金额(不包含货到付款)
            $pay_amount = 0;

            foreach ($order_list as $key => $order_info) {

                $payed_amount = floatval($order_info['rcb_amount']) + floatval($order_info['pd_amount']);
                //计算相关支付金额
                if ($order_info['payment_code'] != 'offline') {
                    if ($order_info['order_state'] == ORDER_STATE_NEW) {
                        $pay_amount_online += ncPriceFormat(floatval($order_info['order_amount']) - $payed_amount);
                    }
                    $pay_amount += floatval($order_info['order_amount']);
                } else {
                    $pay_amount_offline += floatval($order_info['order_amount']);
                }

                //显示支付方式与支付结果
                if ($order_info['payment_code'] == 'offline') {
                    $order_list[$key]['payment_state'] = '货到付款';
                } else {
                    $order_list[$key]['payment_state'] = '在线支付';
                    if ($payed_amount > 0) {
                        $payed_tips = '';
                        if (floatval($order_info['rcb_amount']) > 0) {
                            $payed_tips = '充值卡已支付：￥' . $order_info['rcb_amount'];
                        }
                        if (floatval($order_info['pd_amount']) > 0) {
                            $payed_tips .= ' 预存款已支付：￥' . $order_info['pd_amount'];
                        }
                        $order_list[$key]['order_amount'] .= " ( {$payed_tips} )";
                    }
                }
            }
            Tpl::output('order_list', $order_list);

            //如果线上线下支付金额都为0，转到支付成功页
            if (empty($pay_amount_online) && empty($pay_amount_offline)) {
                if ($proxy_type > 0) {
                    redirect('index.php?act=buy&op=pr_pay_ok&pay_sn=' . $pay_sn . '&pay_amount=' . ncPriceFormat($pay_amount));
                } else {
                    redirect('index.php?act=buy&op=pay_ok&pay_sn=' . $pay_sn . '&pay_amount=' . ncPriceFormat($pay_amount));
                }
            }

            //输出订单描述
            if (empty($pay_amount_online)) {
                $order_remind = '下单成功，我们会尽快为您发货，请保持电话畅通！';
            } elseif (empty($pay_amount_offline)) {
                $order_remind = '请您及时付款，以便订单尽快处理！';
            } else {
                $order_remind = '部分商品需要在线支付，请尽快付款！';
            }
            Tpl::output('order_remind', $order_remind);
            Tpl::output('pay_amount_online', ncPriceFormat($pay_amount_online));
            Tpl::output('pd_amount', ncPriceFormat($pd_amount));

            //显示支付接口列表
            if ($pay_amount_online > 0) {
                $model_payment = Model('payment');
                $condition = array();
                $payment_list = $model_payment->getPaymentOpenList($condition);
                if (!empty($payment_list)) {
//                    unset($payment_list['predeposit']);
                    unset($payment_list['offline']);
                }
                if (empty($payment_list)) {
                    showMessage('暂未找到合适的支付方式', $url, 'html', 'error');
                }
                Tpl::output('payment_list', $payment_list);
            }

            //标识 购买流程执行第几步
            Tpl::output('buy_step', 'step3');
            Tpl::showpage('buy_step2');
        }
    }

    /**
     * 预存款充值下单时支付页面
     */
    public function pd_payOp()
    {
        $pay_sn = $_GET['pay_sn'];
        if (!preg_match('/^\d{18}$/', $pay_sn)) {
            showMessage(Language::get('para_error'), 'index.php?act=predeposit', 'html', 'error');
        }

        //查询支付单信息
        $model_order = Model('predeposit');
        $pd_info = $model_order->getPdRechargeInfo(array('pdr_sn' => $pay_sn, 'pdr_member_id' => $_SESSION['member_id']));
        if (empty($pd_info)) {
            showMessage(Language::get('para_error'), '', 'html', 'error');
        }
        if (intval($pd_info['pdr_payment_state'])) {
            showMessage('您的订单已经支付，请勿重复支付', 'index.php?act=predeposit', 'html', 'error');
        }
        Tpl::output('pdr_info', $pd_info);

        //显示支付接口列表
        $model_payment = Model('payment');
        $condition = array();
        $condition['payment_code'] = array('not in', array('offline', 'predeposit'));
        $condition['payment_state'] = 1;
        $payment_list = $model_payment->getPaymentList($condition);
        if (empty($payment_list)) {
            showMessage('暂未找到合适的支付方式', 'index.php?act=predeposit&op=index', 'html', 'error');
        }
        Tpl::output('payment_list', $payment_list);

        //标识 购买流程执行第几步
        Tpl::output('buy_step', 'step3');
        Tpl::showpage('predeposit_pay');
    }

    /**
     * 支付成功页面
     */
    public function pay_okOp() {
        

        $pay_sn	= $_GET['pay_sn'];
        if (!preg_match('/^\d{18}$/',$pay_sn)){
            showMessage(Language::get('cart_order_pay_not_exists'),'index.php?act=member_order','html','error');
        }

        //查询支付单信息
        $model_order= Model('order');
        $pay_info = $model_order->getOrderPayInfo(array('pay_sn'=>$pay_sn,'buyer_id'=>$_SESSION['member_id']));
        if(empty($pay_info)){
            showMessage(Language::get('cart_order_pay_not_exists'),'index.php?act=member_order','html','error');
        }
        Tpl::output('pay_info',$pay_info);

        Tpl::output('buy_step','step4');
        Tpl::showpage('buy_step3');
    }
    /**
     * 代发订单支付成功页面
     */
    public function pr_pay_okOp() {
        $pay_sn	= $_GET['pay_sn'];
        if (!preg_match('/^\d{18}$/',$pay_sn)){
            showMessage(Language::get('cart_order_pay_not_exists'),'index.php?act=prepare_order','html','error');
        }

        //查询支付单信息
        $model_order= Model('order');
        $pay_info = $model_order->getOrderPayInfo(array('pay_sn'=>$pay_sn,'buyer_id'=>$_SESSION['member_id']));
        if(empty($pay_info)){
            showMessage(Language::get('cart_order_pay_not_exists'),'index.php?act=prepare_order','html','error');
        }
        Tpl::output('pay_info',$pay_info);

        Tpl::output('buy_step','step4');
        Tpl::showpage('buy_pr_step3');
    }
    /**
     * 加载买家收货地址
     *
     */
    public function load_addrOp() {
        $model_addr = Model('address');
        //如果传入ID，先删除再查询
        if (!empty($_GET['id']) && intval($_GET['id']) > 0) {
            $model_addr->delAddress(array('address_id'=>intval($_GET['id']),'member_id'=>$_SESSION['member_id']));
        }
        $condition = array();
        $condition['member_id'] = $_SESSION['member_id'];
        if (!C('delivery_isuse')) {
            $condition['dlyp_id'] = 0;
            $order = 'dlyp_id asc,address_id desc';
        }
        $list = $model_addr->getAddressList($condition,$order);
        $trade_type_id = $_GET['trade_type_id'];
        Tpl::output('trade_type_id',$trade_type_id);
        Tpl::output('address_list',$list);
        Tpl::showpage('buy_address.load','null_layout');
    }

    /**
     * 选择不同地区时，异步处理并返回每个店铺总运费以及本地区是否能使用货到付款
     * 如果店铺统一设置了满免运费规则，则售卖区域无效
     * 如果店铺未设置满免规则，且使用售卖区域，按售卖区域计算，如果其中有商品使用相同的售卖区域，则两种商品数量相加后再应用该售卖区域计算（即作为一种商品算运费）
     * 如果未找到售卖区域，按免运费处理
     * 如果没有使用售卖区域，商品运费按快递价格计算，运费不随购买数量增加
     */
    public function change_addrOp() {
        $logic_buy = Logic('buy');

        $data = $logic_buy->changeAddr($_POST['freight_hash'], $_POST['city_id'], $_POST['area_id'], $_SESSION['member_id']);
        if(!empty($data)) {
            exit(json_encode($data));
        } else {
            exit();
        }
    }

    /**
     * 添加新的收货地址
     *
     */
    public function add_addrOp(){
        $model_addr = Model('address');
        if (chksubmit()){
            $count = $model_addr->getAddressCount(array('member_id'=>$_SESSION['member_id']));
            if ($count >= 20) {
                exit(json_encode(array('state'=>false,'msg'=>'最多允许添加20个有效地址')));
            }
            //验证表单信息
            $obj_validate = new Validate();
            $obj_validate->validateparam = array(
                array("input"=>$_POST["true_name"],"require"=>"true","message"=>Language::get('cart_step1_input_receiver')),
                array("input"=>$_POST["area_id"],"require"=>"true","validator"=>"Number","message"=>Language::get('cart_step1_choose_area')),
                array("input"=>$_POST["address"],"require"=>"true","message"=>Language::get('cart_step1_input_address'))
            );
            $error = $obj_validate->validate();
            if ($error != ''){
                $error = strtoupper(CHARSET) == 'GBK' ? Language::getUTF8($error) : $error;
                exit(json_encode(array('state'=>false,'msg'=>$error)));
            }
            $data = array();
            $data['member_id'] = $_SESSION['member_id'];
            $data['true_name'] = $_POST['true_name'];
            $data['area_id'] = intval($_POST['area_id']);
            $data['city_id'] = intval($_POST['city_id']);
            $data['area_info'] = $_POST['area_info'];
            $data['address'] = $_POST['address'];
            $data['tel_phone'] = $_POST['tel_phone'];
            $data['mob_phone'] = $_POST['mob_phone'];
            $data['receiver_identify'] = $_POST['receiver_identify'];

            //转码
            $data = strtoupper(CHARSET) == 'GBK' ? Language::getGBK($data) : $data;
            $insert_id = $model_addr->addAddress($data);
            if ($insert_id){
                exit(json_encode(array('state'=>true,'addr_id'=>$insert_id)));
            }else {
                exit(json_encode(array('state'=>false,'msg'=>Language::get('cart_step1_addaddress_fail','UTF-8'))));
            }
        } else {
            $trade_type_id = $_GET['trade_type_id'];
            Tpl::output('trade_type_id',$trade_type_id);
            Tpl::showpage('buy_address.add','null_layout');
        }
    }

    /**
     * 加载买家发票列表，最多显示10条
     *
     */
    public function load_invOp() {
        $logic_buy = Logic('buy');

        $condition = array();
        if ($logic_buy->buyDecrypt($_GET['vat_hash'], $_SESSION['member_id']) == 'allow_vat') {
        } else {
            Tpl::output('vat_deny',true);
            $condition['inv_state'] = 1;
        }
        $condition['member_id'] = $_SESSION['member_id'];

        $model_inv = Model('invoice');
        //如果传入ID，先删除再查询
        if (intval($_GET['del_id']) > 0) {
            $model_inv->delInv(array('inv_id'=>intval($_GET['del_id']),'member_id'=>$_SESSION['member_id']));
        }
        $list = $model_inv->getInvList($condition,10);
        if (!empty($list)) {
            foreach ($list as $key => $value) {
                if ($value['inv_state'] == 1) {
                    $list[$key]['content'] = '普通发票'.' '.$value['inv_title'].' '.$value['inv_content'];
                } else {
                    $list[$key]['content'] = '增值税发票'.' '.$value['inv_company'].' '.$value['inv_code'].' '.$value['inv_reg_addr'];
                }
            }
        }
        Tpl::output('inv_list',$list);
        Tpl::showpage('buy_invoice.load','null_layout');
    }

    /**
     * 新增发票信息
     *
     */
    public function add_invOp(){
        $model_inv = Model('invoice');
        if (chksubmit()){
            //如果是增值税发票验证表单信息
            if ($_POST['invoice_type'] == 2) {
                if (empty($_POST['inv_company']) || empty($_POST['inv_code']) || empty($_POST['inv_reg_addr'])) {
                    exit(json_encode(array('state'=>false,'msg'=>Language::get('nc_common_save_fail','UTF-8'))));
                }
            }
            $data = array();
            if ($_POST['invoice_type'] == 1) {
                $data['inv_state'] = 1;
                $data['inv_title'] = $_POST['inv_title_select'] == 'person' ? '个人' : $_POST['inv_title'];
                $data['inv_content'] = $_POST['inv_content'];
            } else {
                $data['inv_state'] = 2;
                $data['inv_company'] = $_POST['inv_company'];
                $data['inv_code'] = $_POST['inv_code'];
                $data['inv_reg_addr'] = $_POST['inv_reg_addr'];
                $data['inv_reg_phone'] = $_POST['inv_reg_phone'];
                $data['inv_reg_bname'] = $_POST['inv_reg_bname'];
                $data['inv_reg_baccount'] = $_POST['inv_reg_baccount'];
                $data['inv_rec_name'] = $_POST['inv_rec_name'];
                $data['inv_rec_mobphone'] = $_POST['inv_rec_mobphone'];
                $data['inv_rec_province'] = $_POST['area_info'];
                $data['inv_goto_addr'] = $_POST['inv_goto_addr'];
            }
            $data['member_id'] = $_SESSION['member_id'];
            //转码
            $data = strtoupper(CHARSET) == 'GBK' ? Language::getGBK($data) : $data;
            $insert_id = $model_inv->addInv($data);
            if ($insert_id) {
                exit(json_encode(array('state'=>'success','id'=>$insert_id)));
            } else {
                exit(json_encode(array('state'=>'fail','msg'=>Language::get('nc_common_save_fail','UTF-8'))));
            }
        } else {
            Tpl::showpage('buy_address.add','null_layout');
        }
    }

    /**
     * AJAX验证支付密码
     */
    public function check_pd_pwdOp(){
        if (empty($_GET['password'])) exit('0');
        $buyer_info	= Model('member')->getMemberInfoByID($_SESSION['member_id'],'member_paypwd');
        echo ($buyer_info['member_paypwd'] != '' && $buyer_info['member_paypwd'] === md5($_GET['password'])) ? '1' : '0';
    }

    /**
     * F码验证
     */
    public function check_fcodeOp() {
        $result = logic('buy')->checkFcode($_GET['goods_commonid'], $_GET['fcode']);
        echo $result['state'] ? '1' : '0';
        exit;
    }

    /**
     * 得到所购买的id和数量
     *
     */
    private function _parseItems($cart_id) {
        //存放所购商品ID和数量组成的键值对
        $buy_items = array();
        if (is_array($cart_id)) {
            foreach ($cart_id as $value) {
                if (preg_match_all('/^(\d{1,10})\|(\d{1,6})$/', $value, $match)) {
                    $buy_items[$match[1][0]] = $match[2][0];
                }
            }
        }
        return $buy_items;
    }
    /**
     * 将以店铺ID为下标的下单商品列表数组进一步划分贸易类型归类
     *
     * @param array $store_cart_list
     * @return array
     */
    private function _getStoreCartList_tradetype($store_cart_list) {
        if (empty($store_cart_list) || !is_array($store_cart_list)) return $store_cart_list;
        $new_array = array();
        $model_goods = Model('goods');
        foreach ($store_cart_list as $store_cart) {
            foreach ($store_cart as $cart) {
                $trade_type_id = $model_goods->getTradeTypeIdByGoodsId($cart['goods_id']);
                $new_array[$cart['store_id']][$trade_type_id][] = $cart;
            }

        }
        return $new_array;
    }

    /**
     * 获得将要生成订单的贸易类型
     *
     * @param array $store_cart_list
     * @return $trade_type_id
     */
    private function _getCartList_tradetype($store_cart_list) {
        if (empty($store_cart_list) || !is_array($store_cart_list)) return $store_cart_list;
        $trade_type_id = 0;
        $model_goods = Model('goods');
        foreach ($store_cart_list as $store_cart) {
            foreach ($store_cart as $cart) {
                $trade_type_id = $model_goods->getTradeTypeIdByGoodsId($cart['goods_id']);
                break;
            }
            break;
        }
        return $trade_type_id;
    }
    /**
     *获得直邮商品总税费
     *
     */
    private function _getStoreCartDirectMailTaxTotal($store_cart_list_trade,$freight_list) {
        $DMtaxtotal = 0;
        foreach($store_cart_list_trade as $store_id => $cart_list_trade) {
            foreach($cart_list_trade as $trade_id => $cart_list) {
                if (intval($trade_id) == 2) {
                    if ( !is_array($freight_list[$store_id]) && intval($freight_list[$store_id]) == 0) {
                        $freight_trade = ncPriceFormat(0);
                    }else{
                        $freight_trade = $freight_list[$store_id][$trade_id];
                    }
                    $DMtaxtotal += calculateTax($cart_list,$freight_trade);
//                    $DMtaxtotal += calculateTax($cart_list,10);
                }
            }
        }
        return ncPriceFormat($DMtaxtotal);
    }

    /**
     * 购买分流
     */
    private function _buy_branch($post) {
        if (!$post['ifcart']) {
            //取得购买商品信息
            $buy_items = $this->_parseItems($post['cart_id']);
            $goods_id = key($buy_items);
            $quantity = current($buy_items);

            $goods_info = Model('goods')->getGoodsOnlineInfoAndPromotionById($goods_id);
            if ($goods_info['is_virtual']) {
                redirect('index.php?act=buy_virtual&op=buy_step1&goods_id='.$goods_id.'&quantity='.$quantity);
            }
        }
    }

}
