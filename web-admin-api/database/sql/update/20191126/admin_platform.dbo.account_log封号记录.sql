USE [admin_platform]
GO
IF  EXISTS (SELECT * FROM ::fn_listextendedproperty(N'MS_Description' , N'SCHEMA',N'dbo', N'TABLE',N'account_log', N'COLUMN',N'create_time'))
EXEC sys.sp_dropextendedproperty @name=N'MS_Description' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'account_log', @level2type=N'COLUMN',@level2name=N'create_time'

GO
IF  EXISTS (SELECT * FROM ::fn_listextendedproperty(N'MS_Description' , N'SCHEMA',N'dbo', N'TABLE',N'account_log', N'COLUMN',N'admin_id'))
EXEC sys.sp_dropextendedproperty @name=N'MS_Description' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'account_log', @level2type=N'COLUMN',@level2name=N'admin_id'

GO
IF  EXISTS (SELECT * FROM ::fn_listextendedproperty(N'MS_Description' , N'SCHEMA',N'dbo', N'TABLE',N'account_log', N'COLUMN',N're_time'))
EXEC sys.sp_dropextendedproperty @name=N'MS_Description' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'account_log', @level2type=N'COLUMN',@level2name=N're_time'

GO
IF  EXISTS (SELECT * FROM ::fn_listextendedproperty(N'MS_Description' , N'SCHEMA',N'dbo', N'TABLE',N'account_log', N'COLUMN',N'client_type'))
EXEC sys.sp_dropextendedproperty @name=N'MS_Description' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'account_log', @level2type=N'COLUMN',@level2name=N'client_type'

GO
IF  EXISTS (SELECT * FROM ::fn_listextendedproperty(N'MS_Description' , N'SCHEMA',N'dbo', N'TABLE',N'account_log', N'COLUMN',N'phone'))
EXEC sys.sp_dropextendedproperty @name=N'MS_Description' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'account_log', @level2type=N'COLUMN',@level2name=N'phone'

GO
IF  EXISTS (SELECT * FROM ::fn_listextendedproperty(N'MS_Description' , N'SCHEMA',N'dbo', N'TABLE',N'account_log', N'COLUMN',N'remark'))
EXEC sys.sp_dropextendedproperty @name=N'MS_Description' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'account_log', @level2type=N'COLUMN',@level2name=N'remark'

GO
IF  EXISTS (SELECT * FROM ::fn_listextendedproperty(N'MS_Description' , N'SCHEMA',N'dbo', N'TABLE',N'account_log', N'COLUMN',N'ip'))
EXEC sys.sp_dropextendedproperty @name=N'MS_Description' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'account_log', @level2type=N'COLUMN',@level2name=N'ip'

GO
IF  EXISTS (SELECT * FROM ::fn_listextendedproperty(N'MS_Description' , N'SCHEMA',N'dbo', N'TABLE',N'account_log', N'COLUMN',N'type'))
EXEC sys.sp_dropextendedproperty @name=N'MS_Description' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'account_log', @level2type=N'COLUMN',@level2name=N'type'

GO
IF  EXISTS (SELECT * FROM ::fn_listextendedproperty(N'MS_Description' , N'SCHEMA',N'dbo', N'TABLE',N'account_log', N'COLUMN',N'game_id'))
EXEC sys.sp_dropextendedproperty @name=N'MS_Description' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'account_log', @level2type=N'COLUMN',@level2name=N'game_id'

GO
IF  EXISTS (SELECT * FROM ::fn_listextendedproperty(N'MS_Description' , N'SCHEMA',N'dbo', N'TABLE',N'account_log', N'COLUMN',N'user_id'))
EXEC sys.sp_dropextendedproperty @name=N'MS_Description' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'account_log', @level2type=N'COLUMN',@level2name=N'user_id'

GO
IF  EXISTS (SELECT * FROM ::fn_listextendedproperty(N'MS_Description' , N'SCHEMA',N'dbo', N'TABLE',N'account_log', N'COLUMN',N'id'))
EXEC sys.sp_dropextendedproperty @name=N'MS_Description' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'account_log', @level2type=N'COLUMN',@level2name=N'id'

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__account_l__admin__43D61337]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[account_log] DROP CONSTRAINT [DF__account_l__admin__43D61337]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__account_l__clien__42E1EEFE]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[account_log] DROP CONSTRAINT [DF__account_l__clien__42E1EEFE]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__account_l__phone__41EDCAC5]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[account_log] DROP CONSTRAINT [DF__account_l__phone__41EDCAC5]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__account_l__remar__40F9A68C]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[account_log] DROP CONSTRAINT [DF__account_l__remar__40F9A68C]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__account_log__ip__40058253]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[account_log] DROP CONSTRAINT [DF__account_log__ip__40058253]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__account_lo__type__3F115E1A]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[account_log] DROP CONSTRAINT [DF__account_lo__type__3F115E1A]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__account_l__game___3E1D39E1]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[account_log] DROP CONSTRAINT [DF__account_l__game___3E1D39E1]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__account_l__user___3D2915A8]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[account_log] DROP CONSTRAINT [DF__account_l__user___3D2915A8]
END

