USE [admin_platform]
GO
IF  EXISTS (SELECT * FROM ::fn_listextendedproperty(N'MS_Description' , N'SCHEMA',N'dbo', N'TABLE',N'statistics_recharge_channels', N'COLUMN',N'channel_id'))
EXEC sys.sp_dropextendedproperty @name=N'MS_Description' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'statistics_recharge_channels', @level2type=N'COLUMN',@level2name=N'channel_id'

GO
IF  EXISTS (SELECT * FROM ::fn_listextendedproperty(N'MS_Description' , N'SCHEMA',N'dbo', N'TABLE',N'statistics_recharge_channels', N'COLUMN',N'total'))
EXEC sys.sp_dropextendedproperty @name=N'MS_Description' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'statistics_recharge_channels', @level2type=N'COLUMN',@level2name=N'total'

GO
IF  EXISTS (SELECT * FROM ::fn_listextendedproperty(N'MS_Description' , N'SCHEMA',N'dbo', N'TABLE',N'statistics_recharge_channels', N'COLUMN',N'type'))
EXEC sys.sp_dropextendedproperty @name=N'MS_Description' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'statistics_recharge_channels', @level2type=N'COLUMN',@level2name=N'type'

GO
IF  EXISTS (SELECT * FROM ::fn_listextendedproperty(N'MS_Description' , N'SCHEMA',N'dbo', N'TABLE',N'statistics_recharge_channels', N'COLUMN',N'statistics_time'))
EXEC sys.sp_dropextendedproperty @name=N'MS_Description' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'statistics_recharge_channels', @level2type=N'COLUMN',@level2name=N'statistics_time'

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__statistic__creat__4119A21D]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[statistics_recharge_channels] DROP CONSTRAINT [DF__statistic__creat__4119A21D]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__statistic__total__40257DE4]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[statistics_recharge_channels] DROP CONSTRAINT [DF__statistic__total__40257DE4]
END

GO
/****** Object:  Index [statistics_retentions_type_index]    Script Date: 2019/11/28 18:16:58 ******/
IF  EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[statistics_recharge_channels]') AND name = N'statistics_retentions_type_index')
DROP INDEX [statistics_retentions_type_index] ON [dbo].[statistics_recharge_channels]
GO
/****** Object:  Index [statistics_retentions_statistics_time_index]    Script Date: 2019/11/28 18:16:58 ******/
IF  EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[statistics_recharge_channels]') AND name = N'statistics_retentions_statistics_time_index')
DROP INDEX [statistics_retentions_statistics_time_index] ON [dbo].[statistics_recharge_channels]
GO
/****** Object:  Table [dbo].[statistics_recharge_channels]    Script Date: 2019/11/28 18:16:58 ******/
IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[statistics_recharge_channels]') AND type in (N'U'))
DROP TABLE [dbo].[statistics_recharge_channels]
GO
/****** Object:  Table [dbo].[statistics_recharge_channels]    Script Date: 2019/11/28 18:16:58 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[statistics_recharge_channels]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[statistics_recharge_channels](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[statistics_time] [date] NOT NULL,
	[type] [int] NOT NULL,
	[total] [int] NOT NULL,
	[created_at] [datetime] NOT NULL,
	[channel_id] [bigint] NOT NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
END
GO
/****** Object:  Index [statistics_retentions_statistics_time_index]    Script Date: 2019/11/28 18:16:58 ******/
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[statistics_recharge_channels]') AND name = N'statistics_retentions_statistics_time_index')
CREATE NONCLUSTERED INDEX [statistics_retentions_statistics_time_index] ON [dbo].[statistics_recharge_channels]
(
	[statistics_time] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
/****** Object:  Index [statistics_retentions_type_index]    Script Date: 2019/11/28 18:16:58 ******/
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[statistics_recharge_channels]') AND name = N'statistics_retentions_type_index')
CREATE NONCLUSTERED INDEX [statistics_retentions_type_index] ON [dbo].[statistics_recharge_channels]
(
	[type] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__statistic__total__40257DE4]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[statistics_recharge_channels] ADD  DEFAULT ('0') FOR [total]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__statistic__creat__4119A21D]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[statistics_recharge_channels] ADD  DEFAULT (getdate()) FOR [created_at]
END

GO
IF NOT EXISTS (SELECT * FROM ::fn_listextendedproperty(N'MS_Description' , N'SCHEMA',N'dbo', N'TABLE',N'statistics_recharge_channels', N'COLUMN',N'statistics_time'))
EXEC sys.sp_addextendedproperty @name=N'MS_Description', @value=N'统计日期' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'statistics_recharge_channels', @level2type=N'COLUMN',@level2name=N'statistics_time'
GO
IF NOT EXISTS (SELECT * FROM ::fn_listextendedproperty(N'MS_Description' , N'SCHEMA',N'dbo', N'TABLE',N'statistics_recharge_channels', N'COLUMN',N'type'))
EXEC sys.sp_addextendedproperty @name=N'MS_Description', @value=N'类型' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'statistics_recharge_channels', @level2type=N'COLUMN',@level2name=N'type'
GO
IF NOT EXISTS (SELECT * FROM ::fn_listextendedproperty(N'MS_Description' , N'SCHEMA',N'dbo', N'TABLE',N'statistics_recharge_channels', N'COLUMN',N'total'))
EXEC sys.sp_addextendedproperty @name=N'MS_Description', @value=N'总数' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'statistics_recharge_channels', @level2type=N'COLUMN',@level2name=N'total'
GO
IF NOT EXISTS (SELECT * FROM ::fn_listextendedproperty(N'MS_Description' , N'SCHEMA',N'dbo', N'TABLE',N'statistics_recharge_channels', N'COLUMN',N'channel_id'))
EXEC sys.sp_addextendedproperty @name=N'MS_Description', @value=N'渠道id' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'statistics_recharge_channels', @level2type=N'COLUMN',@level2name=N'channel_id'
GO
