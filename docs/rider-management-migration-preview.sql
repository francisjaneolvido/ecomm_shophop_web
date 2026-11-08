create table `rider_lifecycle_profiles` (`id` bigint unsigned not null auto_increment primary key, `rider_id` bigint unsigned not null, `preferred_branch_id` bigint unsigned null, `application_status` varchar(32) not null default 'pending_review', `operational_status` varchar(32) not null default 'pending_activation', `availability_state` varchar(16) not null default 'offline', `legacy_onboarding` tinyint(1) not null default '0', `preferred_coverage` json not null, `accepted_coverage` json null, `assignment_confirmation_required` tinyint(1) not null default '0', `document_request` json null, `activated_at` timestamp null, `deactivated_at` timestamp null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';

alter table `rider_lifecycle_profiles` add constraint `rider_lifecycle_profiles_rider_id_foreign` foreign key (`rider_id`) references `riders` (`id`) on delete restrict;

alter table `rider_lifecycle_profiles` add constraint `rider_lifecycle_profiles_preferred_branch_id_foreign` foreign key (`preferred_branch_id`) references `sorting_centers` (`id`) on delete restrict;

alter table `rider_lifecycle_profiles` add index `rmp_states_idx`(`application_status`, `operational_status`);

alter table `rider_lifecycle_profiles` add unique `rider_lifecycle_profiles_rider_id_unique`(`rider_id`);

create table `rider_barangay_capacities` (`id` bigint unsigned not null auto_increment primary key, `logistics_partner_id` bigint unsigned not null, `location_key` varchar(64) not null, `location` json not null, `rider_limit` int unsigned not null default '6', `updated_by_type` varchar(12) not null, `updated_by_id` bigint unsigned not null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';

alter table `rider_barangay_capacities` add constraint `rider_barangay_capacities_logistics_partner_id_foreign` foreign key (`logistics_partner_id`) references `logistics_partners` (`id`) on delete restrict;

alter table `rider_barangay_capacities` add unique `rmp_capacity_location_unique`(`logistics_partner_id`, `location_key`);

create table `rider_capacity_overrides` (`id` bigint unsigned not null auto_increment primary key, `logistics_partner_id` bigint unsigned not null, `rider_id` bigint unsigned not null, `location_key` varchar(64) not null, `reason` text not null, `active` tinyint(1) not null default '1', `approved_by_type` varchar(12) not null, `approved_by_id` bigint unsigned not null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';

alter table `rider_capacity_overrides` add constraint `rider_capacity_overrides_logistics_partner_id_foreign` foreign key (`logistics_partner_id`) references `logistics_partners` (`id`) on delete restrict;

alter table `rider_capacity_overrides` add constraint `rider_capacity_overrides_rider_id_foreign` foreign key (`rider_id`) references `riders` (`id`) on delete restrict;

alter table `rider_capacity_overrides` add unique `rmp_override_rider_location_unique`(`rider_id`, `location_key`);

create table `rider_transfer_requests` (`id` bigint unsigned not null auto_increment primary key, `logistics_partner_id` bigint unsigned not null, `rider_id` bigint unsigned not null, `pending_rider_id` bigint unsigned null, `from_branch_id` bigint unsigned not null, `to_branch_id` bigint unsigned not null, `status` varchar(16) not null default 'pending', `reason` text not null, `decision_reason` text null, `requested_by_type` varchar(12) not null, `requested_by_id` bigint unsigned not null, `decided_by_type` varchar(12) null, `decided_by_id` bigint unsigned null, `decided_at` timestamp null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';

alter table `rider_transfer_requests` add constraint `rider_transfer_requests_logistics_partner_id_foreign` foreign key (`logistics_partner_id`) references `logistics_partners` (`id`) on delete restrict;

alter table `rider_transfer_requests` add constraint `rider_transfer_requests_rider_id_foreign` foreign key (`rider_id`) references `riders` (`id`) on delete restrict;

alter table `rider_transfer_requests` add constraint `rider_transfer_requests_pending_rider_id_foreign` foreign key (`pending_rider_id`) references `riders` (`id`) on delete restrict;

alter table `rider_transfer_requests` add constraint `rider_transfer_requests_from_branch_id_foreign` foreign key (`from_branch_id`) references `sorting_centers` (`id`) on delete restrict;

alter table `rider_transfer_requests` add constraint `rider_transfer_requests_to_branch_id_foreign` foreign key (`to_branch_id`) references `sorting_centers` (`id`) on delete restrict;

alter table `rider_transfer_requests` add index `rmp_transfer_company_state_idx`(`logistics_partner_id`, `status`);

alter table `rider_transfer_requests` add unique `rmp_transfer_pending_unique`(`pending_rider_id`);

create table `rider_work_schedules` (`id` bigint unsigned not null auto_increment primary key, `logistics_partner_id` bigint unsigned not null, `sorting_center_id` bigint unsigned not null, `rider_id` bigint unsigned not null, `starts_at` datetime not null, `ends_at` datetime not null, `status` varchar(16) not null default 'active', `created_by_type` varchar(12) not null, `created_by_id` bigint unsigned not null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';

alter table `rider_work_schedules` add constraint `rider_work_schedules_logistics_partner_id_foreign` foreign key (`logistics_partner_id`) references `logistics_partners` (`id`) on delete restrict;

alter table `rider_work_schedules` add constraint `rider_work_schedules_sorting_center_id_foreign` foreign key (`sorting_center_id`) references `sorting_centers` (`id`) on delete restrict;

alter table `rider_work_schedules` add constraint `rider_work_schedules_rider_id_foreign` foreign key (`rider_id`) references `riders` (`id`) on delete restrict;

alter table `rider_work_schedules` add index `rmp_schedule_rider_state_idx`(`rider_id`, `status`, `starts_at`);

create table `rider_management_events` (`id` bigint unsigned not null auto_increment primary key, `logistics_partner_id` bigint unsigned not null, `sorting_center_id` bigint unsigned null, `rider_id` bigint unsigned not null, `event` varchar(64) not null, `actor_type` varchar(12) not null, `actor_id` bigint unsigned not null, `details` json not null, `created_at` timestamp not null default CURRENT_TIMESTAMP) default character set utf8mb4 collate 'utf8mb4_unicode_ci';

alter table `rider_management_events` add constraint `rider_management_events_logistics_partner_id_foreign` foreign key (`logistics_partner_id`) references `logistics_partners` (`id`) on delete restrict;

alter table `rider_management_events` add constraint `rider_management_events_sorting_center_id_foreign` foreign key (`sorting_center_id`) references `sorting_centers` (`id`) on delete restrict;

alter table `rider_management_events` add constraint `rider_management_events_rider_id_foreign` foreign key (`rider_id`) references `riders` (`id`) on delete restrict;

alter table `rider_management_events` add index `rmp_event_company_rider_idx`(`logistics_partner_id`, `rider_id`, `id`);