GO
/****** Object:  Index [user_id_index]    Script Date: 2019/11/27 17:32:39 ******/
IF  EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[account_log]') AND name = N'user_id_index')
DROP INDEX [user_id_index] ON [dbo].[account_log]
GO
/****** Object:  Index [phone_index]    Script Date: 2019/11/27 17:32:39 ******/
IF  EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[account_log]') AND name = N'phone_index')
DROP INDEX [phone_index] ON [dbo].[account_log]
GO
/****** Object:  Index [game_id_index]    Script Date: 2019/11/27 17:32:39 ******/
IF  EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[account_log]') AND name = N'game_id_index')
DROP INDEX [game_id_index] ON [dbo].[account_log]
GO
/****** Object:  Index [create_time_index]    Script Date: 2019/11/27 17:32:39 ******/
IF  EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[account_log]') AND name = N'create_time_index')
DROP INDEX [create_time_index] ON [dbo].[account_log]
GO
/****** Object:  Table [dbo].[account_log]    Script Date: 2019/11/27 17:32:39 ******/
IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[account_log]') AND type in (N'U'))
DROP TABLE [dbo].[account_log]
GO
/****** Object:  Table [dbo].[account_log]    Script Date: 2019/11/27 17:32:39 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
SET ANSI_PADDING ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[account_log]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[account_log](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[user_id] [int] NOT NULL,
	[game_id] [int] NOT NULL,
	[type] [tinyint] NOT NULL,
	[ip] [varchar](15) NOT NULL,
	[remark] [varchar](255) NOT NULL,
	[phone] [varchar](20) NOT NULL,
	[client_type] [tinyint] NOT NULL,
	[re_time] [datetime] NOT NULL,
	[admin_id] [int] NOT NULL,
	[create_time] [datetime] NOT NULL,
PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
END
GO
SET ANSI_PADDING OFF
GO
/****** Object:  Index [create_time_index]    Script Date: 2019/11/27 17:32:39 ******/
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[account_log]') AND name = N'create_time_index')
CREATE NONCLUSTERED INDEX [create_time_index] ON [dbo].[account_log]
(
	[create_time] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
/****** Object:  Index [game_id_index]    Script Date: 2019/11/27 17:32:39 ******/
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[account_log]') AND name = N'game_id_index')
CREATE NONCLUSTERED INDEX [game_id_index] ON [dbo].[account_log]
(
	[game_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
SET ANSI_PADDING ON

GO
/****** Object:  Index [phone_index]    Script Date: 2019/11/27 17:32:39 ******/
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[account_log]') AND name = N'phone_index')
CREATE NONCLUSTERED INDEX [phone_index] ON [dbo].[account_log]
(
	[phone] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
/****** Object:  Index [user_id_index]    Script Date: 2019/11/27 17:32:39 ******/
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[account_log]') AND name = N'user_id_index')
CREATE NONCLUSTERED INDEX [user_id_index] ON [dbo].[account_log]
(
	[user_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__account_l__user___3D2915A8]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[account_log] ADD  DEFAULT ((0)) FOR [user_id]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__account_l__game___3E1D39E1]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[account_log] ADD  DEFAULT ((0)) FOR [game_id]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__account_lo__type__3F115E1A]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[account_log] ADD  DEFAULT ((0)) FOR [type]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__account_log__ip__40058253]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[account_log] ADD  DEFAULT ('') FOR [ip]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__account_l__remar__40F9A68C]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[account_log] ADD  DEFAULT ('') FOR [remark]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__account_l__phone__41EDCAC5]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[account_log] ADD  DEFAULT ('') FOR [phone]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__account_l__clien__42E1EEFE]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[account_log] ADD  DEFAULT ((0)) FOR [client_type]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__account_l__admin__43D61337]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[account_log] ADD  DEFAULT ((0)) FOR [admin_id]
END

