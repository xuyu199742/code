----------------------------------------------------------------------------------------------------

USE WHQJAccountsDB
GO

IF EXISTS (SELECT * FROM DBO.SYSOBJECTS WHERE ID = OBJECT_ID(N'[dbo].[GSP_recharge_channels_statistics]') and OBJECTPROPERTY(ID, N'IsProcedure') = 1)
DROP PROCEDURE [dbo].[GSP_recharge_channels_statistics]
GO

SET QUOTED_IDENTIFIER ON 
GO

SET ANSI_NULLS ON 
GO

----------------------------------------------------------------------------------------------------

-- I D 登录
CREATE PROC GSP_recharge_channels_statistics
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
    INSERT INTO admin_platform.dbo.statistics_recharge_channels
    (
    statistics_time,type,total,created_at,channel_id
    )
    SELECT d.success_time,d.type,COUNT(*) as total,@Today as created_at,d.channel_id FROM (
		select DISTINCT a.user_id,CONVERT(varchar,b.success_time,23) as success_time,
		a.channel_id,DATEDIFF(DAY,b.success_time,c.LastLogonDate) as type from AgentDB.dbo.channel_user_relation as a
		LEFT JOIN admin_platform.dbo.payment_orders as b ON a.user_id=b.user_id
		LEFT JOIN WHQJAccountsDB.dbo.AccountsInfo as c ON a.user_id=c.UserID
		where b.payment_status='SUCCESS'AND c.LastLogonDate>=@Yesterday AND c.LastLogonDate<@Today
		GROUP BY a.user_id,a.channel_id,success_time,DATEDIFF(DAY,b.success_time,c.LastLogonDate)
		HAVING DATEDIFF(DAY,b.success_time,c.LastLogonDate) IN (1,2,6,14,29)
	) as d
	GROUP BY d.type,d.channel_id,d.success_time
END

RETURN 0

GO

----------------------------------------------------------------------------------------------------