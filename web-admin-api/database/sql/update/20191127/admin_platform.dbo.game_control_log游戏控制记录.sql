USE [admin_platform]
GO
IF  EXISTS (SELECT * FROM ::fn_listextendedproperty(N'MS_Description' , N'SCHEMA',N'dbo', N'TABLE',N'game_control_log', N'COLUMN',N'details'))
EXEC sys.sp_dropextendedproperty @name=N'MS_Description' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'game_control_log', @level2type=N'COLUMN',@level2name=N'details'

GO
IF  EXISTS (SELECT * FROM ::fn_listextendedproperty(N'MS_Description' , N'SCHEMA',N'dbo', N'TABLE',N'game_control_log', N'COLUMN',N'create_time'))
EXEC sys.sp_dropextendedproperty @name=N'MS_Description' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'game_control_log', @level2type=N'COLUMN',@level2name=N'create_time'

GO
IF  EXISTS (SELECT * FROM ::fn_listextendedproperty(N'MS_Description' , N'SCHEMA',N'dbo', N'TABLE',N'game_control_log', N'COLUMN',N'ip'))
EXEC sys.sp_dropextendedproperty @name=N'MS_Description' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'game_control_log', @level2type=N'COLUMN',@level2name=N'ip'

GO
IF  EXISTS (SELECT * FROM ::fn_listextendedproperty(N'MS_Description' , N'SCHEMA',N'dbo', N'TABLE',N'game_control_log', N'COLUMN',N'title'))
EXEC sys.sp_dropextendedproperty @name=N'MS_Description' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'game_control_log', @level2type=N'COLUMN',@level2name=N'title'

GO
IF  EXISTS (SELECT * FROM ::fn_listextendedproperty(N'MS_Description' , N'SCHEMA',N'dbo', N'TABLE',N'game_control_log', N'COLUMN',N'admin_id'))
EXEC sys.sp_dropextendedproperty @name=N'MS_Description' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'game_control_log', @level2type=N'COLUMN',@level2name=N'admin_id'

GO
IF  EXISTS (SELECT * FROM ::fn_listextendedproperty(N'MS_Description' , N'SCHEMA',N'dbo', N'TABLE',N'game_control_log', N'COLUMN',N'id'))
EXEC sys.sp_dropextendedproperty @name=N'MS_Description' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'game_control_log', @level2type=N'COLUMN',@level2name=N'id'

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__game_cont__detai__498EEC8D]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[game_control_log] DROP CONSTRAINT [DF__game_cont__detai__498EEC8D]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__game_control__ip__489AC854]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[game_control_log] DROP CONSTRAINT [DF__game_control__ip__489AC854]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__game_cont__title__47A6A41B]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[game_control_log] DROP CONSTRAINT [DF__game_cont__title__47A6A41B]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__game_cont__admin__46B27FE2]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[game_control_log] DROP CONSTRAINT [DF__game_cont__admin__46B27FE2]
END

GO
/****** Object:  Index [admin_id_index]    Script Date: 2019/11/27 17:33:40 ******/
IF  EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[game_control_log]') AND name = N'admin_id_index')
DROP INDEX [admin_id_index] ON [dbo].[game_control_log]
GO
/****** Object:  Table [dbo].[game_control_log]    Script Date: 2019/11/27 17:33:40 ******/
IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[game_control_log]') AND type in (N'U'))
DROP TABLE [dbo].[game_control_log]
GO
/****** Object:  Table [dbo].[game_control_log]    Script Date: 2019/11/27 17:33:40 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[game_control_log]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[game_control_log](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[admin_id] [int] NOT NULL,
	[title] [varchar](50) NOT NULL,
	[ip] [varchar](15) NOT NULL,
	[create_time] [datetime] NOT NULL,
	[details] [varchar](300) NOT NULL,
PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
END
GO
SET ANSI_PADDING OFF
GO
/****** Object:  Index [admin_id_index]    Script Date: 2019/11/27 17:33:40 ******/
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[game_control_log]') AND name = N'admin_id_index')
CREATE NONCLUSTERED INDEX [admin_id_index] ON [dbo].[game_control_log]
(
	[admin_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__game_cont__admin__46B27FE2]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[game_control_log] ADD  DEFAULT ((0)) FOR [admin_id]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__game_cont__title__47A6A41B]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[game_control_log] ADD  DEFAULT ('') FOR [title]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__game_control__ip__489AC854]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[game_control_log] ADD  DEFAULT ('') FOR [ip]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__game_cont__detai__498EEC8D]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[game_control_log] ADD  DEFAULT ('') FOR [details]
END

GO
IF NOT EXISTS (SELECT * FROM ::fn_listextendedproperty(N'MS_Description' , N'SCHEMA',N'dbo', N'TABLE',N'game_control_log', N'COLUMN',N'id'))
EXEC sys.sp_addextendedproperty @name=N'MS_Description', @value=N'主键' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'game_control_log', @level2type=N'COLUMN',@level2name=N'id'
GO
IF NOT EXISTS (SELECT * FROM ::fn_listextendedproperty(N'MS_Description' , N'SCHEMA',N'dbo', N'TABLE',N'game_control_log', N'COLUMN',N'admin_id'))
EXEC sys.sp_addextendedproperty @name=N'MS_Description', @value=N'操作人' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'game_control_log', @level2type=N'COLUMN',@level2name=N'admin_id'
GO
IF NOT EXISTS (SELECT * FROM ::fn_listextendedproperty(N'MS_Description' , N'SCHEMA',N'dbo', N'TABLE',N'game_control_log', N'COLUMN',N'title'))
EXEC sys.sp_addextendedproperty @name=N'MS_Description', @value=N'操作内容' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'game_control_log', @level2type=N'COLUMN',@level2name=N'title'
GO
IF NOT EXISTS (SELECT * FROM ::fn_listextendedproperty(N'MS_Description' , N'SCHEMA',N'dbo', N'TABLE',N'game_control_log', N'COLUMN',N'ip'))
EXEC sys.sp_addextendedproperty @name=N'MS_Description', @value=N'操作ip' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'game_control_log', @level2type=N'COLUMN',@level2name=N'ip'
GO
IF NOT EXISTS (SELECT * FROM ::fn_listextendedproperty(N'MS_Description' , N'SCHEMA',N'dbo', N'TABLE',N'game_control_log', N'COLUMN',N'create_time'))
EXEC sys.sp_addextendedproperty @name=N'MS_Description', @value=N'操作时间' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'game_control_log', @level2type=N'COLUMN',@level2name=N'create_time'
GO
IF NOT EXISTS (SELECT * FROM ::fn_listextendedproperty(N'MS_Description' , N'SCHEMA',N'dbo', N'TABLE',N'game_control_log', N'COLUMN',N'details'))
EXEC sys.sp_addextendedproperty @name=N'MS_Description', @value=N'操作详情' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'game_control_log', @level2type=N'COLUMN',@level2name=N'details'
GO