GO
IF NOT EXISTS (SELECT * FROM ::fn_listextendedproperty(N'MS_Description' , N'SCHEMA',N'dbo', N'TABLE',N'account_log', N'COLUMN',N'id'))
EXEC sys.sp_addextendedproperty @name=N'MS_Description', @value=N'主键' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'account_log', @level2type=N'COLUMN',@level2name=N'id'
GO
IF NOT EXISTS (SELECT * FROM ::fn_listextendedproperty(N'MS_Description' , N'SCHEMA',N'dbo', N'TABLE',N'account_log', N'COLUMN',N'user_id'))
EXEC sys.sp_addextendedproperty @name=N'MS_Description', @value=N'用户标识' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'account_log', @level2type=N'COLUMN',@level2name=N'user_id'
GO
IF NOT EXISTS (SELECT * FROM ::fn_listextendedproperty(N'MS_Description' , N'SCHEMA',N'dbo', N'TABLE',N'account_log', N'COLUMN',N'game_id'))
EXEC sys.sp_addextendedproperty @name=N'MS_Description', @value=N'用户游戏标识' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'account_log', @level2type=N'COLUMN',@level2name=N'game_id'
GO
IF NOT EXISTS (SELECT * FROM ::fn_listextendedproperty(N'MS_Description' , N'SCHEMA',N'dbo', N'TABLE',N'account_log', N'COLUMN',N'type'))
EXEC sys.sp_addextendedproperty @name=N'MS_Description', @value=N'操作类型：1禁止登陆,2取消禁止登陆，3禁止提现，4取消禁止提现，5禁止登陆及提现，6取消禁止登陆及提现' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'account_log', @level2type=N'COLUMN',@level2name=N'type'
GO
IF NOT EXISTS (SELECT * FROM ::fn_listextendedproperty(N'MS_Description' , N'SCHEMA',N'dbo', N'TABLE',N'account_log', N'COLUMN',N'ip'))
EXEC sys.sp_addextendedproperty @name=N'MS_Description', @value=N'操作ip' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'account_log', @level2type=N'COLUMN',@level2name=N'ip'
GO
IF NOT EXISTS (SELECT * FROM ::fn_listextendedproperty(N'MS_Description' , N'SCHEMA',N'dbo', N'TABLE',N'account_log', N'COLUMN',N'remark'))
EXEC sys.sp_addextendedproperty @name=N'MS_Description', @value=N'备注' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'account_log', @level2type=N'COLUMN',@level2name=N'remark'
GO
IF NOT EXISTS (SELECT * FROM ::fn_listextendedproperty(N'MS_Description' , N'SCHEMA',N'dbo', N'TABLE',N'account_log', N'COLUMN',N'phone'))
EXEC sys.sp_addextendedproperty @name=N'MS_Description', @value=N'注册手机号' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'account_log', @level2type=N'COLUMN',@level2name=N'phone'
GO
IF NOT EXISTS (SELECT * FROM ::fn_listextendedproperty(N'MS_Description' , N'SCHEMA',N'dbo', N'TABLE',N'account_log', N'COLUMN',N'client_type'))
EXEC sys.sp_addextendedproperty @name=N'MS_Description', @value=N'注册来源：1、android，2、ios，3、pc' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'account_log', @level2type=N'COLUMN',@level2name=N'client_type'
GO
IF NOT EXISTS (SELECT * FROM ::fn_listextendedproperty(N'MS_Description' , N'SCHEMA',N'dbo', N'TABLE',N'account_log', N'COLUMN',N're_time'))
EXEC sys.sp_addextendedproperty @name=N'MS_Description', @value=N'注册时间' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'account_log', @level2type=N'COLUMN',@level2name=N're_time'
GO
IF NOT EXISTS (SELECT * FROM ::fn_listextendedproperty(N'MS_Description' , N'SCHEMA',N'dbo', N'TABLE',N'account_log', N'COLUMN',N'admin_id'))
EXEC sys.sp_addextendedproperty @name=N'MS_Description', @value=N'操作人id' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'account_log', @level2type=N'COLUMN',@level2name=N'admin_id'
GO
IF NOT EXISTS (SELECT * FROM ::fn_listextendedproperty(N'MS_Description' , N'SCHEMA',N'dbo', N'TABLE',N'account_log', N'COLUMN',N'create_time'))
EXEC sys.sp_addextendedproperty @name=N'MS_Description', @value=N'创建时间' , @level0type=N'SCHEMA',@level0name=N'dbo', @level1type=N'TABLE',@level1name=N'account_log', @level2type=N'COLUMN',@level2name=N'create_time'
GO
