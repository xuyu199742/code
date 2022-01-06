<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PHPSocketIO\SocketIO;
use Workerman\Lib\Timer;
use Workerman\Worker;
use App;

class MsgPushCommand extends Command
{
    public $sender_io;
    public $uidConnectionMap = array();
    public $last_online_count = 0;
    public $last_online_page_count = 0;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'socket:io {action} {--daemonize}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'socket:io';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //因为workerman需要带参数 所以得强制修改
        global $argv;
        $action = $this->argument('action');
        if (!in_array($action, ['start', 'stop'])) {
            $this->error('Error Arguments');
            exit;
        }
        $argv[0] = 'socket:io';
        $argv[1] = $action;
        $argv[2] = $this->option('daemonize') ? '-d' : '';
        switch ($action){
            case 'start':
                $this->start();
                break;
            case 'stop':
                break;
        }
    }

    public function start(){
        $this->sender_io = new SocketIO(2120);
        $this->start_socket($this->sender_io);
        $this->start_web_service($this->sender_io);
        Worker::runAll();
    }

    private function start_socket($sender_io)
    {
        // 客户端发起连接事件时，设置连接socket的各种事件回调
        $sender_io->on('connection', function ($socket) {
            // 当客户端发来登录事件时触发
            $socket->on('login', function ($uid) use ($socket) {
                // 已经登录过了
                if (isset($socket->uid)) {
                    return;
                }
                // 更新对应uid的在线数据
                $uid = (string)$uid;
                if (!isset($this->uidConnectionMap[$uid])) {
                    $this->uidConnectionMap[$uid] = 0;
                }
                // 这个uid有++$this->uidConnectionMap[$uid]个socket连接
                ++$this->uidConnectionMap[$uid];
                // 将这个连接加入到uid分组，方便针对uid推送数据
                $socket->join($uid);
                $socket->uid = $uid;
                // 更新这个socket对应页面的在线数据
                $socket->emit('update_online_count', "当前<b>{$this->last_online_count}</b>人在线，共打开<b>{$this->last_online_page_count}</b>个页面");
                $socket->emit('data', json_encode(getPushStorage(), true));
            });

            //消息已读
            $socket->on('has_read', function ($data = []) use ($socket) {
                if(empty($data)){
                    \Log::channel('push_fail')->info('消息接受失败:has_read参数丢失');
                }
                $type = json_decode($data,true)['type'] ?? '';
                if($type){
                    readPushStorage($type);
                    $socket->emit('data', json_encode(getPushStorage(), true));
                    \Log::channel('push_success')->info('消息接受成功:'.$type);
                }else{
                    \Log::channel('push_fail')->info('消息接受失败:'.$type);
                }
            });

            // 断开连接
            $socket->on('login_out', function ($uid = '') use ($socket) {
                if (!isset($socket->uid)) {
                    return;
                }
                // 将uid的在线socket数减一
                if (--$this->uidConnectionMap[$socket->uid] <= 0) {
                    unset($this->uidConnectionMap[$socket->uid]);
                }
                $socket->disconnect();
            });

            // 当客户端断开连接是触发（一般是关闭网页或者跳转刷新导致）
            $socket->on('disconnect', function ($uid) use ($socket) {
                if (!isset($socket->uid)) {
                    return;
                }
                // 将uid的在线socket数减一
                if (--$this->uidConnectionMap[$socket->uid] <= 0) {
                    unset($this->uidConnectionMap[$socket->uid]);
                }
            });
        });

    }

    private function start_web_service($sender_io)
    {
        // 当$sender_io启动后监听一个http端口，通过这个端口可以给任意uid或者所有uid推送数据
        $sender_io->on('workerStart', function () {
            // 监听一个http端口
            $inner_http_worker = new Worker('http://0.0.0.0:2121');
            // 当http客户端发来数据时触发
            $inner_http_worker->onMessage = function ($http_connection, $data) {
                $data = $data['post'] ? $data['post'] : $data['get'];
                // 推送数据的url格式 type=publish&to=uid&content=xxxx
                switch (@$data['type']) {
                    case 'publish':
                        $to = @$data['to'];
                        $post_data = json_encode(@$data['data']);
                        // 有指定uid则向uid所在socket组发送数据
                        if ($to) {
                            $this->sender_io->to($to)->emit('data', $post_data);
                            // 否则向所有uid推送数据
                        } else {
                            $this->sender_io->emit('data', $post_data);
                        }
                        // http接口返回，如果用户离线socket返回fail
                        if ($to && !isset($this->uidConnectionMap[$to])) {
                            return $http_connection->send('offline');
                        } else {
                            return $http_connection->send('ok');
                        }
                        break;
                }
                return $http_connection->send('fail');
            };
            // 执行监听
            $inner_http_worker->listen();
            // 一个定时器，定时向所有uid推送当前uid在线数及在线页面数
            Timer::add(1, function(){
                $online_count_now = count($this->uidConnectionMap);
                $online_page_count_now = array_sum($this->uidConnectionMap);
                // 只有在客户端在线数变化了才广播，减少不必要的客户端通讯
                if($this->last_online_count != $online_count_now || $this->last_online_page_count != $online_page_count_now)
                {
                    $this->sender_io->emit('update_online_count', "当前<b>{$online_count_now}</b>人在线，共打开<b>{$online_page_count_now}</b>个页面");
                    $this->last_online_count = $online_count_now;
                    $this->last_online_page_count = $online_page_count_now;
                }
            });
        });
    }
}