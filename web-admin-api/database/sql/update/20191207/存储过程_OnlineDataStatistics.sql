----------------------------------------------------------------------------------------------------

USE admin_platform
GO

IF EXISTS (SELECT * FROM DBO.SYSOBJECTS WHERE ID = OBJECT_ID(N'[dbo].[OnlineDataStatistics]') and OBJECTPROPERTY(ID, N'IsProcedure') = 1)
DROP PROCEDURE [dbo].[OnlineDataStatistics]
GO

SET QUOTED_IDENTIFIER ON 
GO

SET ANSI_NULLS ON 
GO

----------------------------------------------------------------------------------------------------

-- I D µÇÂ¼
CREATE PROC OnlineDataStatistics
WITH ENCRYPTION AS

-- ÊôÐÔÉèÖÃ
SET NOCOUNT ON

DECLARE @Today DATE
DECLARE @TimeToSecond DATETIME

SET @Today = DATEADD(DAY, 0, GETDATE())
SET @TimeToSecond = CONVERT(VARCHAR(16), GETDATE(), 120) 

-- Ö´ÐÐÂß¼­
BEGIN
    INSERT INTO admin_platform.dbo.statistics_online_data
    (
    statistics_time,client_type,total,created_at
    )
    SELECT  @Today as statistics_time ,-1 as client_type,COUNT(*) AS total, @TimeToSecond as created_at from WHQJTreasureDB.dbo.GameChatUserInfo as a
	  left JOIN WHQJAccountsDB.dbo.AccountsInfo as b
	  on a.UserID=b.UserID
	  WHERE a.CollectDate >= @Today

    INSERT INTO admin_platform.dbo.statistics_online_data
    (
    statistics_time,client_type,total,created_at
    )
    SELECT @Today as statistics_time ,b.ClientType as client_type,COUNT(*) AS total,@TimeToSecond as created_at from WHQJTreasureDB.dbo.GameChatUserInfo as a
	 left JOIN WHQJAccountsDB.dbo.AccountsInfo as b
	 on a.UserID=b.UserID
	 WHERE a.CollectDate >= @Today
	 GROUP BY b.ClientType

END

RETURN 0

GO

----------------------------------------------------------------------------------------------------