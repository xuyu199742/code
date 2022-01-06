USE [admin_platform]
GO
IF  EXISTS (SELECT * FROM ::fn_listextendedproperty(N'MS_Description' , N'SCHEMA',N'dbo', N'TABLE',N'agent_red_packet_relation', N'COLUMN',N'serial_number'))
    EXEC sys.sp_dropextendedproperty @name=N'MS_Description' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'agent_red_packet_relation', @level2type=N'COLUMN',@level2name=N'serial_number'

GO
IF  EXISTS (SELECT * FROM ::fn_listextendedproperty(N'MS_Description' , N'SCHEMA',N'dbo', N'TABLE',N'agent_red_packet_relation', N'COLUMN',N'updated_at'))
    EXEC sys.sp_dropextendedproperty @name=N'MS_Description' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'agent_red_packet_relation', @level2type=N'COLUMN',@level2name=N'updated_at'

GO
IF  EXISTS (SELECT * FROM ::fn_listextendedproperty(N'MS_Description' , N'SCHEMA',N'dbo', N'TABLE',N'agent_red_packet_relation', N'COLUMN',N'created_at'))
    EXEC sys.sp_dropextendedproperty @name=N'MS_Description' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'agent_red_packet_relation', @level2type=N'COLUMN',@level2name=N'created_at'

GO
IF  EXISTS (SELECT * FROM ::fn_listextendedproperty(N'MS_Description' , N'SCHEMA',N'dbo', N'TABLE',N'agent_red_packet_relation', N'COLUMN',N'backups'))
    EXEC sys.sp_dropextendedproperty @name=N'MS_Description' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'agent_red_packet_relation', @level2type=N'COLUMN',@level2name=N'backups'

GO
IF  EXISTS (SELECT * FROM ::fn_listextendedproperty(N'MS_Description' , N'SCHEMA',N'dbo', N'TABLE',N'agent_red_packet_relation', N'COLUMN',N'coin'))
    EXEC sys.sp_dropextendedproperty @name=N'MS_Description' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'agent_red_packet_relation', @level2type=N'COLUMN',@level2name=N'coin'

GO
IF  EXISTS (SELECT * FROM ::fn_listextendedproperty(N'MS_Description' , N'SCHEMA',N'dbo', N'TABLE',N'agent_red_packet_relation', N'COLUMN',N'user_id'))
    EXEC sys.sp_dropextendedproperty @name=N'MS_Description' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'agent_red_packet_relation', @level2type=N'COLUMN',@level2name=N'user_id'

GO
IF  EXISTS (SELECT * FROM ::fn_listextendedproperty(N'MS_Description' , N'SCHEMA',N'dbo', N'TABLE',N'agent_red_packet_relation', N'COLUMN',N'money'))
    EXEC sys.sp_dropextendedproperty @name=N'MS_Description' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'agent_red_packet_relation', @level2type=N'COLUMN',@level2name=N'money'

GO
IF  EXISTS (SELECT * FROM ::fn_listextendedproperty(N'MS_Description' , N'SCHEMA',N'dbo', N'TABLE',N'agent_red_packet_relation', N'COLUMN',N'sign'))
    EXEC sys.sp_dropextendedproperty @name=N'MS_Description' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'agent_red_packet_relation', @level2type=N'COLUMN',@level2name=N'sign'

GO
IF  EXISTS (SELECT * FROM ::fn_listextendedproperty(N'MS_Description' , N'SCHEMA',N'dbo', N'TABLE',N'agent_red_packet_relation', N'COLUMN',N'id'))
    EXEC sys.sp_dropextendedproperty @name=N'MS_Description' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'agent_red_packet_relation', @level2type=N'COLUMN',@level2name=N'id'

GO
/****** Object:  Index [money]    Script Date: 2019/11/22 17:06:10 ******/
IF  EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[agent_red_packet_relation]') AND name = N'money')
    DROP INDEX [money] ON [dbo].[agent_red_packet_relation]
GO
/****** Object:  Table [dbo].[agent_red_packet_relation]    Script Date: 2019/11/22 17:06:10 ******/
IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[agent_red_packet_relation]') AND type in (N'U'))
    DROP TABLE [dbo].[agent_red_packet_relation]
