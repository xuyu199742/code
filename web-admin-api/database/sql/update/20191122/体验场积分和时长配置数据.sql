use WHQJAccountsDB

-- 系统设置
IF  NOT EXISTS (SELECT * FROM dbo.SystemStatusInfo WHERE StatusName='ExperienceScore')
     INSERT INTO SystemStatusInfo(StatusName,StatusValue,StatusString,StatusTip,StatusDescription,SortID) VALUES(N'ExperienceScore',0,N'体验场入场积分',N'体验场入场积分',N'键值：0-开启，1-关闭',1)

GO
IF  NOT EXISTS (SELECT * FROM dbo.SystemStatusInfo WHERE StatusName='ExperienceTime')
     INSERT INTO SystemStatusInfo(StatusName,StatusValue,StatusString,StatusTip,StatusDescription,SortID) VALUES(N'ExperienceTime',0,N'体验场体验时长',N'体验场体验时长',N'键值：0-开启，1-关闭',1)
GO