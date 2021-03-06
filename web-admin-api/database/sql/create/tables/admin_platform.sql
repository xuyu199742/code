USE [admin_platform]
GO
IF  EXISTS (SELECT * FROM sys.check_constraints WHERE object_id = OBJECT_ID(N'[dbo].[CK__system_set__lock__286302EC]') AND parent_object_id = OBJECT_ID(N'[dbo].[system_settings]'))
ALTER TABLE [dbo].[system_settings] DROP CONSTRAINT [CK__system_set__lock__286302EC]
GO
IF  EXISTS (SELECT * FROM sys.check_constraints WHERE object_id = OBJECT_ID(N'[dbo].[CK__sms_logs__status__35BCFE0A]') AND parent_object_id = OBJECT_ID(N'[dbo].[sms_logs]'))
ALTER TABLE [dbo].[sms_logs] DROP CONSTRAINT [CK__sms_logs__status__35BCFE0A]
GO
IF  EXISTS (SELECT * FROM sys.check_constraints WHERE object_id = OBJECT_ID(N'[dbo].[CK__payment_p__statu__4316F928]') AND parent_object_id = OBJECT_ID(N'[dbo].[payment_providers]'))
ALTER TABLE [dbo].[payment_providers] DROP CONSTRAINT [CK__payment_p__statu__4316F928]
GO
IF  EXISTS (SELECT * FROM sys.check_constraints WHERE object_id = OBJECT_ID(N'[dbo].[CK__payment_p__range__3E52440B]') AND parent_object_id = OBJECT_ID(N'[dbo].[payment_providers]'))
ALTER TABLE [dbo].[payment_providers] DROP CONSTRAINT [CK__payment_p__range__3E52440B]
GO
IF  EXISTS (SELECT * FROM sys.check_constraints WHERE object_id = OBJECT_ID(N'[dbo].[CK__payment_o__payme__2D27B809]') AND parent_object_id = OBJECT_ID(N'[dbo].[payment_orders]'))
ALTER TABLE [dbo].[payment_orders] DROP CONSTRAINT [CK__payment_o__payme__2D27B809]
GO
IF  EXISTS (SELECT * FROM sys.check_constraints WHERE object_id = OBJECT_ID(N'[dbo].[CK__payment_c__statu__46E78A0C]') AND parent_object_id = OBJECT_ID(N'[dbo].[payment_configs]'))
ALTER TABLE [dbo].[payment_configs] DROP CONSTRAINT [CK__payment_c__statu__46E78A0C]
GO
IF  EXISTS (SELECT * FROM sys.foreign_keys WHERE object_id = OBJECT_ID(N'[dbo].[role_has_permissions_role_id_foreign]') AND parent_object_id = OBJECT_ID(N'[dbo].[role_has_permissions]'))
ALTER TABLE [dbo].[role_has_permissions] DROP CONSTRAINT [role_has_permissions_role_id_foreign]
GO
IF  EXISTS (SELECT * FROM sys.foreign_keys WHERE object_id = OBJECT_ID(N'[dbo].[role_has_permissions_permission_id_foreign]') AND parent_object_id = OBJECT_ID(N'[dbo].[role_has_permissions]'))
ALTER TABLE [dbo].[role_has_permissions] DROP CONSTRAINT [role_has_permissions_permission_id_foreign]
GO
IF  EXISTS (SELECT * FROM sys.foreign_keys WHERE object_id = OBJECT_ID(N'[dbo].[model_has_roles_role_id_foreign]') AND parent_object_id = OBJECT_ID(N'[dbo].[model_has_roles]'))
ALTER TABLE [dbo].[model_has_roles] DROP CONSTRAINT [model_has_roles_role_id_foreign]
GO
IF  EXISTS (SELECT * FROM sys.foreign_keys WHERE object_id = OBJECT_ID(N'[dbo].[model_has_permissions_permission_id_foreign]') AND parent_object_id = OBJECT_ID(N'[dbo].[model_has_permissions]'))
ALTER TABLE [dbo].[model_has_permissions] DROP CONSTRAINT [model_has_permissions_permission_id_foreign]
GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__withdrawa__statu__31EC6D26]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[withdrawal_orders] DROP CONSTRAINT [DF__withdrawa__statu__31EC6D26]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__withdrawa__admin__30F848ED]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[withdrawal_orders] DROP CONSTRAINT [DF__withdrawa__admin__30F848ED]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__withdrawa__lock___19DFD96B]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[withdrawal_automatic] DROP CONSTRAINT [DF__withdrawa__lock___19DFD96B]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__white_ip__nullit__123EB7A3]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[white_ip] DROP CONSTRAINT [DF__white_ip__nullit__123EB7A3]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__vip_busin__nulli__7C4F7684]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[vip_businessman] DROP CONSTRAINT [DF__vip_busin__nulli__7C4F7684]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__vip_busin__platf__7B5B524B]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[vip_businessman] DROP CONSTRAINT [DF__vip_busin__platf__7B5B524B]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__vip_busine__type__7A672E12]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[vip_businessman] DROP CONSTRAINT [DF__vip_busine__type__7A672E12]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__versions__status__20C1E124]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[versions] DROP CONSTRAINT [DF__versions__status__20C1E124]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__SystemNot__Platf__06CD04F7]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[SystemNotice] DROP CONSTRAINT [DF__SystemNot__Platf__06CD04F7]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__SystemNot__Nulli__05D8E0BE]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[SystemNotice] DROP CONSTRAINT [DF__SystemNot__Nulli__05D8E0BE]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__SystemNot__IsTop__04E4BC85]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[SystemNotice] DROP CONSTRAINT [DF__SystemNot__IsTop__04E4BC85]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__SystemNot__IsHot__03F0984C]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[SystemNotice] DROP CONSTRAINT [DF__SystemNot__IsHot__03F0984C]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__SystemNot__Publi__02FC7413]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[SystemNotice] DROP CONSTRAINT [DF__SystemNot__Publi__02FC7413]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__SystemNot__Publi__02084FDA]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[SystemNotice] DROP CONSTRAINT [DF__SystemNot__Publi__02084FDA]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__SystemNot__WebCo__01142BA1]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[SystemNotice] DROP CONSTRAINT [DF__SystemNot__WebCo__01142BA1]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__SystemNot__Mobli__00200768]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[SystemNotice] DROP CONSTRAINT [DF__SystemNot__Mobli__00200768]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__SystemNot__Notic__7F2BE32F]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[SystemNotice] DROP CONSTRAINT [DF__SystemNot__Notic__7F2BE32F]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__system_set__lock__29572725]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[system_settings] DROP CONSTRAINT [DF__system_set__lock__29572725]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__statistic__syste__1DE57479]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[statistics_win_lose] DROP CONSTRAINT [DF__statistic__syste__1DE57479]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__statistic__syste__1CF15040]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[statistics_win_lose] DROP CONSTRAINT [DF__statistic__syste__1CF15040]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__statistic__jetto__1BFD2C07]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[statistics_win_lose] DROP CONSTRAINT [DF__statistic__jetto__1BFD2C07]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__statistic__chang__1B0907CE]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[statistics_win_lose] DROP CONSTRAINT [DF__statistic__chang__1B0907CE]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__statistic__creat__5BE2A6F2]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[statistics_retentions] DROP CONSTRAINT [DF__statistic__creat__5BE2A6F2]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__statistic__total__5AEE82B9]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[statistics_retentions] DROP CONSTRAINT [DF__statistic__total__5AEE82B9]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__statistic__creat__151B244E]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[statistics_game_data] DROP CONSTRAINT [DF__statistic__creat__151B244E]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__sms_logs__status__36B12243]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[sms_logs] DROP CONSTRAINT [DF__sms_logs__status__36B12243]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__sms_logs__type__34C8D9D1]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[sms_logs] DROP CONSTRAINT [DF__sms_logs__type__34C8D9D1]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__register___platf__6E01572D]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[register_give] DROP CONSTRAINT [DF__register___platf__6E01572D]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__register___give___6D0D32F4]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[register_give] DROP CONSTRAINT [DF__register___give___6D0D32F4]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__register___score__6C190EBB]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[register_give] DROP CONSTRAINT [DF__register___score__6C190EBB]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__recharge___state__5070F446]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[recharge_wechats] DROP CONSTRAINT [DF__recharge___state__5070F446]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__recharge_w__sort__4F7CD00D]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[recharge_wechats] DROP CONSTRAINT [DF__recharge_w__sort__4F7CD00D]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__recharge___state__5812160E]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[recharge_unions] DROP CONSTRAINT [DF__recharge___state__5812160E]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__recharge_u__sort__571DF1D5]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[recharge_unions] DROP CONSTRAINT [DF__recharge_u__sort__571DF1D5]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__recharge___state__5441852A]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[recharge_alipays] DROP CONSTRAINT [DF__recharge___state__5441852A]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__recharge_a__sort__534D60F1]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[recharge_alipays] DROP CONSTRAINT [DF__recharge_a__sort__534D60F1]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__recharge___state__4CA06362]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[recharge_agents] DROP CONSTRAINT [DF__recharge___state__4CA06362]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__recharge_a__sort__4BAC3F29]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[recharge_agents] DROP CONSTRAINT [DF__recharge_a__sort__4BAC3F29]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__payment_p__statu__440B1D61]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[payment_providers] DROP CONSTRAINT [DF__payment_p__statu__440B1D61]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__payment_pr__rate__4222D4EF]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[payment_providers] DROP CONSTRAINT [DF__payment_pr__rate__4222D4EF]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__payment_p__max_v__412EB0B6]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[payment_providers] DROP CONSTRAINT [DF__payment_p__max_v__412EB0B6]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__payment_p__min_v__403A8C7D]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[payment_providers] DROP CONSTRAINT [DF__payment_p__min_v__403A8C7D]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__payment_p__range__3F466844]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[payment_providers] DROP CONSTRAINT [DF__payment_p__range__3F466844]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__payment_p__weigh__3D5E1FD2]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[payment_providers] DROP CONSTRAINT [DF__payment_p__weigh__3D5E1FD2]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__payment_o__payme__2E1BDC42]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[payment_orders] DROP CONSTRAINT [DF__payment_o__payme__2E1BDC42]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__payment_o__admin__2C3393D0]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[payment_orders] DROP CONSTRAINT [DF__payment_o__admin__2C3393D0]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__payment_co__sort__48CFD27E]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[payment_configs] DROP CONSTRAINT [DF__payment_co__sort__48CFD27E]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__payment_c__statu__47DBAE45]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[payment_configs] DROP CONSTRAINT [DF__payment_c__statu__47DBAE45]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__login_log__admin__778AC167]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[login_logs] DROP CONSTRAINT [DF__login_log__admin__778AC167]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__failed_jo__faile__1CBC4616]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[failed_jobs] DROP CONSTRAINT [DF__failed_jo__faile__1CBC4616]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__carousel_we__url__0F624AF8]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[carousel_website] DROP CONSTRAINT [DF__carousel_we__url__0F624AF8]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__carousel_a__link__0C85DE4D]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[carousel_affiche] DROP CONSTRAINT [DF__carousel_a__link__0C85DE4D]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__carousel___image__0B91BA14]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[carousel_affiche] DROP CONSTRAINT [DF__carousel___image__0B91BA14]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__carousel_a__sort__0A9D95DB]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[carousel_affiche] DROP CONSTRAINT [DF__carousel_a__sort__0A9D95DB]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__carousel_a__type__09A971A2]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[carousel_affiche] DROP CONSTRAINT [DF__carousel_a__type__09A971A2]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__ads__remark__74AE54BC]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[ads] DROP CONSTRAINT [DF__ads__remark__74AE54BC]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__ads__type__73BA3083]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[ads] DROP CONSTRAINT [DF__ads__type__73BA3083]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__ads__link_url__72C60C4A]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[ads] DROP CONSTRAINT [DF__ads__link_url__72C60C4A]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__ads__resource_ur__71D1E811]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[ads] DROP CONSTRAINT [DF__ads__resource_ur__71D1E811]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__ads__title__70DDC3D8]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[ads] DROP CONSTRAINT [DF__ads__title__70DDC3D8]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__admin_users__sex__164452B1]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[admin_users] DROP CONSTRAINT [DF__admin_users__sex__164452B1]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__admin_use__admin__15502E78]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[admin_users] DROP CONSTRAINT [DF__admin_use__admin__15502E78]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__accounts___withd__25869641]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[accounts_set] DROP CONSTRAINT [DF__accounts___withd__25869641]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__accounts___trans__24927208]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[accounts_set] DROP CONSTRAINT [DF__accounts___trans__24927208]
END