GO
/****** Object:  Table [dbo].[agent_red_packet_relation]    Script Date: 2019/11/22 17:06:10 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[agent_red_packet_relation]') AND type in (N'U'))
    BEGIN
        CREATE TABLE [dbo].[agent_red_packet_relation](
                                                          [id] [bigint] IDENTITY(1,1) NOT NULL,
                                                          [sign] [varchar](100) NOT NULL,
                                                          [money] [int] NOT NULL,
                                                          [user_id] [bigint] NOT NULL,
                                                          [coin] [bigint] NOT NULL,
                                                          [backups] [varchar](255) NULL,
                                                          [created_at] [datetime] NULL,
                                                          [updated_at] [datetime] NULL,
                                                          [serial_number] [varchar](100) NOT NULL,
                                                          PRIMARY KEY CLUSTERED
                                                              (
                                                               [id] ASC
                                                                  )WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY],
                                                          CONSTRAINT [user_sign_unique] UNIQUE NONCLUSTERED
                                                              (
                                                               [sign] ASC,
                                                               [user_id] ASC
                                                                  )WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
        ) ON [PRIMARY]
    END
GO
SET ANSI_PADDING OFF
GO
/****** Object:  Index [money]    Script Date: 2019/11/22 17:06:10 ******/
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[agent_red_packet_relation]') AND name = N'money')
CREATE NONCLUSTERED INDEX [money] ON [dbo].[agent_red_packet_relation]
    (
     [money] ASC
        )WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
IF NOT EXISTS (SELECT * FROM ::fn_listextendedproperty(N'MS_Description' , N'SCHEMA',N'dbo', N'TABLE',N'agent_red_packet_relation', N'COLUMN',N'id'))
    EXEC sys.sp_addextendedproperty @name=N'MS_Description', @value=N'自增id' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'agent_red_packet_relation', @level2type=N'COLUMN',@level2name=N'id'
GO
IF NOT EXISTS (SELECT * FROM ::fn_listextendedproperty(N'MS_Description' , N'SCHEMA',N'dbo', N'TABLE',N'agent_red_packet_relation', N'COLUMN',N'sign'))
    EXEC sys.sp_addextendedproperty @name=N'MS_Description', @value=N'红包标识' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'agent_red_packet_relation', @level2type=N'COLUMN',@level2name=N'sign'
GO
IF NOT EXISTS (SELECT * FROM ::fn_listextendedproperty(N'MS_Description' , N'SCHEMA',N'dbo', N'TABLE',N'agent_red_packet_relation', N'COLUMN',N'money'))
    EXEC sys.sp_addextendedproperty @name=N'MS_Description', @value=N'红包金额' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'agent_red_packet_relation', @level2type=N'COLUMN',@level2name=N'money'
GO
IF NOT EXISTS (SELECT * FROM ::fn_listextendedproperty(N'MS_Description' , N'SCHEMA',N'dbo', N'TABLE',N'agent_red_packet_relation', N'COLUMN',N'user_id'))
    EXEC sys.sp_addextendedproperty @name=N'MS_Description', @value=N'玩家id' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'agent_red_packet_relation', @level2type=N'COLUMN',@level2name=N'user_id'
GO
IF NOT EXISTS (SELECT * FROM ::fn_listextendedproperty(N'MS_Description' , N'SCHEMA',N'dbo', N'TABLE',N'agent_red_packet_relation', N'COLUMN',N'coin'))
    EXEC sys.sp_addextendedproperty @name=N'MS_Description', @value=N'金币值' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'agent_red_packet_relation', @level2type=N'COLUMN',@level2name=N'coin'
GO
IF NOT EXISTS (SELECT * FROM ::fn_listextendedproperty(N'MS_Description' , N'SCHEMA',N'dbo', N'TABLE',N'agent_red_packet_relation', N'COLUMN',N'backups'))
    EXEC sys.sp_addextendedproperty @name=N'MS_Description', @value=N'红包数据备份' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'agent_red_packet_relation', @level2type=N'COLUMN',@level2name=N'backups'
GO
IF NOT EXISTS (SELECT * FROM ::fn_listextendedproperty(N'MS_Description' , N'SCHEMA',N'dbo', N'TABLE',N'agent_red_packet_relation', N'COLUMN',N'created_at'))
    EXEC sys.sp_addextendedproperty @name=N'MS_Description', @value=N'创建时间' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'agent_red_packet_relation', @level2type=N'COLUMN',@level2name=N'created_at'
GO
IF NOT EXISTS (SELECT * FROM ::fn_listextendedproperty(N'MS_Description' , N'SCHEMA',N'dbo', N'TABLE',N'agent_red_packet_relation', N'COLUMN',N'updated_at'))
    EXEC sys.sp_addextendedproperty @name=N'MS_Description', @value=N'更新时间' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'agent_red_packet_relation', @level2type=N'COLUMN',@level2name=N'updated_at'
GO
IF NOT EXISTS (SELECT * FROM ::fn_listextendedproperty(N'MS_Description' , N'SCHEMA',N'dbo', N'TABLE',N'agent_red_packet_relation', N'COLUMN',N'serial_number'))
    EXEC sys.sp_addextendedproperty @name=N'MS_Description', @value=N'流水号' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'agent_red_packet_relation', @level2type=N'COLUMN',@level2name=N'serial_number'
GO
