<?php
/*游戏模块*/
namespace Models\Platform;

/**
 * GameID           int             游戏标识
 * GameName         nvarchar(31)    游戏名称
 * SuportType       int             支持类型
 * DataBaseAddr     nvarchar(15)    连接地址    选择的
 * DataBaseName     nvarchar(31)    数据库名
 * ServerVersion    int             服务器版本
 * ClientVersion    int             客户端版本
 * ServerDLLName    nvarchar(31)    服务端名称
 * ClientExeName    nvarchar(31)    客户端名称
 */

class GameGameItem extends Base
{
    //数据表
    protected $table = 'GameGameItem';
}