GO
IF  EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__accounts___nulli__239E4DCF]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[accounts_set] DROP CONSTRAINT [DF__accounts___nulli__239E4DCF]
END

GO
/****** Object:  Index [withdrawal_status_index]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[withdrawal_orders]') AND name = N'withdrawal_status_index')
DROP INDEX [withdrawal_status_index] ON [dbo].[withdrawal_orders]
GO
/****** Object:  Index [withdrawal_real_gold_coins_index]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[withdrawal_orders]') AND name = N'withdrawal_real_gold_coins_index')
DROP INDEX [withdrawal_real_gold_coins_index] ON [dbo].[withdrawal_orders]
GO
/****** Object:  Index [withdrawal_phone_index]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[withdrawal_orders]') AND name = N'withdrawal_phone_index')
DROP INDEX [withdrawal_phone_index] ON [dbo].[withdrawal_orders]
GO
/****** Object:  Index [withdrawal_orders_withdrawal_type]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[withdrawal_orders]') AND name = N'withdrawal_orders_withdrawal_type')
DROP INDEX [withdrawal_orders_withdrawal_type] ON [dbo].[withdrawal_orders]
GO
/****** Object:  Index [withdrawal_order_no]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[withdrawal_orders]') AND name = N'withdrawal_order_no')
DROP INDEX [withdrawal_order_no] ON [dbo].[withdrawal_orders]
GO
/****** Object:  Index [withdrawal_gold_coins_index]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[withdrawal_orders]') AND name = N'withdrawal_gold_coins_index')
DROP INDEX [withdrawal_gold_coins_index] ON [dbo].[withdrawal_orders]
GO
/****** Object:  Index [withdrawal_card_no_index]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[withdrawal_orders]') AND name = N'withdrawal_card_no_index')
DROP INDEX [withdrawal_card_no_index] ON [dbo].[withdrawal_orders]
GO
/****** Object:  Index [withdrawal_admin_id_index]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[withdrawal_orders]') AND name = N'withdrawal_admin_id_index')
DROP INDEX [withdrawal_admin_id_index] ON [dbo].[withdrawal_orders]
GO
/****** Object:  Index [game_id_index]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[withdrawal_orders]') AND name = N'game_id_index')
DROP INDEX [game_id_index] ON [dbo].[withdrawal_orders]
GO
/****** Object:  Index [withdrawal_status_index]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[withdrawal_automatic]') AND name = N'withdrawal_status_index')
DROP INDEX [withdrawal_status_index] ON [dbo].[withdrawal_automatic]
GO
/****** Object:  Index [withdrawal_order_id]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[withdrawal_automatic]') AND name = N'withdrawal_order_id')
DROP INDEX [withdrawal_order_id] ON [dbo].[withdrawal_automatic]
GO
/****** Object:  Index [withdrawal_lock_id_index]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[withdrawal_automatic]') AND name = N'withdrawal_lock_id_index')
DROP INDEX [withdrawal_lock_id_index] ON [dbo].[withdrawal_automatic]
GO
/****** Object:  Index [third_order_no_index]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[withdrawal_automatic]') AND name = N'third_order_no_index')
DROP INDEX [third_order_no_index] ON [dbo].[withdrawal_automatic]
GO
/****** Object:  Index [order_no_index]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[withdrawal_automatic]') AND name = N'order_no_index')
DROP INDEX [order_no_index] ON [dbo].[withdrawal_automatic]
GO
/****** Object:  Index [ip_unique]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[white_ip]') AND name = N'ip_unique')
DROP INDEX [ip_unique] ON [dbo].[white_ip]
GO
/****** Object:  Index [vip_businessman_admin_id_user_id_index]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[vip_businessman]') AND name = N'vip_businessman_admin_id_user_id_index')
DROP INDEX [vip_businessman_admin_id_user_id_index] ON [dbo].[vip_businessman]
GO
/****** Object:  Index [version_id_unique]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[versions]') AND name = N'version_id_unique')
DROP INDEX [version_id_unique] ON [dbo].[versions]
GO
/****** Object:  Index [version_admin_id_index]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[versions]') AND name = N'version_admin_id_index')
DROP INDEX [version_admin_id_index] ON [dbo].[versions]
GO
/****** Object:  Index [group_key_unique]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[system_settings]') AND name = N'group_key_unique')
DROP INDEX [group_key_unique] ON [dbo].[system_settings]
GO
/****** Object:  Index [statistics_retentions_type_index]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[statistics_retentions]') AND name = N'statistics_retentions_type_index')
DROP INDEX [statistics_retentions_type_index] ON [dbo].[statistics_retentions]
GO
/****** Object:  Index [statistics_retentions_statistics_time_index]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[statistics_retentions]') AND name = N'statistics_retentions_statistics_time_index')
DROP INDEX [statistics_retentions_statistics_time_index] ON [dbo].[statistics_retentions]
GO
/****** Object:  Index [group_key_unique]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[register_give]') AND name = N'group_key_unique')
DROP INDEX [group_key_unique] ON [dbo].[register_give]
GO
/****** Object:  Index [permissions_group]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[permissions]') AND name = N'permissions_group')
DROP INDEX [permissions_group] ON [dbo].[permissions]
GO
/****** Object:  Index [payment_providers_provider_key_unique]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[payment_providers]') AND name = N'payment_providers_provider_key_unique')
DROP INDEX [payment_providers_provider_key_unique] ON [dbo].[payment_providers]
GO
/****** Object:  Index [payment_providers_pay_type_index]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[payment_providers]') AND name = N'payment_providers_pay_type_index')
DROP INDEX [payment_providers_pay_type_index] ON [dbo].[payment_providers]
GO
/****** Object:  Index [user_id_index]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[payment_orders]') AND name = N'user_id_index')
DROP INDEX [user_id_index] ON [dbo].[payment_orders]
GO
/****** Object:  Index [third_order_no_index]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[payment_orders]') AND name = N'third_order_no_index')
DROP INDEX [third_order_no_index] ON [dbo].[payment_orders]
GO
/****** Object:  Index [payment_provider_id_index]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[payment_orders]') AND name = N'payment_provider_id_index')
DROP INDEX [payment_provider_id_index] ON [dbo].[payment_orders]
GO
/****** Object:  Index [order_no_unique]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[payment_orders]') AND name = N'order_no_unique')
DROP INDEX [order_no_unique] ON [dbo].[payment_orders]
GO
/****** Object:  Index [game_id_index]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[payment_orders]') AND name = N'game_id_index')
DROP INDEX [game_id_index] ON [dbo].[payment_orders]
GO
/****** Object:  Index [admin_id_index]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[payment_orders]') AND name = N'admin_id_index')
DROP INDEX [admin_id_index] ON [dbo].[payment_orders]
GO
/****** Object:  Index [payment_configs_key_unique]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[payment_configs]') AND name = N'payment_configs_key_unique')
DROP INDEX [payment_configs_key_unique] ON [dbo].[payment_configs]
GO
/****** Object:  Index [model_has_roles_model_id_model_type_index]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[model_has_roles]') AND name = N'model_has_roles_model_id_model_type_index')
DROP INDEX [model_has_roles_model_id_model_type_index] ON [dbo].[model_has_roles]
GO
/****** Object:  Index [model_has_permissions_model_id_model_type_index]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[model_has_permissions]') AND name = N'model_has_permissions_model_id_model_type_index')
DROP INDEX [model_has_permissions_model_id_model_type_index] ON [dbo].[model_has_permissions]
GO
/****** Object:  Index [jobs_queue_index]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[jobs]') AND name = N'jobs_queue_index')
DROP INDEX [jobs_queue_index] ON [dbo].[jobs]
GO
/****** Object:  Index [error_logs_level_index]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[error_logs]') AND name = N'error_logs_level_index')
DROP INDEX [error_logs_level_index] ON [dbo].[error_logs]
GO
/****** Object:  Index [error_logs_instance_index]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[error_logs]') AND name = N'error_logs_instance_index')
DROP INDEX [error_logs_instance_index] ON [dbo].[error_logs]
GO
/****** Object:  Index [error_logs_created_by_index]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[error_logs]') AND name = N'error_logs_created_by_index')
DROP INDEX [error_logs_created_by_index] ON [dbo].[error_logs]
GO
/****** Object:  Index [error_logs_channel_index]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[error_logs]') AND name = N'error_logs_channel_index')
DROP INDEX [error_logs_channel_index] ON [dbo].[error_logs]
GO
/****** Object:  Index [users_mobile_unique]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[admin_users]') AND name = N'users_mobile_unique')
DROP INDEX [users_mobile_unique] ON [dbo].[admin_users]
GO
/****** Object:  Index [users_email_unique]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[admin_users]') AND name = N'users_email_unique')
DROP INDEX [users_email_unique] ON [dbo].[admin_users]
GO
/****** Object:  Index [users_id_unique]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[accounts_set]') AND name = N'users_id_unique')
DROP INDEX [users_id_unique] ON [dbo].[accounts_set]
GO
/****** Object:  Table [dbo].[withdrawal_orders]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[withdrawal_orders]') AND type in (N'U'))
DROP TABLE [dbo].[withdrawal_orders]
GO
/****** Object:  Table [dbo].[withdrawal_automatic]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[withdrawal_automatic]') AND type in (N'U'))
DROP TABLE [dbo].[withdrawal_automatic]
GO
/****** Object:  Table [dbo].[white_ip]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[white_ip]') AND type in (N'U'))
DROP TABLE [dbo].[white_ip]
GO
/****** Object:  Table [dbo].[vip_businessman]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[vip_businessman]') AND type in (N'U'))
DROP TABLE [dbo].[vip_businessman]
GO
/****** Object:  Table [dbo].[versions]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[versions]') AND type in (N'U'))
DROP TABLE [dbo].[versions]
GO
/****** Object:  Table [dbo].[SystemNotice]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[SystemNotice]') AND type in (N'U'))
DROP TABLE [dbo].[SystemNotice]
GO
/****** Object:  Table [dbo].[system_sms_config]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[system_sms_config]') AND type in (N'U'))
DROP TABLE [dbo].[system_sms_config]
GO
/****** Object:  Table [dbo].[system_settings]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[system_settings]') AND type in (N'U'))
DROP TABLE [dbo].[system_settings]
GO
/****** Object:  Table [dbo].[system_logs]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[system_logs]') AND type in (N'U'))
DROP TABLE [dbo].[system_logs]
GO
/****** Object:  Table [dbo].[statistics_win_lose]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[statistics_win_lose]') AND type in (N'U'))
DROP TABLE [dbo].[statistics_win_lose]
GO
/****** Object:  Table [dbo].[statistics_retentions]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[statistics_retentions]') AND type in (N'U'))
DROP TABLE [dbo].[statistics_retentions]
GO
/****** Object:  Table [dbo].[statistics_game_data]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[statistics_game_data]') AND type in (N'U'))
DROP TABLE [dbo].[statistics_game_data]
GO
/****** Object:  Table [dbo].[sms_logs]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[sms_logs]') AND type in (N'U'))
DROP TABLE [dbo].[sms_logs]
GO
/****** Object:  Table [dbo].[roles]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[roles]') AND type in (N'U'))
DROP TABLE [dbo].[roles]
GO
/****** Object:  Table [dbo].[role_has_permissions]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[role_has_permissions]') AND type in (N'U'))
DROP TABLE [dbo].[role_has_permissions]
GO
/****** Object:  Table [dbo].[register_give]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[register_give]') AND type in (N'U'))
DROP TABLE [dbo].[register_give]
GO
/****** Object:  Table [dbo].[recharge_wechats]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[recharge_wechats]') AND type in (N'U'))
DROP TABLE [dbo].[recharge_wechats]
GO
/****** Object:  Table [dbo].[recharge_unions]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[recharge_unions]') AND type in (N'U'))
DROP TABLE [dbo].[recharge_unions]
GO
/****** Object:  Table [dbo].[recharge_alipays]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[recharge_alipays]') AND type in (N'U'))
DROP TABLE [dbo].[recharge_alipays]
GO
/****** Object:  Table [dbo].[recharge_agents]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[recharge_agents]') AND type in (N'U'))
DROP TABLE [dbo].[recharge_agents]
GO
/****** Object:  Table [dbo].[permissions]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[permissions]') AND type in (N'U'))
DROP TABLE [dbo].[permissions]
GO
/****** Object:  Table [dbo].[payment_providers]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[payment_providers]') AND type in (N'U'))
DROP TABLE [dbo].[payment_providers]
GO
/****** Object:  Table [dbo].[payment_orders]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[payment_orders]') AND type in (N'U'))
DROP TABLE [dbo].[payment_orders]
GO
/****** Object:  Table [dbo].[payment_configs]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[payment_configs]') AND type in (N'U'))
DROP TABLE [dbo].[payment_configs]
GO
/****** Object:  Table [dbo].[order_logs]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[order_logs]') AND type in (N'U'))
DROP TABLE [dbo].[order_logs]
GO
/****** Object:  Table [dbo].[model_has_roles]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[model_has_roles]') AND type in (N'U'))
DROP TABLE [dbo].[model_has_roles]
GO
/****** Object:  Table [dbo].[model_has_permissions]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[model_has_permissions]') AND type in (N'U'))
DROP TABLE [dbo].[model_has_permissions]
GO
/****** Object:  Table [dbo].[migrations]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[migrations]') AND type in (N'U'))
DROP TABLE [dbo].[migrations]
GO
/****** Object:  Table [dbo].[login_logs]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[login_logs]') AND type in (N'U'))
DROP TABLE [dbo].[login_logs]
GO
/****** Object:  Table [dbo].[jobs]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[jobs]') AND type in (N'U'))
DROP TABLE [dbo].[jobs]
GO
/****** Object:  Table [dbo].[first_recharge_logs]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[first_recharge_logs]') AND type in (N'U'))
DROP TABLE [dbo].[first_recharge_logs]
GO
/****** Object:  Table [dbo].[failed_jobs]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[failed_jobs]') AND type in (N'U'))
DROP TABLE [dbo].[failed_jobs]
GO
/****** Object:  Table [dbo].[error_logs]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[error_logs]') AND type in (N'U'))
DROP TABLE [dbo].[error_logs]
GO
/****** Object:  Table [dbo].[carousel_website]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[carousel_website]') AND type in (N'U'))
DROP TABLE [dbo].[carousel_website]
GO
/****** Object:  Table [dbo].[carousel_affiche]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[carousel_affiche]') AND type in (N'U'))
DROP TABLE [dbo].[carousel_affiche]
GO
/****** Object:  Table [dbo].[ads]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[ads]') AND type in (N'U'))
DROP TABLE [dbo].[ads]
GO
/****** Object:  Table [dbo].[admin_users]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[admin_users]') AND type in (N'U'))
DROP TABLE [dbo].[admin_users]
GO
/****** Object:  Table [dbo].[accounts_set]    Script Date: 2019/11/22 11:57:49 ******/
IF  EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[accounts_set]') AND type in (N'U'))
DROP TABLE [dbo].[accounts_set]
GO
/****** Object:  Table [dbo].[accounts_set]    Script Date: 2019/11/22 11:57:49 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[accounts_set]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[accounts_set](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[user_id] [bigint] NOT NULL,
	[nullity] [tinyint] NOT NULL,
	[transfer] [tinyint] NOT NULL,
	[withdraw] [tinyint] NOT NULL,
PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
END
GO
/****** Object:  Table [dbo].[admin_users]    Script Date: 2019/11/22 11:57:49 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[admin_users]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[admin_users](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[admin_id] [int] NOT NULL,
	[username] [nvarchar](255) NOT NULL,
	[email] [nvarchar](255) NOT NULL,
	[mobile] [nvarchar](11) NOT NULL,
	[sex] [smallint] NOT NULL,
	[password] [nvarchar](60) NOT NULL,
	[remember_token] [nvarchar](100) NULL,
	[avatar] [nvarchar](100) NULL,
	[introduction] [nvarchar](100) NULL,
	[google2fa_secret] [nvarchar](255) NULL,
	[created_at] [datetime] NULL,
	[updated_at] [datetime] NULL,
	[deleted_at] [datetime] NULL,
PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
END
GO
/****** Object:  Table [dbo].[ads]    Script Date: 2019/11/22 11:57:49 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[ads]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[ads](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[title] [nvarchar](200) NOT NULL,
	[resource_url] [nvarchar](500) NOT NULL,
	[link_url] [nvarchar](500) NOT NULL,
	[type] [tinyint] NOT NULL,
	[sort_id] [int] NOT NULL,
	[remark] [nvarchar](500) NOT NULL,
	[platform_type] [int] NOT NULL,
	[created_at] [datetime] NULL,
	[updated_at] [datetime] NULL,
PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
END
GO
/****** Object:  Table [dbo].[carousel_affiche]    Script Date: 2019/11/22 11:57:49 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[carousel_affiche]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[carousel_affiche](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[type] [nvarchar](255) NOT NULL,
	[sort] [tinyint] NOT NULL,
	[image] [nvarchar](255) NOT NULL,
	[link] [nvarchar](255) NOT NULL,
	[created_at] [datetime] NULL,
	[updated_at] [datetime] NULL,
PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
END
GO
/****** Object:  Table [dbo].[carousel_website]    Script Date: 2019/11/22 11:57:49 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[carousel_website]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[carousel_website](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[url] [nvarchar](255) NOT NULL,
	[created_at] [datetime] NULL,
	[updated_at] [datetime] NULL,
PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
END
GO
/****** Object:  Table [dbo].[error_logs]    Script Date: 2019/11/22 11:57:49 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[error_logs]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[error_logs](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[instance] [nvarchar](255) NOT NULL,
	[channel] [nvarchar](255) NOT NULL,
	[level] [nvarchar](255) NOT NULL,
	[level_name] [nvarchar](255) NOT NULL,
	[message] [nvarchar](max) NOT NULL,
	[context] [nvarchar](max) NOT NULL,
	[remote_addr] [nvarchar](255) NULL,
	[user_agent] [nvarchar](255) NULL,
	[created_by] [int] NULL,
	[created_at] [datetime] NOT NULL,
PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
END
GO
/****** Object:  Table [dbo].[failed_jobs]    Script Date: 2019/11/22 11:57:49 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[failed_jobs]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[failed_jobs](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[connection] [nvarchar](max) NOT NULL,
	[queue] [nvarchar](max) NOT NULL,
	[payload] [nvarchar](max) NOT NULL,
	[exception] [nvarchar](max) NOT NULL,
	[failed_at] [datetime] NOT NULL,
PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
END
GO
/****** Object:  Table [dbo].[first_recharge_logs]    Script Date: 2019/11/22 11:57:49 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[first_recharge_logs]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[first_recharge_logs](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[order_no] [nvarchar](255) NOT NULL,
	[user_id] [int] NOT NULL,
	[coins] [bigint] NOT NULL,
	[created_at] [datetime] NULL,
	[updated_at] [datetime] NULL,
PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
END
GO
/****** Object:  Table [dbo].[jobs]    Script Date: 2019/11/22 11:57:49 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[jobs]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[jobs](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[queue] [nvarchar](255) NOT NULL,
	[payload] [nvarchar](max) NOT NULL,
	[attempts] [tinyint] NOT NULL,
	[reserved_at] [int] NULL,
	[available_at] [int] NOT NULL,
	[created_at] [int] NOT NULL,
PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
END
GO
/****** Object:  Table [dbo].[login_logs]    Script Date: 2019/11/22 11:57:49 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[login_logs]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[login_logs](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[admin_id] [int] NOT NULL,
	[log_url] [nvarchar](128) NULL,
	[log_ip] [nvarchar](20) NULL,
	[created_at] [datetime] NULL,
	[updated_at] [datetime] NULL,
PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
END
GO
/****** Object:  Table [dbo].[migrations]    Script Date: 2019/11/22 11:57:49 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[migrations]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[migrations](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[migration] [nvarchar](255) NOT NULL,
	[batch] [int] NOT NULL,
PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
END
GO
/****** Object:  Table [dbo].[model_has_permissions]    Script Date: 2019/11/22 11:57:49 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[model_has_permissions]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[model_has_permissions](
	[permission_id] [int] NOT NULL,
	[model_type] [nvarchar](255) NOT NULL,
	[model_id] [bigint] NOT NULL,
 CONSTRAINT [model_has_permissions_permission_model_type_primary] PRIMARY KEY CLUSTERED
(
	[permission_id] ASC,
	[model_id] ASC,
	[model_type] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
END
GO
/****** Object:  Table [dbo].[model_has_roles]    Script Date: 2019/11/22 11:57:49 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[model_has_roles]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[model_has_roles](
	[role_id] [int] NOT NULL,
	[model_type] [nvarchar](255) NOT NULL,
	[model_id] [bigint] NOT NULL,
 CONSTRAINT [model_has_roles_role_model_type_primary] PRIMARY KEY CLUSTERED
(
	[role_id] ASC,
	[model_id] ASC,
	[model_type] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
END
GO
/****** Object:  Table [dbo].[order_logs]    Script Date: 2019/11/22 11:57:49 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[order_logs]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[order_logs](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[admin_id] [int] NOT NULL,
	[log_url] [nvarchar](128) NULL,
	[log_ip] [nvarchar](20) NULL,
	[order_type] [nvarchar](20) NULL,
	[order_no] [nvarchar](255) NULL,
	[log_info] [nvarchar](100) NULL,
	[created_at] [datetime] NULL,
	[updated_at] [datetime] NULL,
PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
END
GO
/****** Object:  Table [dbo].[payment_configs]    Script Date: 2019/11/22 11:57:49 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[payment_configs]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[payment_configs](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[key] [nvarchar](255) NOT NULL,
	[name] [nvarchar](255) NOT NULL,
	[status] [nvarchar](255) NOT NULL,
	[sort] [int] NOT NULL,
	[config] [nvarchar](max) NULL,
	[callback] [nvarchar](128) NULL,
	[created_at] [datetime] NULL,
	[updated_at] [datetime] NULL,
PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
END
GO
/****** Object:  Table [dbo].[payment_orders]    Script Date: 2019/11/22 11:57:49 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[payment_orders]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[payment_orders](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[order_no] [nvarchar](255) NOT NULL,
	[user_id] [bigint] NOT NULL,
	[game_id] [bigint] NOT NULL,
	[admin_id] [bigint] NOT NULL,
	[payment_provider_id] [int] NOT NULL,
	[payment_type] [nvarchar](255) NOT NULL,
	[third_order_no] [nvarchar](255) NULL,
	[payment_status] [nvarchar](255) NOT NULL,
	[amount] [decimal](8, 2) NOT NULL,
	[coins] [bigint] NOT NULL,
	[success_time] [datetime] NULL,
	[third_created_time] [datetime] NULL,
	[nickname] [nvarchar](255) NULL,
	[payment_provider_name] [nvarchar](255) NULL,
	[return_data] [nvarchar](max) NULL,
	[callback_data] [nvarchar](max) NULL,
	[created_at] [datetime] NULL,
	[updated_at] [datetime] NULL,
PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
END
GO
/****** Object:  Table [dbo].[payment_providers]    Script Date: 2019/11/22 11:57:49 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[payment_providers]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[payment_providers](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[payment_config_id] [int] NOT NULL,
	[pay_type] [nvarchar](255) NOT NULL,
	[provider_key] [nvarchar](255) NOT NULL,
	[provider_name] [nvarchar](255) NOT NULL,
	[weight] [int] NOT NULL,
	[pay_value] [nvarchar](255) NULL,
	[range] [nvarchar](255) NOT NULL,
	[min_value] [int] NULL,
	[max_value] [int] NULL,
	[rate] [decimal](7, 4) NOT NULL,
	[status] [nvarchar](255) NOT NULL,
	[created_at] [datetime] NULL,
	[updated_at] [datetime] NULL,
PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
END
GO
/****** Object:  Table [dbo].[permissions]    Script Date: 2019/11/22 11:57:49 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[permissions]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[permissions](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[name] [nvarchar](255) NOT NULL,
	[title] [nvarchar](255) NOT NULL,
	[group] [nvarchar](255) NOT NULL,
	[guard_name] [nvarchar](255) NOT NULL,
	[created_at] [datetime] NULL,
	[updated_at] [datetime] NULL,
PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
END
GO
/****** Object:  Table [dbo].[recharge_agents]    Script Date: 2019/11/22 11:57:49 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[recharge_agents]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[recharge_agents](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[name] [nvarchar](255) NOT NULL,
	[number] [nvarchar](255) NOT NULL,
	[number_type] [tinyint] NOT NULL,
	[nickname] [nvarchar](255) NOT NULL,
	[sort] [tinyint] NOT NULL,
	[state] [tinyint] NOT NULL,
	[created_at] [datetime] NULL,
	[updated_at] [datetime] NULL,
PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
END
GO
/****** Object:  Table [dbo].[recharge_alipays]    Script Date: 2019/11/22 11:57:49 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[recharge_alipays]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[recharge_alipays](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[name] [nvarchar](255) NOT NULL,
	[code_address] [nvarchar](255) NOT NULL,
	[nickname] [nvarchar](255) NOT NULL,
	[ratio] [decimal](6, 2) NULL,
	[sort] [tinyint] NOT NULL,
	[state] [tinyint] NOT NULL,
	[created_at] [datetime] NULL,
	[updated_at] [datetime] NULL,
PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
END
GO
/****** Object:  Table [dbo].[recharge_unions]    Script Date: 2019/11/22 11:57:49 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[recharge_unions]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[recharge_unions](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[name] [nvarchar](255) NOT NULL,
	[bank_id] [tinyint] NOT NULL,
	[payee] [nvarchar](255) NOT NULL,
	[card_number] [nvarchar](255) NOT NULL,
	[opening_bank] [nvarchar](255) NOT NULL,
	[ratio] [decimal](6, 2) NULL,
	[sort] [tinyint] NOT NULL,
	[state] [tinyint] NOT NULL,
	[created_at] [datetime] NULL,
	[updated_at] [datetime] NULL,
PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
END
GO
/****** Object:  Table [dbo].[recharge_wechats]    Script Date: 2019/11/22 11:57:49 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[recharge_wechats]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[recharge_wechats](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[name] [nvarchar](255) NOT NULL,
	[code_address] [nvarchar](255) NOT NULL,
	[nickname] [nvarchar](255) NOT NULL,
	[ratio] [decimal](6, 2) NULL,
	[sort] [tinyint] NOT NULL,
	[state] [tinyint] NOT NULL,
	[created_at] [datetime] NULL,
	[updated_at] [datetime] NULL,
PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
END
GO
/****** Object:  Table [dbo].[register_give]    Script Date: 2019/11/22 11:57:49 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[register_give]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[register_give](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[score_count] [int] NOT NULL,
	[give_type] [int] NOT NULL,
	[platform_type] [int] NOT NULL,
	[created_at] [datetime] NULL,
	[updated_at] [datetime] NULL,
PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
END
GO
/****** Object:  Table [dbo].[role_has_permissions]    Script Date: 2019/11/22 11:57:49 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[role_has_permissions]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[role_has_permissions](
	[permission_id] [int] NOT NULL,
	[role_id] [int] NOT NULL,
 CONSTRAINT [role_has_permissions_permission_id_role_id_primary] PRIMARY KEY CLUSTERED
(
	[permission_id] ASC,
	[role_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
END
GO
/****** Object:  Table [dbo].[roles]    Script Date: 2019/11/22 11:57:49 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[roles]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[roles](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[name] [nvarchar](255) NOT NULL,
	[title] [nvarchar](255) NOT NULL,
	[guard_name] [nvarchar](255) NOT NULL,
	[created_at] [datetime] NULL,
	[updated_at] [datetime] NULL,
PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
END
GO
/****** Object:  Table [dbo].[sms_logs]    Script Date: 2019/11/22 11:57:49 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[sms_logs]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[sms_logs](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[type] [tinyint] NOT NULL,
	[phone] [nvarchar](18) NOT NULL,
	[status] [nvarchar](255) NOT NULL,
	[result] [nvarchar](max) NULL,
	[created_at] [datetime] NULL,
	[updated_at] [datetime] NULL,
PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
END
GO
/****** Object:  Table [dbo].[statistics_game_data]    Script Date: 2019/11/22 11:57:49 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[statistics_game_data]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[statistics_game_data](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[statistics_time] [date] NOT NULL,
	[sum_changeScore] [bigint] NOT NULL,
	[sum_jettonScore] [bigint] NOT NULL,
	[sum_systemServiceScore] [bigint] NOT NULL,
	[sum_streamScore] [bigint] NOT NULL,
	[created_at] [datetime] NOT NULL,
PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
END
GO
/****** Object:  Table [dbo].[statistics_retentions]    Script Date: 2019/11/22 11:57:49 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[statistics_retentions]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[statistics_retentions](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[statistics_time] [date] NOT NULL,
	[type] [int] NOT NULL,
	[total] [int] NOT NULL,
	[created_at] [datetime] NOT NULL,
PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
END
GO
/****** Object:  Table [dbo].[statistics_win_lose]    Script Date: 2019/11/22 11:57:49 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[statistics_win_lose]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[statistics_win_lose](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[server_id] [int] NOT NULL,
	[kind_id] [int] NOT NULL,
	[change_score] [bigint] NOT NULL,
	[jetton_score] [bigint] NOT NULL,
	[system_score] [bigint] NOT NULL,
	[system_service_score] [bigint] NOT NULL,
	[create_time] [datetime] NOT NULL,
PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
END
GO
/****** Object:  Table [dbo].[system_logs]    Script Date: 2019/11/22 11:57:49 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[system_logs]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[system_logs](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[admin_id] [int] NOT NULL,
	[log_url] [nvarchar](128) NULL,
	[log_ip] [nvarchar](20) NULL,
	[log_info] [nvarchar](100) NULL,
	[created_at] [datetime] NULL,
	[updated_at] [datetime] NULL,
PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
END
GO
/****** Object:  Table [dbo].[system_settings]    Script Date: 2019/11/22 11:57:49 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[system_settings]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[system_settings](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[group] [nvarchar](100) NOT NULL,
	[key] [nvarchar](100) NOT NULL,
	[value] [nvarchar](100) NOT NULL,
	[lock] [nvarchar](255) NOT NULL,
	[created_at] [datetime] NULL,
	[updated_at] [datetime] NULL,
PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
END
GO
/****** Object:  Table [dbo].[system_sms_config]    Script Date: 2019/11/22 11:57:49 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[system_sms_config]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[system_sms_config](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[name] [nvarchar](255) NOT NULL,
	[alias] [nvarchar](255) NOT NULL,
	[config] [nvarchar](255) NOT NULL,
	[weight] [int] NOT NULL,
	[status] [tinyint] NOT NULL,
	[created_at] [datetime] NULL,
	[updated_at] [datetime] NULL,
PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
END
GO
/****** Object:  Table [dbo].[SystemNotice]    Script Date: 2019/11/22 11:57:49 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[SystemNotice]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[SystemNotice](
	[NoticeID] [int] IDENTITY(1,1) NOT NULL,
	[NoticeTitle] [nvarchar](50) NOT NULL,
	[MoblieContent] [nvarchar](1000) NOT NULL,
	[WebContent] [nvarchar](max) NOT NULL,
	[SortID] [int] NOT NULL,
	[Publisher] [nvarchar](32) NOT NULL,
	[PublisherTime] [datetime] NOT NULL,
	[IsHot] [tinyint] NOT NULL,
	[IsTop] [tinyint] NOT NULL,
	[Nullity] [tinyint] NOT NULL,
	[PlatformType] [int] NOT NULL,
PRIMARY KEY CLUSTERED
(
	[NoticeID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
END
GO
/****** Object:  Table [dbo].[versions]    Script Date: 2019/11/22 11:57:49 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[versions]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[versions](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[admin_id] [int] NOT NULL,
	[version_id] [int] NOT NULL,
	[version_description] [nvarchar](50) NOT NULL,
	[hot_update_url] [nvarchar](100) NOT NULL,
	[force_update_url] [nvarchar](100) NOT NULL,
	[status] [tinyint] NOT NULL,
	[created_at] [datetime] NULL,
	[updated_at] [datetime] NULL,
PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
END
GO
/****** Object:  Table [dbo].[vip_businessman]    Script Date: 2019/11/22 11:57:49 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[vip_businessman]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[vip_businessman](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[admin_id] [int] NOT NULL,
	[gold_coins] [bigint] NOT NULL,
	[sort_id] [int] NOT NULL,
	[avatar] [nvarchar](100) NULL,
	[contact_information] [nvarchar](255) NOT NULL,
	[type] [int] NOT NULL,
	[platform_type] [int] NOT NULL,
	[nullity] [tinyint] NOT NULL,
	[created_at] [datetime] NULL,
	[updated_at] [datetime] NULL,
	[nickname] [nvarchar](255) NULL,
	[user_id] [bigint] NULL,
PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
END
GO
/****** Object:  Table [dbo].[white_ip]    Script Date: 2019/11/22 11:57:49 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[white_ip]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[white_ip](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[ip] [nvarchar](255) NOT NULL,
	[nullity] [tinyint] NOT NULL,
	[created_at] [datetime] NULL,
	[updated_at] [datetime] NULL,
PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
END
GO
/****** Object:  Table [dbo].[withdrawal_automatic]    Script Date: 2019/11/22 11:57:49 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[withdrawal_automatic]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[withdrawal_automatic](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[order_id] [bigint] NOT NULL,
	[order_no] [nvarchar](255) NULL,
	[third_order_no] [nvarchar](255) NULL,
	[withdrawal_status] [int] NOT NULL,
	[lock_id] [bigint] NOT NULL,
	[created_at] [datetime] NULL,
	[updated_at] [datetime] NULL,
PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
END
GO
/****** Object:  Table [dbo].[withdrawal_orders]    Script Date: 2019/11/22 11:57:49 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[withdrawal_orders]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[withdrawal_orders](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[user_id] [bigint] NOT NULL,
	[game_id] [bigint] NOT NULL,
	[admin_id] [bigint] NOT NULL,
	[order_no] [nvarchar](255) NOT NULL,
	[card_no] [nvarchar](255) NOT NULL,
	[bank_info] [nvarchar](255) NULL,
	[payee] [nvarchar](255) NOT NULL,
	[phone] [nvarchar](255) NOT NULL,
	[gold_coins] [decimal](18, 2) NOT NULL,
	[real_gold_coins] [decimal](18, 2) NOT NULL,
	[status] [int] NOT NULL,
	[money] [decimal](18, 2) NULL,
	[real_money] [decimal](18, 2) NULL,
	[client_ip] [nvarchar](255) NULL,
	[payment_no] [nvarchar](255) NULL,
	[complete_time] [datetime] NULL,
	[remark] [nvarchar](max) NULL,
	[created_at] [datetime] NULL,
	[updated_at] [datetime] NULL,
	[withdrawal_type] [tinyint] NULL,
	[jetton_score] [bigint] NULL,
PRIMARY KEY CLUSTERED
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
END
GO
/****** Object:  Index [users_id_unique]    Script Date: 2019/11/22 11:57:49 ******/
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[accounts_set]') AND name = N'users_id_unique')
CREATE UNIQUE NONCLUSTERED INDEX [users_id_unique] ON [dbo].[accounts_set]
(
	[user_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
SET ANSI_PADDING ON

GO
/****** Object:  Index [users_email_unique]    Script Date: 2019/11/22 11:57:49 ******/
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[admin_users]') AND name = N'users_email_unique')
CREATE UNIQUE NONCLUSTERED INDEX [users_email_unique] ON [dbo].[admin_users]
(
	[email] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
SET ANSI_PADDING ON

GO
/****** Object:  Index [users_mobile_unique]    Script Date: 2019/11/22 11:57:49 ******/
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[admin_users]') AND name = N'users_mobile_unique')
CREATE UNIQUE NONCLUSTERED INDEX [users_mobile_unique] ON [dbo].[admin_users]
(
	[mobile] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
SET ANSI_PADDING ON

GO
/****** Object:  Index [error_logs_channel_index]    Script Date: 2019/11/22 11:57:49 ******/
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[error_logs]') AND name = N'error_logs_channel_index')
CREATE NONCLUSTERED INDEX [error_logs_channel_index] ON [dbo].[error_logs]
(
	[channel] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
/****** Object:  Index [error_logs_created_by_index]    Script Date: 2019/11/22 11:57:49 ******/
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[error_logs]') AND name = N'error_logs_created_by_index')
CREATE NONCLUSTERED INDEX [error_logs_created_by_index] ON [dbo].[error_logs]
(
	[created_by] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
SET ANSI_PADDING ON

GO
/****** Object:  Index [error_logs_instance_index]    Script Date: 2019/11/22 11:57:49 ******/
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[error_logs]') AND name = N'error_logs_instance_index')
CREATE NONCLUSTERED INDEX [error_logs_instance_index] ON [dbo].[error_logs]
(
	[instance] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
SET ANSI_PADDING ON

GO
/****** Object:  Index [error_logs_level_index]    Script Date: 2019/11/22 11:57:49 ******/
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[error_logs]') AND name = N'error_logs_level_index')
CREATE NONCLUSTERED INDEX [error_logs_level_index] ON [dbo].[error_logs]
(
	[level] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
SET ANSI_PADDING ON

GO
/****** Object:  Index [jobs_queue_index]    Script Date: 2019/11/22 11:57:49 ******/
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[jobs]') AND name = N'jobs_queue_index')
CREATE NONCLUSTERED INDEX [jobs_queue_index] ON [dbo].[jobs]
(
	[queue] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
SET ANSI_PADDING ON

GO
/****** Object:  Index [model_has_permissions_model_id_model_type_index]    Script Date: 2019/11/22 11:57:49 ******/
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[model_has_permissions]') AND name = N'model_has_permissions_model_id_model_type_index')
CREATE NONCLUSTERED INDEX [model_has_permissions_model_id_model_type_index] ON [dbo].[model_has_permissions]
(
	[model_id] ASC,
	[model_type] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
SET ANSI_PADDING ON

GO
/****** Object:  Index [model_has_roles_model_id_model_type_index]    Script Date: 2019/11/22 11:57:49 ******/
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[model_has_roles]') AND name = N'model_has_roles_model_id_model_type_index')
CREATE NONCLUSTERED INDEX [model_has_roles_model_id_model_type_index] ON [dbo].[model_has_roles]
(
	[model_id] ASC,
	[model_type] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
SET ANSI_PADDING ON

GO
/****** Object:  Index [payment_configs_key_unique]    Script Date: 2019/11/22 11:57:49 ******/
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[payment_configs]') AND name = N'payment_configs_key_unique')
CREATE UNIQUE NONCLUSTERED INDEX [payment_configs_key_unique] ON [dbo].[payment_configs]
(
	[key] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
/****** Object:  Index [admin_id_index]    Script Date: 2019/11/22 11:57:49 ******/
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[payment_orders]') AND name = N'admin_id_index')
CREATE NONCLUSTERED INDEX [admin_id_index] ON [dbo].[payment_orders]
(
	[admin_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
/****** Object:  Index [game_id_index]    Script Date: 2019/11/22 11:57:49 ******/
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[payment_orders]') AND name = N'game_id_index')
CREATE NONCLUSTERED INDEX [game_id_index] ON [dbo].[payment_orders]
(
	[game_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
SET ANSI_PADDING ON

GO
/****** Object:  Index [order_no_unique]    Script Date: 2019/11/22 11:57:49 ******/
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[payment_orders]') AND name = N'order_no_unique')
CREATE UNIQUE NONCLUSTERED INDEX [order_no_unique] ON [dbo].[payment_orders]
(
	[order_no] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
/****** Object:  Index [payment_provider_id_index]    Script Date: 2019/11/22 11:57:49 ******/
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[payment_orders]') AND name = N'payment_provider_id_index')
CREATE NONCLUSTERED INDEX [payment_provider_id_index] ON [dbo].[payment_orders]
(
	[payment_provider_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
SET ANSI_PADDING ON

GO
/****** Object:  Index [third_order_no_index]    Script Date: 2019/11/22 11:57:49 ******/
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[payment_orders]') AND name = N'third_order_no_index')
CREATE NONCLUSTERED INDEX [third_order_no_index] ON [dbo].[payment_orders]
(
	[third_order_no] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
/****** Object:  Index [user_id_index]    Script Date: 2019/11/22 11:57:49 ******/
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[payment_orders]') AND name = N'user_id_index')
CREATE NONCLUSTERED INDEX [user_id_index] ON [dbo].[payment_orders]
(
	[user_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
SET ANSI_PADDING ON

GO
/****** Object:  Index [payment_providers_pay_type_index]    Script Date: 2019/11/22 11:57:49 ******/
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[payment_providers]') AND name = N'payment_providers_pay_type_index')
CREATE NONCLUSTERED INDEX [payment_providers_pay_type_index] ON [dbo].[payment_providers]
(
	[pay_type] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
SET ANSI_PADDING ON

GO
/****** Object:  Index [payment_providers_provider_key_unique]    Script Date: 2019/11/22 11:57:49 ******/
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[payment_providers]') AND name = N'payment_providers_provider_key_unique')
CREATE UNIQUE NONCLUSTERED INDEX [payment_providers_provider_key_unique] ON [dbo].[payment_providers]
(
	[provider_key] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
SET ANSI_PADDING ON

GO
/****** Object:  Index [permissions_group]    Script Date: 2019/11/22 11:57:49 ******/
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[permissions]') AND name = N'permissions_group')
CREATE NONCLUSTERED INDEX [permissions_group] ON [dbo].[permissions]
(
	[group] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
/****** Object:  Index [group_key_unique]    Script Date: 2019/11/22 11:57:49 ******/
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[register_give]') AND name = N'group_key_unique')
CREATE UNIQUE NONCLUSTERED INDEX [group_key_unique] ON [dbo].[register_give]
(
	[give_type] ASC,
	[platform_type] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
/****** Object:  Index [statistics_retentions_statistics_time_index]    Script Date: 2019/11/22 11:57:49 ******/
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[statistics_retentions]') AND name = N'statistics_retentions_statistics_time_index')
CREATE NONCLUSTERED INDEX [statistics_retentions_statistics_time_index] ON [dbo].[statistics_retentions]
(
	[statistics_time] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
/****** Object:  Index [statistics_retentions_type_index]    Script Date: 2019/11/22 11:57:49 ******/
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[statistics_retentions]') AND name = N'statistics_retentions_type_index')
CREATE NONCLUSTERED INDEX [statistics_retentions_type_index] ON [dbo].[statistics_retentions]
(
	[type] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
SET ANSI_PADDING ON

GO
/****** Object:  Index [group_key_unique]    Script Date: 2019/11/22 11:57:49 ******/
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[system_settings]') AND name = N'group_key_unique')
CREATE UNIQUE NONCLUSTERED INDEX [group_key_unique] ON [dbo].[system_settings]
(
	[group] ASC,
	[key] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
/****** Object:  Index [version_admin_id_index]    Script Date: 2019/11/22 11:57:49 ******/
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[versions]') AND name = N'version_admin_id_index')
CREATE NONCLUSTERED INDEX [version_admin_id_index] ON [dbo].[versions]
(
	[admin_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
/****** Object:  Index [version_id_unique]    Script Date: 2019/11/22 11:57:49 ******/
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[versions]') AND name = N'version_id_unique')
CREATE UNIQUE NONCLUSTERED INDEX [version_id_unique] ON [dbo].[versions]
(
	[version_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
/****** Object:  Index [vip_businessman_admin_id_user_id_index]    Script Date: 2019/11/22 11:57:49 ******/
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[vip_businessman]') AND name = N'vip_businessman_admin_id_user_id_index')
CREATE NONCLUSTERED INDEX [vip_businessman_admin_id_user_id_index] ON [dbo].[vip_businessman]
(
	[admin_id] ASC,
	[user_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
SET ANSI_PADDING ON

GO
/****** Object:  Index [ip_unique]    Script Date: 2019/11/22 11:57:49 ******/
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[white_ip]') AND name = N'ip_unique')
CREATE UNIQUE NONCLUSTERED INDEX [ip_unique] ON [dbo].[white_ip]
(
	[ip] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
SET ANSI_PADDING ON

GO
/****** Object:  Index [order_no_index]    Script Date: 2019/11/22 11:57:49 ******/
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[withdrawal_automatic]') AND name = N'order_no_index')
CREATE NONCLUSTERED INDEX [order_no_index] ON [dbo].[withdrawal_automatic]
(
	[order_no] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
SET ANSI_PADDING ON

GO
/****** Object:  Index [third_order_no_index]    Script Date: 2019/11/22 11:57:49 ******/
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[withdrawal_automatic]') AND name = N'third_order_no_index')
CREATE NONCLUSTERED INDEX [third_order_no_index] ON [dbo].[withdrawal_automatic]
(
	[third_order_no] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
/****** Object:  Index [withdrawal_lock_id_index]    Script Date: 2019/11/22 11:57:49 ******/
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[withdrawal_automatic]') AND name = N'withdrawal_lock_id_index')
CREATE NONCLUSTERED INDEX [withdrawal_lock_id_index] ON [dbo].[withdrawal_automatic]
(
	[lock_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
/****** Object:  Index [withdrawal_order_id]    Script Date: 2019/11/22 11:57:49 ******/
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[withdrawal_automatic]') AND name = N'withdrawal_order_id')
CREATE UNIQUE NONCLUSTERED INDEX [withdrawal_order_id] ON [dbo].[withdrawal_automatic]
(
	[order_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
/****** Object:  Index [withdrawal_status_index]    Script Date: 2019/11/22 11:57:49 ******/
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[withdrawal_automatic]') AND name = N'withdrawal_status_index')
CREATE NONCLUSTERED INDEX [withdrawal_status_index] ON [dbo].[withdrawal_automatic]
(
	[withdrawal_status] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
/****** Object:  Index [game_id_index]    Script Date: 2019/11/22 11:57:49 ******/
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[withdrawal_orders]') AND name = N'game_id_index')
CREATE NONCLUSTERED INDEX [game_id_index] ON [dbo].[withdrawal_orders]
(
	[game_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
/****** Object:  Index [withdrawal_admin_id_index]    Script Date: 2019/11/22 11:57:49 ******/
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[withdrawal_orders]') AND name = N'withdrawal_admin_id_index')
CREATE NONCLUSTERED INDEX [withdrawal_admin_id_index] ON [dbo].[withdrawal_orders]
(
	[admin_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
SET ANSI_PADDING ON

GO
/****** Object:  Index [withdrawal_card_no_index]    Script Date: 2019/11/22 11:57:49 ******/
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[withdrawal_orders]') AND name = N'withdrawal_card_no_index')
CREATE NONCLUSTERED INDEX [withdrawal_card_no_index] ON [dbo].[withdrawal_orders]
(
	[card_no] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
/****** Object:  Index [withdrawal_gold_coins_index]    Script Date: 2019/11/22 11:57:49 ******/
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[withdrawal_orders]') AND name = N'withdrawal_gold_coins_index')
CREATE NONCLUSTERED INDEX [withdrawal_gold_coins_index] ON [dbo].[withdrawal_orders]
(
	[gold_coins] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
SET ANSI_PADDING ON

GO
/****** Object:  Index [withdrawal_order_no]    Script Date: 2019/11/22 11:57:49 ******/
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[withdrawal_orders]') AND name = N'withdrawal_order_no')
CREATE UNIQUE NONCLUSTERED INDEX [withdrawal_order_no] ON [dbo].[withdrawal_orders]
(
	[order_no] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
/****** Object:  Index [withdrawal_orders_withdrawal_type]    Script Date: 2019/11/22 11:57:49 ******/
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[withdrawal_orders]') AND name = N'withdrawal_orders_withdrawal_type')
CREATE NONCLUSTERED INDEX [withdrawal_orders_withdrawal_type] ON [dbo].[withdrawal_orders]
(
	[withdrawal_type] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
SET ANSI_PADDING ON

GO
/****** Object:  Index [withdrawal_phone_index]    Script Date: 2019/11/22 11:57:49 ******/
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[withdrawal_orders]') AND name = N'withdrawal_phone_index')
CREATE NONCLUSTERED INDEX [withdrawal_phone_index] ON [dbo].[withdrawal_orders]
(
	[phone] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
/****** Object:  Index [withdrawal_real_gold_coins_index]    Script Date: 2019/11/22 11:57:49 ******/
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[withdrawal_orders]') AND name = N'withdrawal_real_gold_coins_index')
CREATE NONCLUSTERED INDEX [withdrawal_real_gold_coins_index] ON [dbo].[withdrawal_orders]
(
	[real_gold_coins] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
/****** Object:  Index [withdrawal_status_index]    Script Date: 2019/11/22 11:57:49 ******/
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[withdrawal_orders]') AND name = N'withdrawal_status_index')
CREATE NONCLUSTERED INDEX [withdrawal_status_index] ON [dbo].[withdrawal_orders]
(
	[status] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__accounts___nulli__239E4DCF]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[accounts_set] ADD  DEFAULT ('0') FOR [nullity]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__accounts___trans__24927208]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[accounts_set] ADD  DEFAULT ('0') FOR [transfer]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__accounts___withd__25869641]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[accounts_set] ADD  DEFAULT ('0') FOR [withdraw]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__admin_use__admin__15502E78]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[admin_users] ADD  DEFAULT ('0') FOR [admin_id]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__admin_users__sex__164452B1]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[admin_users] ADD  DEFAULT ('1') FOR [sex]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__ads__title__70DDC3D8]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[ads] ADD  DEFAULT ('') FOR [title]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__ads__resource_ur__71D1E811]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[ads] ADD  DEFAULT ('') FOR [resource_url]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__ads__link_url__72C60C4A]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[ads] ADD  DEFAULT ('') FOR [link_url]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__ads__type__73BA3083]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[ads] ADD  DEFAULT ('0') FOR [type]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__ads__remark__74AE54BC]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[ads] ADD  DEFAULT ('') FOR [remark]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__carousel_a__type__09A971A2]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[carousel_affiche] ADD  DEFAULT ('1') FOR [type]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__carousel_a__sort__0A9D95DB]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[carousel_affiche] ADD  DEFAULT ('255') FOR [sort]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__carousel___image__0B91BA14]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[carousel_affiche] ADD  DEFAULT ('') FOR [image]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__carousel_a__link__0C85DE4D]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[carousel_affiche] ADD  DEFAULT ('') FOR [link]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__carousel_we__url__0F624AF8]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[carousel_website] ADD  DEFAULT ('') FOR [url]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__failed_jo__faile__1CBC4616]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[failed_jobs] ADD  DEFAULT (getdate()) FOR [failed_at]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__login_log__admin__778AC167]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[login_logs] ADD  DEFAULT ('0') FOR [admin_id]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__payment_c__statu__47DBAE45]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[payment_configs] ADD  DEFAULT ('OFF') FOR [status]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__payment_co__sort__48CFD27E]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[payment_configs] ADD  DEFAULT ('0') FOR [sort]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__payment_o__admin__2C3393D0]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[payment_orders] ADD  DEFAULT ('0') FOR [admin_id]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__payment_o__payme__2E1BDC42]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[payment_orders] ADD  DEFAULT ('WAIT') FOR [payment_status]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__payment_p__weigh__3D5E1FD2]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[payment_providers] ADD  DEFAULT ('0') FOR [weight]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__payment_p__range__3F466844]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[payment_providers] ADD  DEFAULT ('OFF') FOR [range]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__payment_p__min_v__403A8C7D]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[payment_providers] ADD  DEFAULT ('0') FOR [min_value]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__payment_p__max_v__412EB0B6]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[payment_providers] ADD  DEFAULT ('0') FOR [max_value]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__payment_pr__rate__4222D4EF]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[payment_providers] ADD  DEFAULT ('0') FOR [rate]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__payment_p__statu__440B1D61]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[payment_providers] ADD  DEFAULT ('OFF') FOR [status]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__recharge_a__sort__4BAC3F29]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[recharge_agents] ADD  DEFAULT ('0') FOR [sort]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__recharge___state__4CA06362]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[recharge_agents] ADD  DEFAULT ('1') FOR [state]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__recharge_a__sort__534D60F1]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[recharge_alipays] ADD  DEFAULT ('0') FOR [sort]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__recharge___state__5441852A]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[recharge_alipays] ADD  DEFAULT ('1') FOR [state]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__recharge_u__sort__571DF1D5]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[recharge_unions] ADD  DEFAULT ('0') FOR [sort]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__recharge___state__5812160E]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[recharge_unions] ADD  DEFAULT ('1') FOR [state]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__recharge_w__sort__4F7CD00D]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[recharge_wechats] ADD  DEFAULT ('0') FOR [sort]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__recharge___state__5070F446]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[recharge_wechats] ADD  DEFAULT ('1') FOR [state]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__register___score__6C190EBB]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[register_give] ADD  DEFAULT ('0') FOR [score_count]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__register___give___6D0D32F4]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[register_give] ADD  DEFAULT ('0') FOR [give_type]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__register___platf__6E01572D]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[register_give] ADD  DEFAULT ('0') FOR [platform_type]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__sms_logs__type__34C8D9D1]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[sms_logs] ADD  DEFAULT ('1') FOR [type]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__sms_logs__status__36B12243]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[sms_logs] ADD  DEFAULT ('SUCCESS') FOR [status]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__statistic__creat__151B244E]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[statistics_game_data] ADD  DEFAULT (getdate()) FOR [created_at]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__statistic__total__5AEE82B9]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[statistics_retentions] ADD  DEFAULT ('0') FOR [total]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__statistic__creat__5BE2A6F2]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[statistics_retentions] ADD  DEFAULT (getdate()) FOR [created_at]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__statistic__chang__1B0907CE]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[statistics_win_lose] ADD  DEFAULT ('0') FOR [change_score]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__statistic__jetto__1BFD2C07]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[statistics_win_lose] ADD  DEFAULT ('0') FOR [jetton_score]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__statistic__syste__1CF15040]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[statistics_win_lose] ADD  DEFAULT ('0') FOR [system_score]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__statistic__syste__1DE57479]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[statistics_win_lose] ADD  DEFAULT ('0') FOR [system_service_score]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__system_set__lock__29572725]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[system_settings] ADD  DEFAULT ('UNLOCKED') FOR [lock]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__SystemNot__Notic__7F2BE32F]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[SystemNotice] ADD  DEFAULT ('') FOR [NoticeTitle]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__SystemNot__Mobli__00200768]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[SystemNotice] ADD  DEFAULT ('') FOR [MoblieContent]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__SystemNot__WebCo__01142BA1]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[SystemNotice] ADD  DEFAULT ('') FOR [WebContent]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__SystemNot__Publi__02084FDA]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[SystemNotice] ADD  DEFAULT ('') FOR [Publisher]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__SystemNot__Publi__02FC7413]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[SystemNotice] ADD  DEFAULT (getdate()) FOR [PublisherTime]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__SystemNot__IsHot__03F0984C]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[SystemNotice] ADD  DEFAULT ('0') FOR [IsHot]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__SystemNot__IsTop__04E4BC85]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[SystemNotice] ADD  DEFAULT ('0') FOR [IsTop]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__SystemNot__Nulli__05D8E0BE]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[SystemNotice] ADD  DEFAULT ('0') FOR [Nullity]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__SystemNot__Platf__06CD04F7]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[SystemNotice] ADD  DEFAULT ('1') FOR [PlatformType]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__versions__status__20C1E124]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[versions] ADD  DEFAULT ('1') FOR [status]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__vip_busine__type__7A672E12]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[vip_businessman] ADD  DEFAULT ('1') FOR [type]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__vip_busin__platf__7B5B524B]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[vip_businessman] ADD  DEFAULT ('0') FOR [platform_type]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__vip_busin__nulli__7C4F7684]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[vip_businessman] ADD  DEFAULT ('0') FOR [nullity]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__white_ip__nullit__123EB7A3]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[white_ip] ADD  DEFAULT ('0') FOR [nullity]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__withdrawa__lock___19DFD96B]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[withdrawal_automatic] ADD  DEFAULT ('0') FOR [lock_id]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__withdrawa__admin__30F848ED]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[withdrawal_orders] ADD  DEFAULT ('0') FOR [admin_id]
