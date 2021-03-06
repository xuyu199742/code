USE [AgentDB]
GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__channel_w__updat__2A4B4B5E]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[channel_withdraw_record] DROP CONSTRAINT [DF__channel_w__updat__2A4B4B5E]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__channel_w__creat__29572725]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[channel_withdraw_record] DROP CONSTRAINT [DF__channel_w__creat__29572725]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__channel_w__admin__286302EC]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[channel_withdraw_record] DROP CONSTRAINT [DF__channel_w__admin__286302EC]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__channel_w__statu__276EDEB3]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[channel_withdraw_record] DROP CONSTRAINT [DF__channel_w__statu__276EDEB3]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__channel_u__creat__1DE57479]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[channel_user_relation] DROP CONSTRAINT [DF__channel_u__creat__1DE57479]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__channel_i__chann__1B0907CE]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[channel_info] DROP CONSTRAINT [DF__channel_i__chann__1B0907CE]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__channel_i__nulli__1A14E395]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[channel_info] DROP CONSTRAINT [DF__channel_i__nulli__1A14E395]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__channel_i__remar__1920BF5C]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[channel_info] DROP CONSTRAINT [DF__channel_i__remar__1920BF5C]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__channel_i__retur__182C9B23]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[channel_info] DROP CONSTRAINT [DF__channel_i__retur__182C9B23]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__channel_i__retur__173876EA]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[channel_info] DROP CONSTRAINT [DF__channel_i__retur__173876EA]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__channel_i__conta__164452B1]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[channel_info] DROP CONSTRAINT [DF__channel_i__conta__164452B1]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__channel_i__phone__15502E78]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[channel_info] DROP CONSTRAINT [DF__channel_i__phone__15502E78]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__channel_i__nickn__145C0A3F]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[channel_info] DROP CONSTRAINT [DF__channel_i__nickn__145C0A3F]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__channel_i__user___1367E606]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[channel_info] DROP CONSTRAINT [DF__channel_i__user___1367E606]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__channel_i__balan__1273C1CD]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[channel_info] DROP CONSTRAINT [DF__channel_i__balan__1273C1CD]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__channel_i__passw__117F9D94]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[channel_info] DROP CONSTRAINT [DF__channel_i__passw__117F9D94]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__channel_i__paren__108B795B]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[channel_info] DROP CONSTRAINT [DF__channel_i__paren__108B795B]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__channel_i__creat__24927208]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[channel_income] DROP CONSTRAINT [DF__channel_i__creat__24927208]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__channel_i__serve__239E4DCF]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[channel_income] DROP CONSTRAINT [DF__channel_i__serve__239E4DCF]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__channel_i__kind___22AA2996]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[channel_income] DROP CONSTRAINT [DF__channel_i__kind___22AA2996]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__channel_i__user___21B6055D]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[channel_income] DROP CONSTRAINT [DF__channel_i__user___21B6055D]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__channel_i__recor__20C1E124]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[channel_income] DROP CONSTRAINT [DF__channel_i__recor__20C1E124]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__agent_wit__admin__47DBAE45]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[agent_withdraw_record] DROP CONSTRAINT [DF__agent_wit__admin__47DBAE45]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__agent_wit__statu__46E78A0C]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[agent_withdraw_record] DROP CONSTRAINT [DF__agent_wit__statu__46E78A0C]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__agent_wit__back___45F365D3]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[agent_withdraw_record] DROP CONSTRAINT [DF__agent_wit__back___45F365D3]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__agent_wit__back___44FF419A]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[agent_withdraw_record] DROP CONSTRAINT [DF__agent_wit__back___44FF419A]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__agent_wit__phone__440B1D61]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[agent_withdraw_record] DROP CONSTRAINT [DF__agent_wit__phone__440B1D61]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__agent_with__name__4316F928]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[agent_withdraw_record] DROP CONSTRAINT [DF__agent_with__name__4316F928]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__agent_wit__score__4222D4EF]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[agent_withdraw_record] DROP CONSTRAINT [DF__agent_wit__score__4222D4EF]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__agent_wit__order__412EB0B6]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[agent_withdraw_record] DROP CONSTRAINT [DF__agent_wit__order__412EB0B6]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__agent_wit__user___403A8C7D]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[agent_withdraw_record] DROP CONSTRAINT [DF__agent_wit__user___403A8C7D]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__agent_rel__creat__31EC6D26]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[agent_relation] DROP CONSTRAINT [DF__agent_rel__creat__31EC6D26]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__agent_rela__rank__30F848ED]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[agent_relation] DROP CONSTRAINT [DF__agent_rela__rank__30F848ED]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__agent_rel__paren__300424B4]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[agent_relation] DROP CONSTRAINT [DF__agent_rel__paren__300424B4]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__agent_rat__rebat__37A5467C]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[agent_rate_config] DROP CONSTRAINT [DF__agent_rat__rebat__37A5467C]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__agent_rat__water__36B12243]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[agent_rate_config] DROP CONSTRAINT [DF__agent_rat__water__36B12243]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__agent_rat__water__35BCFE0A]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[agent_rate_config] DROP CONSTRAINT [DF__agent_rat__water__35BCFE0A]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__agent_rate__name__34C8D9D1]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[agent_rate_config] DROP CONSTRAINT [DF__agent_rate__name__34C8D9D1]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__agent_inf__balan__2D27B809]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[agent_info] DROP CONSTRAINT [DF__agent_inf__balan__2D27B809]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__agent_inc__rewar__3D5E1FD2]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[agent_income] DROP CONSTRAINT [DF__agent_inc__rewar__3D5E1FD2]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__agent_inc__team___3C69FB99]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[agent_income] DROP CONSTRAINT [DF__agent_inc__team___3C69FB99]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__agent_inc__perso__3B75D760]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[agent_income] DROP CONSTRAINT [DF__agent_inc__perso__3B75D760]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__agent_inc__user___3A81B327]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[agent_income] DROP CONSTRAINT [DF__agent_inc__user___3A81B327]
END

