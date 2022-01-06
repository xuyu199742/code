----------------------------------------------------------------------------------------------------

USE WHQJAccountsDB
GO

IF EXISTS (SELECT * FROM DBO.SYSOBJECTS WHERE ID = OBJECT_ID(N'[dbo].[GSP_recharge_rates_statistics]') and OBJECTPROPERTY(ID, N'IsProcedure') = 1)
DROP PROCEDURE [dbo].[GSP_recharge_rates_statistics]
GO

SET QUOTED_IDENTIFIER ON 
GO

SET ANSI_NULLS ON 
GO

----------------------------------------------------------------------------------------------------

-- I D 登录
CREATE PROC GSP_recharge_rates_statistics
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
    INSERT INTO admin_platform.dbo.statistics_recharge_rates
    (
    statistics_time,type,total,created_at
    )
   SELECT c.success_time,c.type,COUNT(*) as total,@Today as created_at FROM (
		SELECT DISTINCT b.user_id,CONVERT(varchar,b.success_time,23) as success_time,DATEDIFF(DAY,b.success_time,a.LastLogonDate) as type
		FROM WHQJAccountsDB.dbo.AccountsInfo as a 
		LEFT JOIN admin_platform.dbo.payment_orders as b ON a.UserID=b.user_id
		WHERE a.IsAndroid=0 AND b.payment_status='SUCCESS' AND a.LastLogonDate>=@Yesterday AND a.LastLogonDate < @Today		
		GROUP BY b.user_id,success_time,DATEDIFF(DAY,b.success_time,a.LastLogonDate)
		HAVING DATEDIFF(DAY,b.success_time,a.LastLogonDate) IN (1,2,6,14,29)
	) as c
GROUP BY c.type,c.success_time
END

RETURN 0

GO

----------------------------------------------------------------------------------------------------