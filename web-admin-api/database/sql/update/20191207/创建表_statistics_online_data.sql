USE [admin_platform]
GO
/****** Object:  Table [dbo].[statistics_online_data]    Script Date: 2019/12/6 15:26:48 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[statistics_online_data](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[statistics_time] [date] NOT NULL,
	[client_type] [int] NOT NULL,
	[total] [int] NOT NULL,
	[created_at] [datetime] NOT NULL
) ON [PRIMARY]

GO
/****** Object:  Index [IX_statistics_online_data]    Script Date: 2019/12/6 15:26:48 ******/
CREATE NONCLUSTERED INDEX [IX_statistics_online_data] ON [dbo].[statistics_online_data]
(
	[id] ASC,
	[client_type] ASC,
	[created_at] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