GO
/****** Object:  Index [withdraw_phone_index]    Script Date: 2019/11/22 12:02:43 ******/
IF  EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[channel_withdraw_record]') AND name = N'withdraw_phone_index')
DROP INDEX [withdraw_phone_index] ON [dbo].[channel_withdraw_record]
GO
/****** Object:  Index [withdraw_order_no]    Script Date: 2019/11/22 12:02:43 ******/
IF  EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[channel_withdraw_record]') AND name = N'withdraw_order_no')
DROP INDEX [withdraw_order_no] ON [dbo].[channel_withdraw_record]
GO
/****** Object:  Index [withdraw_card_no_index]    Script Date: 2019/11/22 12:02:43 ******/
IF  EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[channel_withdraw_record]') AND name = N'withdraw_card_no_index')
DROP INDEX [withdraw_card_no_index] ON [dbo].[channel_withdraw_record]
GO
/****** Object:  Index [users_id_unique]    Script Date: 2019/11/22 12:02:43 ******/
IF  EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[channel_user_relation]') AND name = N'users_id_unique')
DROP INDEX [users_id_unique] ON [dbo].[channel_user_relation]
GO
/****** Object:  Index [phone_unique]    Script Date: 2019/11/22 12:02:43 ******/
IF  EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[channel_info]') AND name = N'phone_unique')
DROP INDEX [phone_unique] ON [dbo].[channel_info]
GO
/****** Object:  Index [order_no_unique]    Script Date: 2019/11/22 12:02:43 ******/
IF  EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[agent_withdraw_record]') AND name = N'order_no_unique')
DROP INDEX [order_no_unique] ON [dbo].[agent_withdraw_record]
GO
/****** Object:  Index [users_id_unique]    Script Date: 2019/11/22 12:02:43 ******/
IF  EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[agent_relation]') AND name = N'users_id_unique')
DROP INDEX [users_id_unique] ON [dbo].[agent_relation]
GO
/****** Object:  Index [users_id_unique]    Script Date: 2019/11/22 12:02:43 ******/
IF  EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[agent_info]') AND name = N'users_id_unique')
DROP INDEX [users_id_unique] ON [dbo].[agent_info]
GO
/****** Object:  Table [dbo].[channel_withdraw_record]    Script Date: 2019/11/22 12:02:43 ******/
IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[channel_withdraw_record]') AND type in (N'U'))
DROP TABLE [dbo].[channel_withdraw_record]
GO
/****** Object:  Table [dbo].[channel_user_relation]    Script Date: 2019/11/22 12:02:43 ******/
IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[channel_user_relation]') AND type in (N'U'))
DROP TABLE [dbo].[channel_user_relation]
GO
/****** Object:  Table [dbo].[channel_info]    Script Date: 2019/11/22 12:02:43 ******/
IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[channel_info]') AND type in (N'U'))
DROP TABLE [dbo].[channel_info]
GO
/****** Object:  Table [dbo].[channel_income]    Script Date: 2019/11/22 12:02:43 ******/
IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[channel_income]') AND type in (N'U'))
DROP TABLE [dbo].[channel_income]
GO
/****** Object:  Table [dbo].[agent_withdraw_record]    Script Date: 2019/11/22 12:02:43 ******/
IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[agent_withdraw_record]') AND type in (N'U'))
DROP TABLE [dbo].[agent_withdraw_record]
GO
/****** Object:  Table [dbo].[agent_relation]    Script Date: 2019/11/22 12:02:43 ******/
IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[agent_relation]') AND type in (N'U'))
DROP TABLE [dbo].[agent_relation]
GO
/****** Object:  Table [dbo].[agent_rate_config]    Script Date: 2019/11/22 12:02:43 ******/
IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[agent_rate_config]') AND type in (N'U'))
DROP TABLE [dbo].[agent_rate_config]
GO
/****** Object:  Table [dbo].[agent_info]    Script Date: 2019/11/22 12:02:43 ******/
IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[agent_info]') AND type in (N'U'))
DROP TABLE [dbo].[agent_info]
GO
/****** Object:  Table [dbo].[agent_income]    Script Date: 2019/11/22 12:02:43 ******/
IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[agent_income]') AND type in (N'U'))
DROP TABLE [dbo].[agent_income]
GO
/****** Object:  Table [dbo].[agent_income]    Script Date: 2019/11/22 12:02:43 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[agent_income]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[agent_income](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[user_id] [int] NOT NULL,
	[person_score] [bigint] NOT NULL,
	[team_score] [bigint] NOT NULL,
	[reward_score] [bigint] NOT NULL,
	[start_date] [date] NOT NULL,
	[end_date] [date] NOT NULL,
	[created_at] [datetime] NOT NULL,
PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
END
GO
/****** Object:  Table [dbo].[agent_info]    Script Date: 2019/11/22 12:02:43 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[agent_info]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[agent_info](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[user_id] [int] NOT NULL,
	[balance] [bigint] NOT NULL,
	[created_at] [datetime] NULL,
	[updated_at] [datetime] NULL,
PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
END
GO
/****** Object:  Table [dbo].[agent_rate_config]    Script Date: 2019/11/22 12:02:43 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[agent_rate_config]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[agent_rate_config](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[name] [nvarchar](10) NOT NULL,
	[water_min] [bigint] NOT NULL,
	[water_max] [bigint] NOT NULL,
	[rebate] [int] NOT NULL,
	[created_at] [datetime] NULL,
	[updated_at] [datetime] NULL,
PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
END
GO
/****** Object:  Table [dbo].[agent_relation]    Script Date: 2019/11/22 12:02:43 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[agent_relation]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[agent_relation](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[user_id] [int] NOT NULL,
	[parent_user_id] [int] NOT NULL,
	[rank] [nvarchar](max) NOT NULL,
	[created_at] [datetime] NOT NULL,
PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
END
GO
/****** Object:  Table [dbo].[agent_withdraw_record]    Script Date: 2019/11/22 12:02:43 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[agent_withdraw_record]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[agent_withdraw_record](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[user_id] [int] NOT NULL,
	[order_no] [nvarchar](32) NOT NULL,
	[score] [bigint] NOT NULL,
	[name] [nvarchar](255) NOT NULL,
	[phonenum] [nvarchar](255) NOT NULL,
	[back_name] [nvarchar](255) NOT NULL,
	[back_card] [nvarchar](255) NOT NULL,
	[status] [tinyint] NOT NULL,
	[admin_id] [int] NOT NULL,
	[created_at] [datetime] NULL,
	[updated_at] [datetime] NULL,
PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
END
GO
/****** Object:  Table [dbo].[channel_income]    Script Date: 2019/11/22 12:02:43 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[channel_income]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[channel_income](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[channel_id] [bigint] NOT NULL,
	[record_type] [tinyint] NOT NULL,
	[user_id] [bigint] NOT NULL,
	[stream_score] [bigint] NOT NULL,
	[return_score] [bigint] NOT NULL,
	[kind_id] [int] NOT NULL,
	[server_id] [int] NOT NULL,
	[created_at] [datetime] NOT NULL,
PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
END
GO
/****** Object:  Table [dbo].[channel_info]    Script Date: 2019/11/22 12:02:43 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[channel_info]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[channel_info](
	[channel_id] [int] IDENTITY(1,1) NOT NULL,
	[admin_id] [bigint] NOT NULL,
	[parent_id] [bigint] NOT NULL,
	[password] [nvarchar](100) NOT NULL,
	[balance] [bigint] NOT NULL,
	[user_id] [int] NOT NULL,
	[nickname] [nvarchar](16) NOT NULL,
	[phone] [nvarchar](11) NOT NULL,
	[contact_address] [nvarchar](50) NOT NULL,
	[return_type] [tinyint] NOT NULL,
	[return_rate] [smallint] NOT NULL,
	[remarks] [nvarchar](100) NULL,
	[nullity] [tinyint] NOT NULL,
	[channel_domain] [nvarchar](50) NOT NULL,
	[deleted_at] [datetime] NULL,
	[created_at] [datetime] NULL,
	[updated_at] [datetime] NULL,
PRIMARY KEY CLUSTERED
(
	[channel_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
END
GO
/****** Object:  Table [dbo].[channel_user_relation]    Script Date: 2019/11/22 12:02:43 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[channel_user_relation]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[channel_user_relation](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[channel_id] [int] NOT NULL,
	[user_id] [int] NOT NULL,
	[created_at] [datetime] NOT NULL,
PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
END
GO
/****** Object:  Table [dbo].[channel_withdraw_record]    Script Date: 2019/11/22 12:02:43 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[channel_withdraw_record]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[channel_withdraw_record](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[channel_id] [nchar](10) NOT NULL,
	[order_no] [nvarchar](255) NOT NULL,
	[card_no] [nvarchar](255) NOT NULL,
	[bank_info] [nvarchar](255) NULL,
	[payee] [nvarchar](255) NOT NULL,
	[phone] [nvarchar](255) NOT NULL,
	[value] [int] NOT NULL,
	[status] [tinyint] NOT NULL,
	[admin_id] [int] NOT NULL,
	[created_at] [datetime] NOT NULL,
	[updated_at] [datetime] NOT NULL,
PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
END
GO
/****** Object:  Index [users_id_unique]    Script Date: 2019/11/22 12:02:43 ******/
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[agent_info]') AND name = N'users_id_unique')
CREATE UNIQUE NONCLUSTERED INDEX [users_id_unique] ON [dbo].[agent_info]
(
	[user_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
/****** Object:  Index [users_id_unique]    Script Date: 2019/11/22 12:02:43 ******/
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[agent_relation]') AND name = N'users_id_unique')
CREATE UNIQUE NONCLUSTERED INDEX [users_id_unique] ON [dbo].[agent_relation]
(
	[user_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
SET ANSI_PADDING ON

GO
/****** Object:  Index [order_no_unique]    Script Date: 2019/11/22 12:02:43 ******/
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[agent_withdraw_record]') AND name = N'order_no_unique')
CREATE UNIQUE NONCLUSTERED INDEX [order_no_unique] ON [dbo].[agent_withdraw_record]
(
	[order_no] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
SET ANSI_PADDING ON

GO
/****** Object:  Index [phone_unique]    Script Date: 2019/11/22 12:02:43 ******/
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[channel_info]') AND name = N'phone_unique')
CREATE UNIQUE NONCLUSTERED INDEX [phone_unique] ON [dbo].[channel_info]
(
	[phone] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
/****** Object:  Index [users_id_unique]    Script Date: 2019/11/22 12:02:43 ******/
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[channel_user_relation]') AND name = N'users_id_unique')
CREATE UNIQUE NONCLUSTERED INDEX [users_id_unique] ON [dbo].[channel_user_relation]
(
	[user_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
SET ANSI_PADDING ON

GO
/****** Object:  Index [withdraw_card_no_index]    Script Date: 2019/11/22 12:02:43 ******/
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[channel_withdraw_record]') AND name = N'withdraw_card_no_index')
CREATE NONCLUSTERED INDEX [withdraw_card_no_index] ON [dbo].[channel_withdraw_record]
(
	[card_no] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
SET ANSI_PADDING ON

GO
/****** Object:  Index [withdraw_order_no]    Script Date: 2019/11/22 12:02:43 ******/
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[channel_withdraw_record]') AND name = N'withdraw_order_no')
CREATE UNIQUE NONCLUSTERED INDEX [withdraw_order_no] ON [dbo].[channel_withdraw_record]
(
	[order_no] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
SET ANSI_PADDING ON

GO
/****** Object:  Index [withdraw_phone_index]    Script Date: 2019/11/22 12:02:43 ******/
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[channel_withdraw_record]') AND name = N'withdraw_phone_index')
CREATE NONCLUSTERED INDEX [withdraw_phone_index] ON [dbo].[channel_withdraw_record]
(
	[phone] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__agent_inc__user___3A81B327]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[agent_income] ADD  DEFAULT ('0') FOR [user_id]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__agent_inc__perso__3B75D760]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[agent_income] ADD  DEFAULT ('0') FOR [person_score]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__agent_inc__team___3C69FB99]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[agent_income] ADD  DEFAULT ('0') FOR [team_score]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__agent_inc__rewar__3D5E1FD2]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[agent_income] ADD  DEFAULT ('0') FOR [reward_score]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__agent_inf__balan__2D27B809]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[agent_info] ADD  DEFAULT ('0') FOR [balance]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__agent_rate__name__34C8D9D1]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[agent_rate_config] ADD  DEFAULT ('') FOR [name]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__agent_rat__water__35BCFE0A]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[agent_rate_config] ADD  DEFAULT ('0') FOR [water_min]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__agent_rat__water__36B12243]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[agent_rate_config] ADD  DEFAULT ('0') FOR [water_max]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__agent_rat__rebat__37A5467C]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[agent_rate_config] ADD  DEFAULT ('0') FOR [rebate]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__agent_rel__paren__300424B4]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[agent_relation] ADD  DEFAULT ('0') FOR [parent_user_id]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__agent_rela__rank__30F848ED]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[agent_relation] ADD  DEFAULT ('0,') FOR [rank]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__agent_rel__creat__31EC6D26]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[agent_relation] ADD  DEFAULT (getdate()) FOR [created_at]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__agent_wit__user___403A8C7D]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[agent_withdraw_record] ADD  DEFAULT ('0') FOR [user_id]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__agent_wit__order__412EB0B6]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[agent_withdraw_record] ADD  DEFAULT ('') FOR [order_no]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__agent_wit__score__4222D4EF]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[agent_withdraw_record] ADD  DEFAULT ('0') FOR [score]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__agent_with__name__4316F928]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[agent_withdraw_record] ADD  DEFAULT ('') FOR [name]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__agent_wit__phone__440B1D61]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[agent_withdraw_record] ADD  DEFAULT ('') FOR [phonenum]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__agent_wit__back___44FF419A]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[agent_withdraw_record] ADD  DEFAULT ('') FOR [back_name]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__agent_wit__back___45F365D3]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[agent_withdraw_record] ADD  DEFAULT ('') FOR [back_card]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__agent_wit__statu__46E78A0C]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[agent_withdraw_record] ADD  DEFAULT ('0') FOR [status]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__agent_wit__admin__47DBAE45]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[agent_withdraw_record] ADD  DEFAULT ('0') FOR [admin_id]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__channel_i__recor__20C1E124]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[channel_income] ADD  DEFAULT ('0') FOR [record_type]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__channel_i__user___21B6055D]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[channel_income] ADD  DEFAULT ('0') FOR [user_id]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__channel_i__kind___22AA2996]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[channel_income] ADD  DEFAULT ('0') FOR [kind_id]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__channel_i__serve__239E4DCF]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[channel_income] ADD  DEFAULT ('0') FOR [server_id]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__channel_i__creat__24927208]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[channel_income] ADD  DEFAULT (getdate()) FOR [created_at]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__channel_i__paren__108B795B]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[channel_info] ADD  DEFAULT ('0') FOR [parent_id]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__channel_i__passw__117F9D94]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[channel_info] ADD  DEFAULT ('') FOR [password]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__channel_i__balan__1273C1CD]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[channel_info] ADD  DEFAULT ('0') FOR [balance]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__channel_i__user___1367E606]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[channel_info] ADD  DEFAULT ('0') FOR [user_id]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__channel_i__nickn__145C0A3F]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[channel_info] ADD  DEFAULT ('') FOR [nickname]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__channel_i__phone__15502E78]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[channel_info] ADD  DEFAULT ('') FOR [phone]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__channel_i__conta__164452B1]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[channel_info] ADD  DEFAULT ('') FOR [contact_address]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__channel_i__retur__173876EA]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[channel_info] ADD  DEFAULT ('1') FOR [return_type]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__channel_i__retur__182C9B23]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[channel_info] ADD  DEFAULT ('0') FOR [return_rate]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__channel_i__remar__1920BF5C]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[channel_info] ADD  DEFAULT ('') FOR [remarks]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__channel_i__nulli__1A14E395]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[channel_info] ADD  DEFAULT ('0') FOR [nullity]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__channel_i__chann__1B0907CE]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[channel_info] ADD  DEFAULT ('') FOR [channel_domain]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__channel_u__creat__1DE57479]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[channel_user_relation] ADD  DEFAULT (getdate()) FOR [created_at]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__channel_w__statu__276EDEB3]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[channel_withdraw_record] ADD  DEFAULT ('0') FOR [status]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__channel_w__admin__286302EC]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[channel_withdraw_record] ADD  DEFAULT ('0') FOR [admin_id]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__channel_w__creat__29572725]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[channel_withdraw_record] ADD  DEFAULT (getdate()) FOR [created_at]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__channel_w__updat__2A4B4B5E]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[channel_withdraw_record] ADD  DEFAULT (getdate()) FOR [updated_at]
END

GO
