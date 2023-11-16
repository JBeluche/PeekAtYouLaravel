
   INFO  Preparing database.  

  Creating migration table ................................................................................. 19ms DONE

   INFO  Running migrations.  

  2014_10_12_000000_create_users_table ...............................................................................  
  ⇂ create table `users` (`id` bigint unsigned not null auto_increment primary key, `name` varchar(255) not null, `email` varchar(255) not null, `email_verified_at` timestamp null, `password` varchar(255) not null, `remember_token` varchar(100) null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci'  
  ⇂ alter table `users` add unique `users_email_unique`(`email`)  
  2014_10_12_100000_create_password_resets_table .....................................................................  
  ⇂ create table `password_resets` (`email` varchar(255) not null, `token` varchar(255) not null, `created_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci'  
  ⇂ alter table `password_resets` add index `password_resets_email_index`(`email`)  
  2019_08_19_000000_create_failed_jobs_table .........................................................................  
  ⇂ create table `failed_jobs` (`id` bigint unsigned not null auto_increment primary key, `uuid` varchar(255) not null, `connection` text not null, `queue` text not null, `payload` longtext not null, `exception` longtext not null, `failed_at` timestamp default CURRENT_TIMESTAMP not null) default character set utf8mb4 collate 'utf8mb4_unicode_ci'  
  ⇂ alter table `failed_jobs` add unique `failed_jobs_uuid_unique`(`uuid`)  
  2019_12_14_000001_create_personal_access_tokens_table ..............................................................  
  ⇂ create table `personal_access_tokens` (`id` bigint unsigned not null auto_increment primary key, `tokenable_type` varchar(255) not null, `tokenable_id` bigint unsigned not null, `name` varchar(255) not null, `token` varchar(64) not null, `abilities` text null, `last_used_at` timestamp null, `expires_at` timestamp null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci'  
  ⇂ alter table `personal_access_tokens` add index `personal_access_tokens_tokenable_type_tokenable_id_index`(`tokenable_type`, `tokenable_id`)  
  ⇂ alter table `personal_access_tokens` add unique `personal_access_tokens_token_unique`(`token`)  


  2022_11_01_143254_create_calendars_table ...........................................................................  
  ⇂ create table `calendars` (`id` bigint unsigned not null auto_increment primary key, `name` varchar(255) not null, `is_bullet_calendar` tinyint(1) not null, `user_id` bigint unsigned not null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci'  
  ⇂ alter table `calendars` add constraint `calendars_user_id_foreign` foreign key (`user_id`) references `users` (`id`) on delete cascade  


  2022_11_03_063210_create_color_associations ........................................................................  
  ⇂ create table `color_associations` (`id` bigint unsigned not null auto_increment primary key, `association_text` varchar(255) not null, `color_hex_value` varchar(6) not null, `calendar_id` bigint unsigned not null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci'  
  ⇂ alter table `color_associations` add constraint `color_associations_calendar_id_foreign` foreign key (`calendar_id`) references `calendars` (`id`) on delete cascade  

  
  2022_11_03_074137_create_dates_table ...............................................................................  
  ⇂ create table `dates` (`id` bigint unsigned not null auto_increment primary key, `long_note` varchar(255) null, `displayed_note` varchar(42) null, `date` date not null, `extra_value` tinyint null, `calendar_id` bigint unsigned not null, `color_association_id` bigint unsigned not null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci'  
  ⇂ alter table `dates` add constraint `dates_calendar_id_foreign` foreign key (`calendar_id`) references `calendars` (`id`) on delete cascade  
  ⇂ alter table `dates` add constraint `dates_color_association_id_foreign` foreign key (`color_association_id`) references `color_associations` (`id`) on delete cascade  