END

GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[DF__withdrawa__statu__31EC6D26]') AND type = 'D')
BEGIN
ALTER TABLE [dbo].[withdrawal_orders] ADD  DEFAULT ('0') FOR [status]
END

GO
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE object_id = OBJECT_ID(N'[dbo].[model_has_permissions_permission_id_foreign]') AND parent_object_id = OBJECT_ID(N'[dbo].[model_has_permissions]'))
ALTER TABLE [dbo].[model_has_permissions]  WITH CHECK ADD  CONSTRAINT [model_has_permissions_permission_id_foreign] FOREIGN KEY([permission_id])
REFERENCES [dbo].[permissions] ([id])
ON DELETE CASCADE
GO
IF  EXISTS (SELECT * FROM sys.foreign_keys WHERE object_id = OBJECT_ID(N'[dbo].[model_has_permissions_permission_id_foreign]') AND parent_object_id = OBJECT_ID(N'[dbo].[model_has_permissions]'))
ALTER TABLE [dbo].[model_has_permissions] CHECK CONSTRAINT [model_has_permissions_permission_id_foreign]
GO
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE object_id = OBJECT_ID(N'[dbo].[model_has_roles_role_id_foreign]') AND parent_object_id = OBJECT_ID(N'[dbo].[model_has_roles]'))
ALTER TABLE [dbo].[model_has_roles]  WITH CHECK ADD  CONSTRAINT [model_has_roles_role_id_foreign] FOREIGN KEY([role_id])
REFERENCES [dbo].[roles] ([id])
ON DELETE CASCADE
GO
IF  EXISTS (SELECT * FROM sys.foreign_keys WHERE object_id = OBJECT_ID(N'[dbo].[model_has_roles_role_id_foreign]') AND parent_object_id = OBJECT_ID(N'[dbo].[model_has_roles]'))
ALTER TABLE [dbo].[model_has_roles] CHECK CONSTRAINT [model_has_roles_role_id_foreign]
GO
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE object_id = OBJECT_ID(N'[dbo].[role_has_permissions_permission_id_foreign]') AND parent_object_id = OBJECT_ID(N'[dbo].[role_has_permissions]'))
ALTER TABLE [dbo].[role_has_permissions]  WITH CHECK ADD  CONSTRAINT [role_has_permissions_permission_id_foreign] FOREIGN KEY([permission_id])
REFERENCES [dbo].[permissions] ([id])
ON DELETE CASCADE
GO
IF  EXISTS (SELECT * FROM sys.foreign_keys WHERE object_id = OBJECT_ID(N'[dbo].[role_has_permissions_permission_id_foreign]') AND parent_object_id = OBJECT_ID(N'[dbo].[role_has_permissions]'))
ALTER TABLE [dbo].[role_has_permissions] CHECK CONSTRAINT [role_has_permissions_permission_id_foreign]
GO
IF NOT EXISTS (SELECT * FROM sys.foreign_keys WHERE object_id = OBJECT_ID(N'[dbo].[role_has_permissions_role_id_foreign]') AND parent_object_id = OBJECT_ID(N'[dbo].[role_has_permissions]'))
ALTER TABLE [dbo].[role_has_permissions]  WITH CHECK ADD  CONSTRAINT [role_has_permissions_role_id_foreign] FOREIGN KEY([role_id])
REFERENCES [dbo].[roles] ([id])
ON DELETE CASCADE
GO
IF  EXISTS (SELECT * FROM sys.foreign_keys WHERE object_id = OBJECT_ID(N'[dbo].[role_has_permissions_role_id_foreign]') AND parent_object_id = OBJECT_ID(N'[dbo].[role_has_permissions]'))
ALTER TABLE [dbo].[role_has_permissions] CHECK CONSTRAINT [role_has_permissions_role_id_foreign]
GO
IF NOT EXISTS (SELECT * FROM sys.check_constraints WHERE object_id = OBJECT_ID(N'[dbo].[CK__payment_c__statu__46E78A0C]') AND parent_object_id = OBJECT_ID(N'[dbo].[payment_configs]'))
ALTER TABLE [dbo].[payment_configs]  WITH CHECK ADD CHECK  (([status]=N'OFF' OR [status]=N'ON'))
GO
IF NOT EXISTS (SELECT * FROM sys.check_constraints WHERE object_id = OBJECT_ID(N'[dbo].[CK__payment_o__payme__2D27B809]') AND parent_object_id = OBJECT_ID(N'[dbo].[payment_orders]'))
ALTER TABLE [dbo].[payment_orders]  WITH CHECK ADD CHECK  (([payment_status]=N'FAIL' OR [payment_status]=N'SUCCESS' OR [payment_status]=N'WAIT'))
GO
IF NOT EXISTS (SELECT * FROM sys.check_constraints WHERE object_id = OBJECT_ID(N'[dbo].[CK__payment_p__range__3E52440B]') AND parent_object_id = OBJECT_ID(N'[dbo].[payment_providers]'))
ALTER TABLE [dbo].[payment_providers]  WITH CHECK ADD CHECK  (([range]=N'OFF' OR [range]=N'ON'))
GO
IF NOT EXISTS (SELECT * FROM sys.check_constraints WHERE object_id = OBJECT_ID(N'[dbo].[CK__payment_p__statu__4316F928]') AND parent_object_id = OBJECT_ID(N'[dbo].[payment_providers]'))
ALTER TABLE [dbo].[payment_providers]  WITH CHECK ADD CHECK  (([status]=N'OFF' OR [status]=N'ON'))
GO
IF NOT EXISTS (SELECT * FROM sys.check_constraints WHERE object_id = OBJECT_ID(N'[dbo].[CK__sms_logs__status__35BCFE0A]') AND parent_object_id = OBJECT_ID(N'[dbo].[sms_logs]'))
ALTER TABLE [dbo].[sms_logs]  WITH CHECK ADD CHECK  (([status]=N'SUCCESS' OR [status]=N'FAIL'))
GO
IF NOT EXISTS (SELECT * FROM sys.check_constraints WHERE object_id = OBJECT_ID(N'[dbo].[CK__system_set__lock__286302EC]') AND parent_object_id = OBJECT_ID(N'[dbo].[system_settings]'))
ALTER TABLE [dbo].[system_settings]  WITH CHECK ADD CHECK  (([lock]=N'UNLOCKED' OR [lock]=N'LOCKED'))
GO
