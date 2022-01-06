----------------------------------------------------------------------------------------------------

USE WHQJAccountsDB
GO

IF EXISTS (SELECT * FROM DBO.SYSOBJECTS WHERE ID = OBJECT_ID(N'[dbo].[GSP_retention_channels_statistics]') and OBJECTPROPERTY(ID, N'IsProcedure') = 1)
DROP PROCEDURE [dbo].[GSP_retention_channels_statistics]
GO

SET QUOTED_IDENTIFIER ON 
GO

SET ANSI_NULLS ON 
GO

----------------------------------------------------------------------------------------------------

-- I D 登录
CREATE PROC GSP_retention_channels_statistics 
	@Days INT = 0
WITH ENCRYPTION AS

-- 属性设置
SET NOCOUNT ON

DECLARE @Yesterday date
DECLARE @Today date
SET @Today = DATEADD(DAY, -@Days, GETDATE())

SET @Yesterday=DATEADD(DAY, -1, @Today)

-- 执行逻辑
BEGIN
    INSERT INTO admin_platform.dbo.statistics_retention_channels
    (
    statistics_time,type,total,created_at,channel_id
    )
    SELECT CONVERT(varchar,b.RegisterDate,23) as statistics_time,DATEDIFF(DAY,b.RegisterDate , b.LastLogonDate) as type,count(*) as total,@Today as created_at,a.channel_id as channel_id
	FROM AgentDB.dbo.channel_user_relation as a
	LEFT JOIN  WHQJAccountsDB.dbo.AccountsInfo as b ON b.UserID=a.user_id 
	where b.IsAndroid=0 AND b.LastLogonDate>=@Yesterday AND  b.LastLogonDate<@Today
    GROUP BY a.channel_id,DATEDIFF(DAY,b.RegisterDate,b.LastLogonDate),CONVERT(varchar,b.RegisterDate,23)
    HAVING DATEDIFF(DAY,b.RegisterDate,b.LastLogonDate) IN (1,2,6,14,29)
END

RETURN 0

GO

----------------------------------------------------------------------------------------------------